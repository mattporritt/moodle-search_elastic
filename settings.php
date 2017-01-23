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
 * Elastic search engine settings.
 *
 * @package    search_elastic
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('search_elastic_settings', '', get_string('pluginname_desc', 'search_elastic')));

    if (! during_initial_install ()) {
        // Basic settings.
        $settings->add(new admin_setting_configtext('search_elastic/hostname',
                get_string('hostname', 'search_elastic' ),
                get_string('hostname_desc', 'search_elastic'),
                'http://127.0.0.1', PARAM_URL));

        $settings->add(new admin_setting_configtext('search_elastic/port',
                get_string('port', 'search_elastic' ),
                get_string('port_desc', 'search_elastic'),
                9200, PARAM_INT));

        $settings->add(new admin_setting_configtext('search_elastic/index',
                get_string('index', 'search_elastic' ),
                get_string('index_desc', 'search_elastic'),
                'moodle', PARAM_ALPHANUMEXT));

        // File indexing settings.
        $settings->add(new admin_setting_heading('search_elastic_fileindexing',
                get_string('fileindexsettings', 'search_elastic'),
                get_string('fileindexsettings_desc', 'search_elastic')
                ));
        $settings->add(new admin_setting_configcheckbox('search_elastic/fileindexing',
                get_string('fileindexing', 'search_elastic'),
                get_string('fileindexing_help', 'search_elastic'), 0));

        $settings->add(new admin_setting_configtext('search_elastic/tikahostname',
                get_string('tikahostname', 'search_elastic' ),
                get_string('tikahostname_desc', 'search_elastic'),
                'http://127.0.0.1', PARAM_URL));

        $settings->add(new admin_setting_configtext('search_elastic/tikaport',
                get_string('tikaport', 'search_elastic' ),
                get_string('tikaport_desc', 'search_elastic'),
                9998, PARAM_INT));

        // Request Signing settings.
        $settings->add(new admin_setting_heading('search_elastic_signing',
                get_string('signingsettings', 'search_elastic'),
                get_string('signingsettings_desc', 'search_elastic')
                ));
        $settings->add(new admin_setting_configcheckbox('search_elastic/signing',
                get_string('signing', 'search_elastic'),
                get_string('signing_desc', 'search_elastic'), 0));

        $settings->add(new admin_setting_configtext('search_elastic/keyid',
                get_string('signingkeyid', 'search_elastic' ),
                get_string('signingkeyid_desc', 'search_elastic'),
                '', PARAM_TEXT));

        $settings->add(new admin_setting_configtext('search_elastic/secretkey',
                 get_string('signingsecretkey', 'search_elastic' ),
                 get_string('signingsecretkey_desc', 'search_elastic'),
                 '', PARAM_TEXT));
        $settings->add(new admin_setting_configtext('search_elastic/region',
                 get_string('region', 'search_elastic' ),
                 get_string('region_desc', 'search_elastic'),
                 '', PARAM_TEXT));

    }
}
