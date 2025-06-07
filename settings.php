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
 * @copyright  2017 Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use search_elastic\admin_setting_check;
use search_elastic\admin_setting_configurl;
use search_elastic\check\server_ready_check;

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('searchplugins', new admin_category('search_elastic', get_string('pluginname', 'search_elastic')));
    $settings = new admin_settingpage('elasticsettings', get_string('adminsettings', 'search_elastic'));
    $settings->add(new admin_setting_heading('basicsettings', get_string('basicsettings', 'search_elastic'), ''));

    // Only check status when when in full tree (i.e. not search)
    // and only when viewing the elastic settings - this is because it can take a long time to run.
    // We don't want it to run unnecessarily.
    if ($ADMIN->fulltree && optional_param('section', null, PARAM_TEXT) == 'elasticsettings') {
        $settings->add(new admin_setting_check('search_elastic/connectiontest', get_string('connectiontest', 'search_elastic'),
            new server_ready_check()));
    }

    $settings->add(new admin_setting_configurl('search_elastic/hostname', get_string ('hostname', 'search_elastic'),
        get_string ('hostname_help', 'search_elastic'), ''));

    $settings->add(new admin_setting_configtext('search_elastic/port', get_string ('port', 'search_elastic'),
        get_string ('port_help', 'search_elastic'), 9200, PARAM_INT));

    $settings->add(new admin_setting_configtext('search_elastic/index', get_string ('index', 'search_elastic'),
        get_string ('index_help', 'search_elastic'), 'moodle', PARAM_ALPHANUMEXT));

    $settings->add(new admin_setting_configtext('search_elastic/sendsize', get_string ('sendsize', 'search_elastic'),
        get_string ('sendsize_help', 'search_elastic'), 9000000, PARAM_ALPHANUMEXT));

    $settings->add(new admin_setting_configtext('search_elastic/apikey', get_string ('apikey', 'search_elastic'),
        get_string ('apikey_help', 'search_elastic'), ''));

    $settings->add(new admin_setting_configtext('search_elastic/connecttimeout', get_string('connecttimeout', 'search_elastic'),
        get_string('connecttimeout_help', 'search_elastic'), 5, PARAM_INT));

    $settings->add(new admin_setting_heading('signingsettings', get_string('signingsettings', 'search_elastic'), ''));
    $settings->add(new admin_setting_configcheckbox('search_elastic/signing', get_string('signing', 'search_elastic'),
        get_string ('signing_help', 'search_elastic'), 0));

    $settings->add(new admin_setting_configtext('search_elastic/signingkeyid', get_string ('signingkeyid', 'search_elastic'),
        get_string ('signingkeyid_help', 'search_elastic'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configpasswordunmask('search_elastic/signingsecretkey',
        get_string ('signingsecretkey', 'search_elastic'),
        get_string ('signingsecretkey_help', 'search_elastic'), ''));

    $settings->add(new admin_setting_configtext('search_elastic/region', get_string ('region', 'search_elastic'),
        get_string ('region_help', 'search_elastic'), 'us-west-2', PARAM_TEXT));

    $settings->add(new admin_setting_heading('searchsettings', get_string('searchsettings', 'search_elastic'), ''));
    $settings->add(new admin_setting_configcheckbox('search_elastic/wildcardend', get_string('wildcardend', 'search_elastic'),
        get_string ('wildcardend_help', 'search_elastic'), 0));

    $settings->add(new admin_setting_configcheckbox('search_elastic/wildcardstart', get_string('wildcardstart', 'search_elastic'),
        get_string ('wildcardstart_help', 'search_elastic'), 0));

    $settings->add(new admin_setting_heading('advsettings', get_string('advsettings', 'search_elastic'), ''));
    $settings->add(new admin_setting_configcheckbox('search_elastic/logging', get_string('logging', 'search_elastic'),
        get_string ('logging_help', 'search_elastic'), 0));

    $simpleurl = get_string('simplehelpurl', 'search_elastic');
    $complexurl = get_string('complexhelpurl', 'search_elastic');
    $settings->add(new admin_setting_configcheckbox('search_elastic/usesimplequery', get_string('usesimplequery', 'search_elastic'),
        get_string ('usesimplequery_help', 'search_elastic', array(
            'simple' => html_writer::link($simpleurl, $simpleurl),
            'complex' => html_writer::link($complexurl, $complexurl))), 0));

    // BOOSTING SETTINGS.
    $settings->add(new admin_setting_heading('boostsettings', get_string('boostsettings', 'search_elastic'), ''));
    $searchareas = \core_search\manager::get_search_areas_list(true);
    foreach ($searchareas as $areaid => $searcharea) {
        $boostconfig = 'boost_' . $areaid;
        // Replace the dash with an underscore, so it is a valid control name.
        $boostconfig = str_replace('-', '_', $boostconfig);
        $settings->add(new admin_setting_configtext("search_elastic/$boostconfig", $searcharea->get_visible_name(),
            get_string('boostvalue', 'search_elastic'), 10, PARAM_INT));
    }

    $enrichsettings = new admin_externalpage('search_elastic_enrichsettings',
            get_string('enrichsettings', 'search_elastic'),
            new moodle_url('/search/engine/elastic/enrich.php'));

    $ADMIN->add('search_elastic', $settings);
    $ADMIN->add('search_elastic', $enrichsettings);
    $settings = null;
}
