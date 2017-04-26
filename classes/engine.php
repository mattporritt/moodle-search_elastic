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

/**
 * Elasticsearch engine.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class engine extends \core_search\engine {

    /**
     * @var int Factor to multiply fetch limit by when getting results.
     */
    protected $totalresultdocs = 0;

    /**
     * @var bool The payload to be sent to the Elasticsearch service.
     */
    protected $payload = false;

    /**
     * @var int The current size of the payload object.
     */
    protected $payloadsize = 0;

    /**
     * @var int Count of how many parent documents are in current payload.
     */
    protected $count = 0;

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
        $client = new \search_elastic\esrequest();

        if (!empty($this->config->index) && $url) {
            $index = $url . '/'. $this->config->index;
            $response = $client->get($index);
            $responsecode = $response->getStatusCode();

        }
        if ($responsecode == 200) {
            $returnval = true;
        }

        return $returnval;
    }

    /**
     * Create index in Elasticsearch backend
     */
    private function create_index() {
        $url = $this->get_url();
        $client = new \search_elastic\esrequest();
        if (!empty($this->config->index) && $url) {
            $index = $url . '/'. $this->config->index;
            $response = $client->put($index);
            $responsecode = $response->getStatusCode();
        } else {
            throw new \moodle_exception('noconfig', 'search_elastic', '');
        }
        if ($responsecode !== 200) {
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
        $client = new \search_elastic\esrequest();
        $response = $client->get($url);
        $responsebody = $response->getBody(true);

        if (!$url) {
            $returnval = get_string('noconfig', 'search_elastic');
        } else if (!(bool)json_decode($responsebody)) {
            $returnval = get_string('noserver', 'search_elastic');
        }

        return $returnval;
    }

    /**
     * Called when indexing is triggered.
     * Creates the Index namespace and adds fields if they don't exist.
     *
     * @param bool $fullindex is this a full index of site.
     */
    public function index_starting($fullindex = false) {
        if ($fullindex) {
            // If we are doing a reindex then delete old index first.
            $this->delete();

            // Check if index exists and create it if it doesn't.
            $hasindex = $this->check_index();
            if (!$hasindex) {
                $this->create_index();
            }
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \core_search\engine::area_index_starting()
     *
     * @param \core_search\base $searcharea The search area.
     * @param bool $fullindex is this a full index of site.
     */
    public function area_index_starting($searcharea, $fullindex = false) {
        $requiredfields = \search_elastic\document::get_required_fields_definition();
        $url = $this->get_url();
        $mappingeurl = $url . '/'. $this->config->index. '/_mapping/'.$searcharea->get_area_id();
        $mapping = array('properties' => $requiredfields);
        $client = new \search_elastic\esrequest();

        $client->put($mappingeurl, json_encode($mapping));
    }

    /**
     * Get the currently indexed files for a particular document, returns the total count, and a subset of files.
     *
     * @param document $document
     * @param int      $start The row to start the results on. Zero indexed.
     * @param int      $rows The number of rows to fetch
     * @return array   A two element array, the first is the total number of availble results, the second is an array
     *                 of documents for the current request.
     */
    private function get_indexed_files($document, $start = 0, $rows = 500) {
        $url = $this->get_url();
        $indexeurl = $url . '/'. $this->config->index. '/_search';
        $client = new \search_elastic\esrequest();
        // TODO: move this to document class.
        $query = array('query' => array(
                'bool' => array(
                        'must' => array(
                            array('match' => array('type' => 2)),
                            array('match' => array('areaid' => $document->get('areaid'))),
                            array('match' => array('parentid' => $document->get('id'))),
                        )
                )),
                '_source' => array('id',
                                  'modified',
                                  'filecontenthash',
                                  'title'),
                'from' => $start,
                'size' => $rows,
                );
        $jsonquery = json_encode($query);
        $response = $client->post($indexeurl, $jsonquery)->getBody();
        $results = json_decode($response);

        if (!isset($results->hits)) {
            $returnarray = array(0, array());
        } else {
            $returnarray = array($results->hits->total, $results->hits->hits);
        }

        return $returnarray;

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
     * Given an array of files,
     * remove these from the index.
     *
     * @param object $idstodelete Files to remove from index.
     */
    private function delete_indexed_files($idstodelete) {
        // Delete files that are no longer attached.
        foreach ($idstodelete as $id => $type) {
            // We directly delete the item using the client, as the engine delete_by_id won't work on file docs.
            $this->delete_by_type_id ( $type, $id );
        }
    }

    /**
     * Given a document, get that documents associated files
     * and update them in the index.
     *
     * @param \core_search\document $document The document whose files to index/
     * @return array files to add to index and files to delete from index.
     */
    private function filter_indexed_files($document) {
        $rows = 500; // Maximum rows to process at a time.
        $files = $document->get_files(); // Get the attached files.
        // We do this progressively, so we can handle lots of files cleanly.
        list ( $numfound, $indexedfiles ) = $this->get_indexed_files ( $document, 0, $rows );
        $count = 0;
        $idstodelete = array ();

        do {
            // Go through each indexed file. We want to not index any stored and unchanged ones, delete any missing ones.
            foreach ($indexedfiles as $indexedfile) {
                $fileid = $indexedfile->_source->id;

                if (isset ( $files [$fileid] )) {
                    // Check for changes that would mean we need to re-index the file. If so, just leave in $files.
                    // Filelib does not guarantee time modified is updated, so we will check important values.
                    if ($indexedfile->_source->modified != $files [$fileid]->get_timemodified ()) {
                        continue;
                    }
                    if (strcmp ( $indexedfile->_source->title, $files [$fileid]->get_filename () ) !== 0) {
                        continue;
                    }
                    if ($indexedfile->_source->filecontenthash != $files [$fileid]->get_contenthash ()) {
                        continue;
                    }
                    // If the file is already indexed, we can just remove it from the files array and skip it.
                    unset ( $files [$fileid] );
                } else {
                    // This means we have found a file that is no longer attached, so we need to delete from the index.
                    // We do it later, since this is progressive, and it could reorder results.
                    $idstodelete [$indexedfile->_source->id] = $indexedfile->_type;
                }
            }
            $count += $rows;

            if ($count < $numfound) {
                // If we haven't hit the total count yet, fetch the next batch.
                list ( $numfound, $indexedfiles ) = $this->get_indexed_files ( $document, $count, $rows );
            }
        } while ( $count < $numfound );

        return array($files, $idstodelete);
    }

    /**
     * Given a document object, transform into formatted JSON ready to be
     * sent to Elasticsearch.
     *
     * @param object $docdata Object containing document information to index.
     * @return string The JSON representation of doc data, ready to be indexed.
     */
    private function create_payload($docdata) {

        $meta = array('index' => array('_index' => $this->config->index, '_type' => $docdata['areaid']));
        $jsonmeta = json_encode($meta);
        $jsondoc = json_encode($docdata);
        $jsonpayload = $jsonmeta . "\n" . $jsondoc. "\n";

        return $jsonpayload;
    }

    /**
     * Add files to the index.
     *
     * @param document $document document
     */
    private function process_document_files($document) {
        // Handle already indexed Files.
        if (!$document->get_is_new()) {

            // If this isn't a new document, we need to check the exiting indexed files.
            list ($files, $idstodelete) = $this->filter_indexed_files($document);

            // Delete files that are no longer attached.
            $this->delete_indexed_files($idstodelete);

        }

        foreach ($files as $fileid => $file) {
            $filedocdata = $document->export_file_for_engine($file);

            $jsonpayload = $this->create_payload($filedocdata);
            $this->batch_add_documents($jsonpayload);
        }

        $this->batch_add_documents(false, false, true);

    }

    /**
     * Loop through given iterator of search documents
     * and and have the search engine back end add them
     * to the index.
     *
     * @param iterator $iterator the iterator of documents to index
     * @param searcharea $searcharea the area for the documents to index
     * @param aray $options document indexing options
     * @return array Processed document counts
     */
    public function add_documents($iterator, $searcharea, $options) {
        $lastindexeddoc = 0;
        $numrecords = 0;
        $numdocsignored = 0;
        $numdocs = 0;

        // First we'll process all the documents, then if we
        // are processing files we'll itterate through again and just add the files.
        foreach ($iterator as $document) {
            if (!$document instanceof \core_search\document) {
                continue;
            }
            if ($options['lastindexedtime'] == 0) {
                // If we have never indexed this area before, it must be new.
                $document->set_is_new(true);
            }

            $lastindexeddoc = $document->get('modified');
            $docdata = $document->export_for_engine();

            $numrecords++;
            $jsonpayload = $this->create_payload($docdata);
            $numdocsignored += $this->batch_add_documents($jsonpayload, true);

            if ($options['indexfiles']) {
                $searcharea->attach_files($document);
                $this->process_document_files($document);
            }
        }

        $numdocsignored += $this->batch_add_documents(false, true, true);
        $numdocs = $numrecords - $numdocsignored;

        return array($numrecords, $numdocs, $numdocsignored, $lastindexeddoc);
    }

    /**
     * Add the payload object containing document information
     * in JSON format to the Elasticsearch index.
     *
     * @param string $jsonpayload
     * @param bool $isdoc
     * @param bool $sendnow
     * @return number Number of documents not indexed.
     */
    private function batch_add_documents($jsonpayload, $isdoc=false, $sendnow=false) {
        $numdocsignored = 0;
        if (!$sendnow) {
            $this->payload .= $jsonpayload;
            $this->payloadsize += strlen($jsonpayload);
        }

        // Track how many parent docs are in the request.
        if ($isdoc) {
            $this->count++;
        }

        // Some Elastic search providers such as AWS have a limit on how big the
        // HTTP payload can be. Therefore we limit it to a size in bytes.
        // If we don't have enough data to send yet return early.
        if ($this->payloadsize < $this->config->sendsize && !$sendnow) {
            return $numdocsignored;
        } else if ($this->payloadsize > 0) { // Make sure we have at least some data to send.
            $url = $this->get_url ();
            $client = new \search_elastic\esrequest ();
            $docurl = $url . '/' . $this->config->index . '/_bulk';
            $response = $client->post ( $docurl, $this->payload );
            $responsebody = json_decode ($response->getBody () );

            // Process response.
            // If no errors were returned from bulk operation then numdocs = numrecords.
            // If there are errors we need to itterate throught he response and count how many.
            if ($response->getStatusCode() == 413) {
                // TODO: add handling to retry sending payload one record at a time.
                debugging ( get_string ( 'addfail', 'search_elastic' ) . ' Request Entity Too Large', DEBUG_DEVELOPER );
                $numdocsignored = $this->count;

            } else if ($response->getStatusCode() >= 300) {
                debugging ( get_string ( 'addfail', 'search_elastic' ) .
                        ' Error Code: ' . $response->getStatusCode(), DEBUG_DEVELOPER );
                $numdocsignored = $this->count;

            } else if ($responsebody->errors) {
                debugging ( get_string ( 'addfail', 'search_elastic' ) . $responsebody, DEBUG_DEVELOPER );
                foreach ($responsebody->items as $item) {
                    if ($item->index->status >= 300) {
                        $numdocsignored ++;
                    }
                }
            }

            // Reser the counts.
            $this->payload = false;
            $this->payloadsize = 0;

            // Reset the parent doc ocunt after attempting to add.
            if ($isdoc) {
                $this->count = 0;
            }
        }

        return $numdocsignored;
    }

    /**
     * Add a document to the index
     *
     * @param document $document
     * @param bool $fileindexing are we indexing files
     * @return bool
     */
    public function add_document($document, $fileindexing = false) {
        $docdata = $document->export_for_engine();
        $url = $this->get_url();
        $docurl = $url . '/'. $this->config->index . '/'.$docdata['areaid'];
        $jsondoc = json_encode($docdata);

        $client = new \search_elastic\esrequest();
        $response = $client->post($docurl, $jsondoc);
        $responsecode = $response->getStatusCode();

        if ($responsecode !== 201) {
            debugging(get_string('addfail', 'search_elastic') . $response->getBody(), DEBUG_DEVELOPER);
            return false;
        }

        if ($fileindexing) {
            // This will take care of updating all attached files in the index.
            $this->process_document_files($document);
        }
        return true;

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
        $url = $this->get_url() . '/'.  $this->config->index . '/_search';
        $client = new \search_elastic\esrequest();

        $returnlimit = \core_search\manager::MAX_RESULTS;

        if ($limit == 0) {
            $limit = $returnlimit;
        }

        $query = new \search_elastic\query();
        $esquery = $query->get_query($filters, $usercontexts);

        // Send a request to the server.
        $results = json_decode($client->post($url, json_encode($esquery))->getBody());

        // Iterate through results.
        // TODO: refactor this into its own method.
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
     * @param string $type The document type to delete
     * @param string $id The document id to delete
     * @return void
     */
    public function delete_by_type_id($type, $id) {
        $url = $this->get_url();
        $deleteurl = $url . '/'. $this->config->index . '/'. $type . '/'. $id;
        $client = new \search_elastic\esrequest();

        $client->delete($deleteurl);
    }

    /**
     * Manage deletion of content out of Elasticsearch.
     * If an $areaid is not passed this will delete EVERYTHING!
     *
     * @param bool $areaid | string
     */
    public function delete($areaid = false) {
        $url = $this->get_url();
        $indexeurl = $url . '/'. $this->config->index;
        $client = new \search_elastic\esrequest();
        $returnval = false;

        if ($areaid === false) {
            // Delete all your search engine index contents.
            // Response will return acknowledged True if deletion worked,
            // or a status of not found if index doesn't exist.
            // We'll treat both cases as good.
            $response = json_decode($client->delete($indexeurl)->getBody());
            if (isset($response->acknowledged) && ($response->acknowledged == true)) {
                $this->create_index(); // Recreate the new index.
                $returnval = true;
            } else if (isset($response->status) && ($response->status == 404)) {
                $this->create_index();
                $returnval = true;
            }
        } else {
            $url = $url . '/_search';
            // TODO: move this to request class and check query construction.
            $query = array('query' => array(
                                'bool' => array(
                                    'must' => array(
                                        'match' => array('areaid' => $areaid)
                                    )
                                )
                            ),
                           'fields' => array());
            $results = json_decode($client->post($url, json_encode($query))->getBody());
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
        $client = new \search_elastic\esrequest();
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
            $response = $client->get($url);
            $responsecode = $response->getStatusCode();
            if ($responsecode == 200) {
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
        $url = $this->get_url() . '/' . $this->config->index . '/_forcemerge';
        $client = new \search_elastic\esrequest();

        $client->post($url, '');
    }
}
