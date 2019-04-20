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
 * Elasticsearch engine upgrade code.
 *
 * @package     search_elastic
 * @copyright   2018 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade search_elastic.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_search_elastic_upgrade($oldversion) {
    if ($oldversion < 2019042101) {
        // Check for corrupt index definition and fix if required.
        // Fix involves deleting all indexed documents.
        $elastic = new \search_elastic\engine();
        $validindex = $elastic->validate_index();

        if (!$validindex) {
            // Index insn't valid, lets delete it.
            // Delete operation will recreate index with correct mapping.
            $elastic->delete();

            // Let Moodle know index has been deleted so contents are automatically reindexed.
            $searchmanager = \core_search\manager::instance();
            $searchmanager->delete_index();
        }

        upgrade_plugin_savepoint(true, 2019042101, 'search', 'elastic');
    }

    return true;
}
