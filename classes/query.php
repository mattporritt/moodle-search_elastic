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


class query  {

    /**
     * @var number of records to return to Global Search.
     */
    private $limit = 0;

    /**
     * @var query string to pass to Elastic search.
     */
    private $query = array();

    /**
     * construct basic query structure
     */
    public function __construct() {

        $returnlimit = \core_search\manager::MAX_RESULTS;

        // Basic object to build query from.
        $this->query = array('query' => array(
                                'bool' => array(
                                    'must' => array('multi_match' => array()),
                                    'filter' => array('bool' => array('must' => array()))
                            )),
                             'size' => $returnlimit,
                             '_source' => array('excludes' => array('filetext'))
        );
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
        $qobj = array('query' => $q, 'fields' => $searchfields, 'type' => 'phrase_prefix');

        return $qobj;
    }

    private function construct_q_all() {
        return array('query_string' => array('query' => '*', 'fields' => $this->get_search_fields()));
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
        $contextobj = array('terms' => array('contextid' => array()));
        $contexts = array();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($usercontexts));

        foreach ($iterator as $key => $value) {
            array_push ($contexts, $value);
        }
        $contexts = array_values(array_unique ($contexts));
        $contextobj['terms']['contextid'] = $contexts;
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
        $valueobj = array('term' => array($key => $value));

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
    private function construct_array($filters, $key, $match) {
        $arrayobj = array('terms' => array($match => array()));
        $values = $filters->$key;

        foreach ($values as $value) {
            array_push ($arrayobj['terms'][$match], $value);
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
        if (isset($filters->timeend) && $filters->timeend != 0) {
            $contextobj['range']['modified']['lte'] = $filters->timeend;
        }

        return $contextobj;
    }

    /**
     * Construct the Elasticsearch query
     *
     * @param array $filters
     * @param array|int $usercontexts
     * @return \search_elastic\query
     */
    public function get_query($filters, $usercontexts) {
        $query = $this->query;

        // Add query text.
        if ($filters->q != '*') {
            $q = $this->construct_q($filters->q);
            $query['query']['bool']['must']['multi_match'] = $q;
        } else {
            $q = $this->construct_q_all();
            $query['query']['bool']['must'] = $q;
        }

        // Add contexts.
        if (gettype($usercontexts) == 'array') {
            $contexts = $this->construct_contexts($usercontexts);
            array_push ($query['query']['bool']['filter']['bool']['must'], $contexts);
        }

        // Add filters.
        if (isset($filters->title) && $filters->title != null) {
            $title = $this->construct_value($filters, 'title');
            array_push ($query['query']['bool']['filter']['bool']['must'], $title);
        }
        if (isset($filters->areaids)) {
            $areaids = $this->construct_array($filters, 'areaids', 'areaid');
            array_push ($query['query']['bool']['filter']['bool']['must'], $areaids);
        }
        if (isset($filters->courseids) && $filters->courseids != null) {
            $courseids = $this->construct_array($filters, 'courseids', 'courseid');
            array_push ($query['query']['bool']['filter']['bool']['must'], $courseids);
        }
        if ($filters->timestart != 0  || $filters->timeend != 0) {
            $timerange = $this->construct_time_range($filters);
            array_push ($query['query']['bool']['filter']['bool']['must'], $timerange);
        }

        return $query;
    }
}