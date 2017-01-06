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
 * Solr engine.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/lib/filelib.php');

class engine extends \core_search\engine {

    /**
     * Constructor.
     */
    public function __construct() {
        global $CFG;
        $this->config = get_config('search_elastic');
    }

    /**
     * Generates the Elasticsearch server endpoint URL from
     * the config hostname and port.
     *
     * @return url|bool Returns url if succes or false on error.
     */
    private function get_url(){
        $return_val = False;

        if (!empty($this->config->hostname) && !empty($this->config->port)) {
            $url = rtrim($this->config->hostname,"/");
            $port = $this->config->port;
            return $url . ':'. $port;
        }

        return $return_val;
    }

    /**
     * Is the Elasticsearch server endpoint configured in Moodle
     * and available.
     *
     * @return true|string Returns true if all good or an error string.
     */
    public function is_server_configured() {
        $url = $this->get_url();
        $return_val = True;
        $client = new \curl();

        if (!$url) {
            $return_val = get_string('no_config', 'search_elastic');
        }
        elseif (!(bool)json_decode($client->get($url))) {
            $return_val = get_string('no_server', 'search_elastic');
        }

        return $return_val;
    }

    /**
     * Have all the requirements been met for the Elasticsearch server
     * enpoint to operate correctly. That is is the server configured and
     * available and is the schema correclty setup.
     *
     * @return true|string Returns true on success or a string on failure.
     */
    public function is_server_ready() {

        // First check if the server is configured and available
        return $this->is_server_configured();

        // Now check if the schema is setup
    }

    public function get_query_total_count() {
        // Return an approximate count of total records for the most recently completed execute_query().
        // Must be implemented to return the number of results that available for the most recent call to execute_query().
        // This is used to determine how many pages will be displayed in the paging bar. For more discussion see MDL-53758. 

    }

    public function add_document($document, $fileindexing = false) {

    }

    public function execute_query($filters, $usercontexts, $limit = 0) {

    }

    public function delete($module = null) {

    }
}
