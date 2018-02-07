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
 * Document enrichment settings form class.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Document enrichment settings form class.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrich_form extends \moodleform {

    /**
     * Build form for the general setting admin page for plugin.
     */
    public function definition() {
        $config = get_config('search_elastic');
        $mform = $this->_form;


        // File indexing settings.
        $mform->addElement('header', 'fileindexsettings', get_string('fileindexsettings', 'search_elastic'));

        $mform->addElement('advcheckbox',
                'fileindexing',
                get_string ('fileindexing', 'search_elastic'),
                'Enable', array(), array(0, 1));
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

        // AWS Rekognition settings.
        $mform->addElement('header', 'imagerecognitionsettings', get_string('imagerecognitionsettings', 'search_elastic'));

        $mform->addElement('advcheckbox',
                'imageindex',
                get_string ('imageindex', 'search_elastic'),
                'Enable', array(), array(0, 1));
        $mform->setType('imageindex', PARAM_INT);
        $mform->addHelpButton('imageindex', 'imageindex', 'search_elastic');
        if (isset($config->imageindex)) {
            $mform->setDefault('imageindex', $config->imageindex);
        } else {
            $mform->setDefault('imageindex', 0);
        }

        $mform->addElement('text', 'rekkeyid',  get_string ('rekkeyid', 'search_elastic'));
        $mform->setType('rekkeyid', PARAM_TEXT);
        $mform->addHelpButton('rekkeyid', 'rekkeyid', 'search_elastic');
        $mform->disabledIf('rekkeyid', 'imageindex');
        if (isset($config->rekkeyid)) {
            $mform->setDefault('rekkeyid', $config->rekkeyid);
        } else {
            $mform->setDefault('rekkeyid', '');
        }

        $mform->addElement('text', 'reksecretkey',  get_string ('reksecretkey', 'search_elastic'));
        $mform->setType('reksecretkey', PARAM_TEXT);
        $mform->addHelpButton('reksecretkey', 'reksecretkey', 'search_elastic');
        $mform->disabledIf('reksecretkey', 'imageindex');
        if (isset($config->reksecretkey)) {
            $mform->setDefault('reksecretkey', $config->reksecretkey);
        } else {
            $mform->setDefault('reksecretkey', '');
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

        $this->add_action_buttons();
    }

}
