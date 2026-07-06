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

/**
 * Function to upgrade search_elastic.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_search_elastic_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019042101) {
        // Check for corrupt index definition and fix if required.
        // Fix involves deleting all indexed documents.
        $elastic = new \search_elastic\engine();

        if ($elastic->is_server_ready() === true) {
            $validindex = $elastic->validate_index();

            if (!$validindex) {
                // Index insn't valid, lets delete it.
                // Delete operation will recreate index with correct mapping.
                $elastic->delete();

                // Let Moodle know index has been deleted so contents are automatically reindexed.
                $searchmanager = \core_search\manager::instance();
                $searchmanager->delete_index();
            }
        }

        upgrade_plugin_savepoint(true, 2019042101, 'search', 'elastic');
    }

    if ($oldversion < 2020022800) {
        // Manually upgrade boosting settings to new control name.
        require_once(__DIR__ . '/upgradelib.php');
        update_boosting_setting_names();
        upgrade_plugin_savepoint(true, 2020022800, 'search', 'elastic');
    }

    if ($oldversion < 2023092000) {
        // Update existing default values that have been set to http://127.0.0.1.
        // To avoid check API from failing when elasticsearch is not setup.
        $DB->execute("UPDATE {config_plugins}
                         SET value = ''
                       WHERE plugin = 'search_elastic' AND name = 'hostname' AND value = 'http://127.0.0.1'");

        upgrade_plugin_savepoint(true, 2023092000, 'search', 'elastic');
    }

    if ($oldversion < 2025062708) {
        // Define table search_elastic_errors to be created.
        $table = new xmldb_table('search_elastic_errors');

        // Adding fields to the search_elastic_errors.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('docid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, null);
        $table->add_field('areaid', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL);
        $table->add_field('errortype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL);
        $table->add_field('errormessage', XMLDB_TYPE_TEXT, null, null, null);
        $table->add_field('retrycount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'failed');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('contentmodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('parentid', XMLDB_TYPE_CHAR, '100', null, null);

        // Adding keys to table search_elastic_errors.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table search_elastic_errors.
        $table->add_index('docid', XMLDB_INDEX_NOTUNIQUE, ['docid']);
        $table->add_index('contextid', XMLDB_INDEX_NOTUNIQUE, ['contextid']);
        $table->add_index('areaid', XMLDB_INDEX_NOTUNIQUE, ['areaid']);
        $table->add_index('status', XMLDB_INDEX_NOTUNIQUE, ['status']);
        $table->add_index('errortype', XMLDB_INDEX_NOTUNIQUE, ['errortype']);
        $table->add_index('parentid', XMLDB_INDEX_NOTUNIQUE, ['parentid']);

        // Conditionally launch create table for search_elastic_errors.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2025062708, 'search', 'elastic');
    }

    if ($oldversion < 2026051404) {
        $table = new xmldb_table('search_elastic_errors');

        $field = new xmldb_field('fileid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'docid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Backfill fileid for existing rows where docid is a plain integer (file documents).
        // File docids are purely numeric and non-file docids always contain '-' (e.g. mod_assign-activity-123).
        $docidcast = $DB->sql_cast_char2int('docid');
        $DB->execute("UPDATE {search_elastic_errors} SET fileid = {$docidcast} WHERE docid NOT LIKE '%-%'");

        upgrade_plugin_savepoint(true, 2026051404, 'search', 'elastic');
    }

    return true;
}
