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

namespace search_elastic\check;

use action_link;
use core\check\check;
use core\check\result;
use moodle_url;
use search_elastic\engine;

/**
 * Elasticsearch plugin server ready check.
 *
 * @package     search_elastic
 * @copyright   Matthew Hilton <matthew.hilton@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class server_ready_check extends check {
    /** @var object|false Custom Guzzle stack to use for requests. Used only for unit testing. */
    private $stack;

    /**
     * Create check.
     * @param object|false $stack Custom HTTP stack for unit testing.
     */
    public function __construct($stack = false) {
        $this->stack = $stack;
    }

    /**
     * Performs check and returns result.
     *
     * @return result
     */
    public function get_result(): result {
        // Check the plugin is configured.
        if (empty(get_config('search_elastic', 'hostname'))) {
            return new result(result::NA, get_string('connection:na', 'search_elastic'));
        }

        // Query the server to see the HTTP response.
        $engine = new engine();
        $url = $engine->get_url();
        $status = $engine->get_server_status_code($this->stack);
        $resultstatus = $status === 200 ? result::OK : result::ERROR;

        // Format the check details nicely.
        $statusdetails = get_string('connection:status', 'search_elastic', [
            'url' => $url,
            'status' => $status,
        ]);

        return new result($resultstatus, $statusdetails);
    }

    /**
     * Returns the link to action this check if it failed.
     *
     * @return action_link A link to the elasticsearch engine plugin settings
     */
    public function get_action_link(): action_link {
        $configstr = get_string('adminsettings', 'search_elastic');
        $configurl = new moodle_url('/admin/settings.php', ['section' => 'elasticsettings']);

        return new action_link($configurl, $configstr);
    }
}
