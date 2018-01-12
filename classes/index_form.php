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


        // File indexing settings.
        $mform->addElement('header', 'fileindexsettings', get_string('fileindexsettings', 'search_elastic'));

        $mform->addElement('advcheckbox', 'fileindexing',  get_string ('fileindexing', 'search_elastic'), 'Enable', array(), array(0, 1));
        $mform->setType('fileindexing', PARAM_INT);
        $mform->addHelpButton('fileindexing', 'fileindexing', 'search_elastic');
        if (isset($config->fileindexing)) {
            $mform->setDefault('fileindexing', $config->fileindexing);
        } else {
            $mform->setDefault('fileindexing', 0);
        }

        $mform->addElement('text', 'tikahostname',  get_string ('tikahostname', 'search_elastic'));
        $mform->setType('tikahostname', PARAM_URL);
        $mform->addHelpButton('tikahostname', 'tikahostname', 'search_elastic');
        $mform->disabledIf('tikahostname', 'fileindexing');
        if (isset($config->tikahostname)) {
            $mform->setDefault('tikahostname', $config->tikahostname);
        } else {
            $mform->setDefault('tikahostname', 'http://127.0.0.1');
        }

        $mform->addElement('text', 'tikaport',  get_string ('tikaport', 'search_elastic'));
        $mform->setType('tikaport', PARAM_INT);
        $mform->addHelpButton('tikaport', 'tikaport', 'search_elastic');
        $mform->disabledIf('tikaport', 'fileindexing');
        if (isset($config->tikaport)) {
            $mform->setDefault('tikaport', $config->tikaport);
        } else {
            $mform->setDefault('tikaport', 9998);
        }

        $mform->addElement('text', 'tikasendsize',  get_string ('tikasendsize', 'search_elastic'));
        $mform->setType('tikasendsize', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('tikasendsize', 'tikasendsize', 'search_elastic');
        $mform->disabledIf('tikasendsize', 'fileindexing');
        if (isset($config->tikasendsize)) {
            $mform->setDefault('tikasendsize', $config->tikasendsize);
        } else {
            $mform->setDefault('tikasendsize', 512000000);
        }

        // AWS Rekognition settings
        $mform->addElement('header', 'rekognitionsettings', get_string('rekognitionsettings', 'search_elastic'));

        $mform->addElement('advcheckbox', 'imageindex',  get_string ('imageindex', 'search_elastic'), 'Enable', array(), array(0, 1));
        $mform->setType('imageindex', PARAM_INT);
        $mform->addHelpButton('imageindex', 'imageindex', 'search_elastic');
        if (isset($config->imageindex)) {
            $mform->setDefault('imageindex', $config->imageindex);
        } else {
            $mform->setDefault('imageindex', 0);
        }

        $mform->addElement('text', 'reksigningkeyid',  get_string ('reksigningkeyid', 'search_elastic'));
        $mform->setType('reksigningkeyid', PARAM_TEXT);
        $mform->addHelpButton('reksigningkeyid', 'reksigningkeyid', 'search_elastic');
        $mform->disabledIf('reksigningkeyid', 'imageindex');
        if (isset($config->reksigningkeyid)) {
            $mform->setDefault('reksigningkeyid', $config->reksigningkeyid);
        } else {
            $mform->setDefault('reksigningkeyid', '');
        }

        $mform->addElement('text', 'reksigningsecretkey',  get_string ('reksigningsecretkey', 'search_elastic'));
        $mform->setType('reksigningsecretkey', PARAM_TEXT);
        $mform->addHelpButton('reksigningsecretkey', 'reksigningsecretkey', 'search_elastic');
        $mform->disabledIf('reksigningsecretkey', 'imageindex');
        if (isset($config->reksigningsecretkey)) {
            $mform->setDefault('reksigningsecretkey', $config->reksigningsecretkey);
        } else {
            $mform->setDefault('reksigningsecretkey', '');
        }

        $mform->addElement('text', 'rekregion',  get_string ('rekregion', 'search_elastic'));
        $mform->setType('rekregion', PARAM_TEXT);
        $mform->addHelpButton('rekregion', 'rekregion', 'search_elastic');
        $mform->disabledIf('rekregion', 'imageindex');
        if (isset($config->rekregion)) {
            $mform->setDefault('rekregion', $config->rekregion);
        } else {
            $mform->setDefault('rekregion', 'us-west-2');
        }

        $mform->addElement('text', 'maxlabels',  get_string ('maxlabels', 'search_elastic'));
        $mform->setType('maxlabels', PARAM_INT);
        $mform->addHelpButton('maxlabels', 'maxlabels', 'search_elastic');
        $mform->disabledIf('maxlabels', 'imageindex');
        if (isset($config->maxlabels)) {
            $mform->setDefault('maxlabels', $config->maxlabels);
        } else {
            $mform->setDefault('maxlabels', 10);
        }

        $mform->addElement('text', 'minconfidence',  get_string ('minconfidence', 'search_elastic'));
        $mform->setType('minconfidence', PARAM_INT);
        $mform->addHelpButton('minconfidence', 'minconfidence', 'search_elastic');
        $mform->disabledIf('minconfidence', 'imageindex');
        if (isset($config->minconfidence)) {
            $mform->setDefault('minconfidence', $config->minconfidence);
        } else {
            $mform->setDefault('minconfidence', 90);
        }

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
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}
