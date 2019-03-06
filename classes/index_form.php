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
 * Main Admin settings form class.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Main Admin settings form class.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class index_form extends \moodleform {

    /**
     * Build form for the general setting admin page for plugin.
     */
    public function definition() {
        $config = get_config('search_elastic');
        $mform = $this->_form;

        // Basic settings.
        $mform->addElement('header', 'basicheader', get_string('basicsettings', 'search_elastic'));

        $mform->addElement('text', 'hostname',  get_string ('hostname', 'search_elastic'));
        $mform->setType('hostname', PARAM_URL);
        $mform->addHelpButton('hostname', 'hostname', 'search_elastic');
        $mform->addRule('hostname', get_string ('required'), 'required', '', 'client');
        if (isset($config->hostname)) {
            $mform->setDefault('hostname', $config->hostname);
        } else {
            $mform->setDefault('hostname', 'http://127.0.0.1');
        }

        $mform->addElement('text', 'port',  get_string ('port', 'search_elastic'));
        $mform->setType('port', PARAM_INT);
        $mform->addHelpButton('port', 'port', 'search_elastic');
        $mform->addRule('port', get_string ('required'), 'required', '', 'client');
        if (isset($config->port)) {
            $mform->setDefault('port', $config->port);
        } else {
            $mform->setDefault('port', 9200);
        }

        $mform->addElement('text', 'index',  get_string ('index', 'search_elastic'));
        $mform->setType('index', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('index', 'index', 'search_elastic');
        $mform->addRule('index', get_string ('required'), 'required', '', 'client');
        if (isset($config->index)) {
            $mform->setDefault('index', $config->index);
        } else {
            $mform->setDefault('index', 'mooodle');
        }

        $mform->addElement('text', 'sendsize',  get_string ('sendsize', 'search_elastic'));
        $mform->setType('sendsize', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('sendsize', 'sendsize', 'search_elastic');
        $mform->addRule('sendsize', get_string ('required'), 'required', '', 'client');
        if (isset($config->sendsize)) {
            $mform->setDefault('sendsize', $config->sendsize);
        } else {
            $mform->setDefault('sendsize', 9000000);
        }

        // Search Settings.
        $mform->addElement('header', 'searchsettings', get_string('searchsettings', 'search_elastic'));

        $mform->addElement(
            'advcheckbox',
            'wildcardend',
            get_string ('wildcardend', 'search_elastic'),
            get_string('enable'), array(), array(0, 1));
        $mform->setType('wildcardend', PARAM_INT);
        $mform->addHelpButton('wildcardend', 'wildcardend', 'search_elastic');
        $wildcardend = isset($config->wildcardend) ? $config->wildcardend : 0;
        $mform->setDefault('wildcardend', $wildcardend);

        $mform->addElement(
            'advcheckbox',
            'wildcardstart',
            get_string ('wildcardstart', 'search_elastic'),
            get_string('enable'), array(), array(0, 1));
        $mform->setType('wildcardstart', PARAM_INT);
        $mform->addHelpButton('wildcardstart', 'wildcardstart', 'search_elastic');
        $wildcardstart = isset($config->wildcardstart) ? $config->wildcardstart : 0;
        $mform->setDefault('wildcardstart', $wildcardstart);

        // Request Signing settings.
        $mform->addElement('header', 'signingsettings', get_string('signingsettings', 'search_elastic'));

        $mform->addElement('advcheckbox', 'signing',  get_string ('signing', 'search_elastic'), 'Enable', array(), array(0, 1));
        $mform->setType('signing', PARAM_INT);
        $mform->addHelpButton('signing', 'signing', 'search_elastic');
        if (isset($config->signing)) {
            $mform->setDefault('signing', $config->signing);
        } else {
            $mform->setDefault('signing', 0);
        }

        $mform->addElement('text', 'signingkeyid',  get_string ('signingkeyid', 'search_elastic'));
        $mform->setType('signingkeyid', PARAM_TEXT);
        $mform->addHelpButton('signingkeyid', 'signingkeyid', 'search_elastic');
        $mform->disabledIf('signingkeyid', 'signing');
        if (isset($config->signingkeyid)) {
            $mform->setDefault('signingkeyid', $config->signingkeyid);
        } else {
            $mform->setDefault('signingkeyid', '');
        }

        $mform->addElement('text', 'signingsecretkey',  get_string ('signingsecretkey', 'search_elastic'));
        $mform->setType('signingsecretkey', PARAM_TEXT);
        $mform->addHelpButton('signingsecretkey', 'signingsecretkey', 'search_elastic');
        $mform->disabledIf('signingsecretkey', 'signing');
        if (isset($config->signingsecretkey)) {
            $mform->setDefault('signingsecretkey', $config->signingsecretkey);
        } else {
            $mform->setDefault('signingsecretkey', '');
        }

        $mform->addElement('text', 'region',  get_string ('region', 'search_elastic'));
        $mform->setType('region', PARAM_TEXT);
        $mform->addHelpButton('region', 'region', 'search_elastic');
        $mform->disabledIf('region', 'signing');
        if (isset($config->region)) {
            $mform->setDefault('region', $config->region);
        } else {
            $mform->setDefault('region', 'us-west-2');
        }

        $this->add_action_buttons();
    }

}
