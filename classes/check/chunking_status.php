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

use search_elastic\local\chunking\manager;
use search_elastic\local\model\error;
use action_link;
use core\check\check;
use core\check\result;
use moodle_url;

/**
 * Health check for recent chunking-related errors.
 *
 * If chunking is disabled, this returns NA. If enabled, it queries the search_elastic_errors table
 * for errors classified as TYPE_CHUNKING. If one or more chunking related errors are found, a WARNING
 * result is returned. Otherwise, the check reports OK.
 *
 * @package     search_elastic
 * @author      Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright   2026 Monash University (http://www.monash.edu)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chunking_status extends check {
    /**
     * Performs check and returns result.
     *
     * @return result
     */
    public function get_result(): result {
        global $DB;

        if (!manager::is_chunking_enabled()) {
            return new result(result::NA, get_string('chunking:na', 'search_elastic'));
        }

        $sql = "SELECT COUNT(*)
                  FROM {search_elastic_errors}
                 WHERE errortype = :errortype";
        $count = $DB->count_records_sql($sql, ['errortype' => error::TYPE_CHUNKING]);
        if ($count > 0) {
            return new result(result::WARNING, get_string('chunkingrelatederrorsfound', 'search_elastic', $count));
        }

        return new result(result::OK, get_string('norecentchunkingerrors', 'search_elastic'));
    }

    /**
     * Returns a link to the indexing errors page.
     *
     * @return action_link
     */
    public function get_action_link(): action_link {
        $configstr = get_string('indexingerrors', 'search_elastic');
        $configurl = new moodle_url('/search/engine/elastic/errors.php');

        return new action_link($configurl, $configstr);
    }
}
