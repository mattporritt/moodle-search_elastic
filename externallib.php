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

class search_elastic_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function search_parameters() {
         $parameters = new external_function_parameters(
                array('welcomemessage' => new external_value(
                          PARAM_TEXT,
                          'The welcome message. By default it is "Hello world,"',
                          VALUE_DEFAULT,
                          'Hello world, '),
//                       'q'  => new external_value(
//                           PARAM_TEXT,
//                           'The search query',
//                           VALUE_DEFAULT,
//                           '*'),
//                       'title'  => new external_value(
//                           PARAM_TEXT,
//                           'The search query',
//                           VALUE_DEFAULT,
//                           '*'),
//                       'courses'  => new external_value(
//                           PARAM_TEXT,
//                           'Courses to return results from.',
//                           VALUE_DEFAULT,
//                           'Hello world, '),
//                       'searchareas'  => new external_value(
//                           PARAM_TEXT,
//                           'Search areas to return results from',
//                           VALUE_DEFAULT,
//                           'Hello world, '),
//                       'timestart'  => new external_value(
//                           PARAM_INT,
//                           'Return results newer than this. Value in seconds since Epoch',
//                           VALUE_DEFAULT,
//                           0),
//                       'timeend'  => new external_value(
//                           PARAM_INT,
//                           'Return results newer than this. Value in seconds since Epoch',
//                           VALUE_DEFAULT,
//                           0),
                )
        );

        return $parameters;
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function search($welcomemessage = 'Hello world, ',
                                  $q='*',
                                  $title=false,
                                  $courses=false,
                                  $searchareas=false,
                                  $timestart=0,
                                  $timeend=0) {
        global $USER;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::search_parameters(),
                array('welcomemessage' => $welcomemessage));

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('moodle/search:query', $context)) {
            throw new moodle_exception('cannot_search');
        }

        // Search code goes here.
        $filters = new \stdClass();
        $filters->q = '*';
        $filters->title = '';
        $filters->timestart = 0;
        $filters->timeend = 0;

        $search = \core_search\manager::instance();
 
        // Execute search.
        $results = $search->search($filters);

        // Process results.
        //return print_r($results, true);
        return $params['welcomemessage'] . $USER->firstname ;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function search_returns() {
        return new external_value(PARAM_TEXT, 'The welcome message + user first name');
    }



}