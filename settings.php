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

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('searchplugins', new admin_category('search_elastic', get_string('pluginname', 'search_elastic')));

    $pluginsettings = new admin_externalpage('search_elastic_settings',
            get_string('adminsettings', 'search_elastic'),
            new moodle_url('/search/engine/elastic/index.php'));

    $boostsettings= new admin_externalpage('search_elastic_boostsettings',
            get_string('boostsettings', 'search_elastic'),
            new moodle_url('/search/engine/elastic/boost.php'));

    $ADMIN->add('search_elastic', $pluginsettings);
    $ADMIN->add('search_elastic', $boostsettings);

    $settings = null;
}
