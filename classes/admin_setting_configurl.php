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

/**
 * Elasticsearch settings.
 *
 * @package     search_elastic
 * @copyright   2023 Lars Thoms <lars@thoms.io>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_configurl extends \admin_setting_configtext {

    /**
     * Unlike `PARAM_URL`, HTTP BasicAuth components are allowed.
     * It must also be an HTTP scheme, and port or path specifications
     * are not allowed.
     *
     * @param $data
     * @return \lang_string|mixed|string|true
     * @throws \coding_exception
     */
    public function validate($data) {
        if (self::validateURL($data)) {
            return true;
        } else {
            return get_string('validateerror', 'admin');
        }
    }

    static public function validateURL($data) {
        global $CFG;

        $data = (string)fix_utf8($data);
        include_once($CFG->dirroot . '/lib/validateurlsyntax.php');
        return (!empty($data) && validateUrlSyntax($data, 's+H?S?F-E-u?P?a+I?p-f-q-r-'));
    }
}
