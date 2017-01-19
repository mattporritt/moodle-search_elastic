<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Elasticsearch engine.
 *
 * Provides an interface between Moodles Global search functionality
 * and the Elasticsearch (https://www.elastic.co/products/elasticsearch)
 * search engine.
 *
 * Elasticsearch presents a REST Webservice API that we communicate with
 * via Curl.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic;

defined('MOODLE_INTERNAL') || die();

class engine extends \core_search\engine {

    /**
     * @var int Factor to multiply fetch limit by when getting results.
     */
    protected $totalresultdocs = 0;

    /**
     * Generates the Elasticsearch server endpoint URL from
     * the config hostname and port.
     *
     * @return url|bool Returns url if succes or false on error.
     */
    private function get_url() {
        $returnval = false;

        if (!empty($this->config->hostname) && !empty($this->config->port)) {
            $url = rtrim($this->config->hostname, "/");
            $port = $this->config->port;
            return $url . ':'. $port;
        }

        return $returnval;
    }

    /**
     * Check if index exists in Elasticssearch backend
     *
     * @return bool True on success False on failure
     */
    private function check_index() {
        $returnval = false;
        $response = 404;
        $url = $this->get_url();
        $client = new \search_elastic\elastic_curl();

        if (!empty($this->config->index) && $url) {
            $index = $url . '/'. $this->config->index;
            $client->get($index);
            $response = $client->info['http_code'];
        }
        if ($response == 200) {
            $returnval = true;
        }

        return $returnval;
    }

    /**
     * Create index in Elasticsearch backend
     */
    private function create_index() {
        $url = $this->get_url();
        $client = new \search_elastic\elastic_curl();
        if (!empty($this->config->index) && $url) {
            $index = $url . '/'. $this->config->index;
            $response = $client->put($index);
        } else {
            throw new \moodle_exception('noconfig', 'search_elastic', '');
        }
        if ($client->info['http_code'] !== 200) {
            throw new \moodle_exception('indexfail', 'search_elastic', '');
        }

    }

    /**
     * Is the Elasticsearch server endpoint configured in Moodle
     * and available.
     *
     * @return true|string Returns true if all good or an error string.
     */
    public function is_server_ready() {
        $url = $this->get_url();
        $returnval = true;
        $client = new \search_elastic\elastic_curl();

        if (!$url) {
            $returnval = get_string('noconfig', 'search_elastic');
        } else if (!(bool)json_decode($client->get($url))) {
            $returnval = get_string('noserver', 'search_elastic');
        }

        return $returnval;
    }

    /**
     * Called when indexing is triggered.
     * Creates the Index namespace and adds fields if they don't exist.
     */
    public function index_starting($fullindex = false) {
        // Check if index exists and create it if it doesn't.
        $hasindex = $this->check_index();
        if (!$hasindex) {
            $this->create_index();
        }
    }

    /**
     * Return a count of total records for the most recently completed
     * execute_query().
     * Must be implemented to return the number of results that available
     * for the most recent call to execute_query().
     * This is used to determine how many pages will be displayed in the paging bar.
     * For more discussion see MDL-53758.
     *
     * @return int
     */
    public function get_query_total_count() {
        return $this->totalresultdocs;
    }

    /**
     * Add files to the index
     */
    private function process_document_files($document) {
        $url = $this->get_url();

        $files = $document->get_files();
        foreach ($files as $file) {
            $filedoc = $document->export_file_for_engine($file);
            $docurl = $url . '/'. $this->config->index . '/'.$filedoc['id'];
            $jsondoc = json_encode($filedoc);
            $client = new \search_elastic\elastic_curl();
            $response = $client->post($docurl, $jsondoc);
        }
    }

    /**
     * Add a document to the index
     */
    public function add_document($document, $fileindexing = false) {
        $docdata = $document->export_for_engine();
        $url = $this->get_url();
        $docurl = $url . '/'. $this->config->index . '/'.$docdata['id'];
        $jsondoc = json_encode($docdata);

        $client = new \search_elastic\elastic_curl();
        $response = $client->post($docurl, $jsondoc);

        if ($client->info['http_code'] !== 201) {
            throw new \moodle_exception('addfail', 'search_elastic', '', '', $response);
        }

        if ($fileindexing) {
            // This will take care of updating all attached files in the index.
            $this->process_document_files($document);
        }

    }

    /**
     * Gets an array of fields to search.
     * The returned fields are what the 'q' string is matched against in a search.
     * It makes sense to not search every field here, so some are removed.
     *
     * @return array
     */
    private function get_search_fields() {
        $allfields = array_keys( \core_search\document::get_default_fields_definition());
        array_push($allfields, 'filetext');
        $excludedfields = array('itemid',
                                'areaid',
                                'courseid',
                                'contextid',
                                'userid',
                                'owneruserid',
                                'modified',
                                'type'
        );
        $searchfields = array_diff($allfields, $excludedfields);

        return array_values($searchfields);
    }

    /**
     * Takes the search string the user has entered
     * and constructs the corresponding part of the
     * search query.
     *
     * @param string $q
     * @return array
     */
    private function construct_q($q) {

        $searchfields = $this->get_search_fields();
        $qobj = array('query_string' => array('query' => $q, 'fields' => $searchfields));

        return $qobj;
    }

    /**
     * Takes supplied user contexts from Moodle search core
     * and constructs the corresponding part of the
     * search query.
     *
     * @param array $usercontexts
     * @return array
     */
    private function construct_contexts($usercontexts) {
        $contextobj = array();

        foreach ($usercontexts as $context) {
            $addcontext = array('match' => array('contextid' => $context));
            array_push ($contextobj, $addcontext);
        }

        return $contextobj;
    }

    /**
     * Takes the form submission filter data and given a key value
     * constructs a single match component for the search query.
     *
     * @param array $filters
     * @param string $key
     * @return array
     */
    private function construct_value($filters, $key) {
        $value = $filters->$key;
        $valueobj = array('match' => array($key => $value));

        return $valueobj;
    }

    /**
     * Takes the form submission filter data and given a key value
     * constructs an array of match components for the search query.
     *
     * @param array $filters
     * @param string $key
     * @return array
     */
    private function construct_array($filters, $key) {
        $arrayobj = array();
        $values = $filters->$key;

        foreach ($values as $value) {
            $addcontext = array('match' => array($key => $value));
            array_push ($arrayobj, $addcontext);
        }

        return $arrayobj;
    }

    /**
     * Takes the form submission filter data and
     * constructs the time range components for the search query.
     *
     * @param array $filters
     * @return array
     */
    private function construct_time_range($filters) {
        $contextobj = array('range' => array('modified' => array()));

        if (isset($filters->timestart) && $filters->timestart != 0) {
            $contextobj['range']['modified']['gte'] = $filters->timestart;
        }
        if (isset($filters->timesend) && $filters->timeend != 0) {
            $contextobj['range']['modified']['lte'] = $filters->timeend;
        }

        return $contextobj;
    }

    /**
     * Takes the user supplied query as well as data from Moodle global
     * search core to construct the search query and execute the query
     * against the search engine.
     * Returns an array of matching result documents.
     *
     * @param array $filters
     * @param array $usercontexts
     * @param int $limit
     * @return array $docs
     */
    public function execute_query($filters, $usercontexts, $limit = 0) {
        $docs = array();
        $doccount = 0;
        $url = $this->get_url() . '/'.  $this->config->index . '/_search?pretty';
        $client = new \search_elastic\elastic_curl();

        $returnlimit = \core_search\manager::MAX_RESULTS;

        if ($limit == 0) {
            $limit = $returnlimit;
        }

        // Basic object to build query from.
        $query = array('query' => array('bool' => array('must' => array())),
                       'size' => $returnlimit,
                       '_source' => array('excludes' => array('filetext'))
        );

        // Add query text.
        $q = $this->construct_q($filters->q);
        array_push ($query['query']['bool']['must'], $q);
        // Add contexts.
        if (gettype($usercontexts) == 'array') {
            $contexts = $this->construct_contexts($usercontexts);
            foreach ($contexts as $context) {
                array_push ($query['query']['bool']['must'], $context);
            }
        }
        // Add filters.
        if (isset($filters->title) && $filters->title != null) {
            $title = $this->construct_value($filters, 'title');
            array_push ($query['query']['bool']['must'], $title);
        }
        if (isset($filters->areaids)) {
            $areaids = $this->construct_array($filters, 'areaids');
            foreach ($areaids as $areaid) {
                array_push ($query['quer y']['bool']['must'], $areaid);
            }
        }
        if (isset($filters->courseids) && $filters->courseids != null) {
            $courseids = $this->construct_array($filters, 'courseids');
            foreach ($courseids as $courseid) {
                array_push ($query['query']['bool']['must'], $courseid);
            }
        }
        if ($filters->timestart != 0  || $filters->timeend != 0) {
            $timerange = $this->construct_time_range($filters);
            $query['query']['bool']['filter'] = $timerange;
        }

        // Send a request to the server.
        $results = json_decode($client->post($url, json_encode($query)));

        // Iterate through results.
        if (isset($results->hits)) {
            foreach ($results->hits->hits as $result) {
                $searcharea = $this->get_search_area($result->_source->areaid);
                if (!$searcharea) {
                    continue;
                }
                $access = $searcharea->check_access($result->_source->itemid);

                if ($access == \core_search\manager::ACCESS_DELETED) {
                    $this->delete_by_type_id($result->_type, $result->_id);
                } else if ($access == \core_search\manager::ACCESS_GRANTED && $doccount < $limit) {
                    $docs[] = $this->to_document($searcharea, (array)$result->_source);
                    $doccount++;
                }
                if ($access == \core_search\manager::ACCESS_GRANTED) {
                    $this->totalresultdocs++;
                }

            }

        }
        // TODO: handle negative cases and errors.
        return $docs;
    }

    /**
     * Deletes the specified document.
     *
     * @param string $id The document id to delete
     * @return void
     */
    public function delete_by_type_id($type, $id) {
        $url = $this->get_url();
        $deleteurl = $url . '/'. $this->config->index . '/'. $type . '/'. $id;
        $client = new \search_elastic\elastic_curl();

        $client->delete($deleteurl);
    }

    /**
     * Manage deletion of content out of Elasticsearch.
     * If an $areaid is not passed this will delete EVERYTHING!
     */
    public function delete($areaid = false) {
        $url = $this->get_url();
        $indexeurl = $url . '/'. $this->config->index;
        $client = new \search_elastic\elastic_curl();
        $returnval = false;

        if ($areaid === false) {
            // Delete all your search engine index contents.
            // Response will return acknowledged True if deletion worked,
            // or a status of not found if index doesn't exist.
            // We'll treat both cases as good.
            $response = json_decode($client->delete($indexeurl));
            if (isset($response->acknowledged) && ($response->acknowledged == true)) {
                $this->create_index(); // Recreate the new index.
                $returnval = true;
            } else if (isset($response->status) && ($response->status == 404)) {
                $this->create_index();
                $returnval = true;
            }
        } else {
            $url = $url . '/_search?pretty';
            $query = array('query' => array(
                                'bool' => array(
                                    'must' => array(
                                        'match' => array('areaid' => $areaid)
                                    )
                                )
                            ),
                           'fields' => array());
            $results = json_decode($client->post($url, json_encode($query)));
            if (isset($results->hits)) {
                foreach ($results->hits->hits as $result) {
                    $this->delete_by_type_id($result->_type, $result->_id);
                }
            }
        }
        return $returnval;
    }

    /**
     * Returns status of if this backend supports indexing of files
     * and if that support is available and enabled.
     *
     * @return bool
     */
    public function file_indexing_enabled() {
        $returnval = false;
        $client = new \search_elastic\elastic_curl();
        $url = '';
        // Check if we have a valid set of config.
        if (!empty($this->config->tikahostname) &&
            !empty($this->config->tikaport &&
            (bool)$this->config->fileindexing)) {
                $port = $this->config->port;
                $hostname = rtrim($this->config->hostname, "/");
                $url = $hostname . ':'. $port;
        }

        // Check we can reach Tika server.
        if ($url !== '') {
            $client->get($url);
            if ($client->info['http_code'] == 200) {
                $returnval = true;
            }
        }

        return $returnval;
    }

    /**
     * The force merge operation allows to reduce the number of segments by merging
     * them and optimizes the index for faster search operations.
     *
     * This call will block until the merge is complete.
     * If the http connection is lost, the request will continue in the background,
     * and any new requests will block until the previous force merge is complete.
     *
     */
    public function optimize() {
        $url = $this->get_url(). $this->config->index . '/_forcemerge';
        $client = new \search_elastic\elastic_curl();

        $client->post($url);
    }
}
