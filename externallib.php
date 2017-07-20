<?php

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
 * Elasticsearch Web Service
 *
 * @package    search_elastic
 * @copyright  2017 Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

use core_search\manager;

/**
 * Elasticsearch Web Service
 *
 * @package    search_elastic
 * @copyright  2017 Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_elastic_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function search_parameters() {
        return new external_function_parameters ( array (
                'q' => new external_value(PARAM_TEXT, 'The search query', VALUE_DEFAULT, '*'),
                'title' => new external_value(PARAM_TEXT, 'Show results that match this title', VALUE_DEFAULT, ''),
                'courseids' => new external_multiple_structure(
                        new external_value(PARAM_INT, 'Course ids'),
                        'List of course ids. If empty return all courses.', VALUE_OPTIONAL ),
                'areaids' => new external_multiple_structure(
                        new external_value(PARAM_INT, 'Area ids'),
                        'List of area ids. If empty return all areas.', VALUE_OPTIONAL ),
                'timestart' => new external_value(PARAM_INT,
                        'Return results newer than this. Value in seconds since Epoch', VALUE_DEFAULT, 0 ),
                'timeend' => new external_value(PARAM_INT,
                        'Return results older than this. Value in seconds since Epoch', VALUE_DEFAULT, 0 )
        ) );
    }

    /**
     * Returns welcome message
     * @return array $docs The search results
     */
    public static function search($q, $title, $courseids=false, $areaids=false, $timestart=0, $timeend=0) {
        global $USER;

        // Parameter validation.
        // This feels dumb and the docs are vague, buy it is required.
        $params = self::validate_parameters(self::search_parameters(),
                array('q' => $q,
                       'title' => $title, 
                       'courseids' => $courseids,
                       'areaids' => $areaids, 
                       'timestart' => $timestart, 
                       'timeend' => $timeend
                       ));

        // Context validation.
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        // Capability checking.
        if (!has_capability('moodle/search:query', $context)) {
            throw new moodle_exception('cannot_search');
        }

        // Execute search.
        $search = \core_search\manager::instance();
        $results = $search->search((object)$params);
        
        // Process the results.
        $docs = array();
        foreach ($results as $result) {
            $docs[] = $result->export_for_webservice();
        }

        return $docs;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function search_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                    array(
                        'componentname' => new external_value(PARAM_TEXT, 'desc'),
                        'areaname' => new external_value(PARAM_TEXT, 'desc'),
                        'courseurl' => new external_value(PARAM_RAW, 'desc'),
                        'coursefullname' => new external_value(PARAM_TEXT, 'desc'),
                        'modified' => new external_value(PARAM_TEXT, 'desc'),
                        'title' => new external_value(PARAM_TEXT, 'desc'),
                        'docurl' => new external_value(PARAM_RAW, 'desc'),
                        'content' => new external_value(PARAM_RAW, 'desc'),
                        'contexturl' => new external_value(PARAM_RAW, 'desc'),
                        'description1' => new external_value(PARAM_TEXT, 'desc'),
                        'description2' => new external_value(PARAM_TEXT, 'desc'),
                    )
                )
        );
    }



}