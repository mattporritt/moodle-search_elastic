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

use html_writer;

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

    private $customdata;

    /**
     *
     * @param unknown $element
     * @param unknown $default
     * @param unknown $mform
     * @param unknown $config
     */
    private function setDefault($element, $default, &$mform, $config) {
        if (isset($this->customdata[$element])) {
            $mform->setDefault($element, $this->customdata[$element]);
        } elseif (isset($config->{$element})) {
            $mform->setDefault($element, $config->{$element});
        } else {
            $mform->setDefault($element, $default);
        }
    }

    /**
     * Build form for the general setting admin page for plugin.
     */
    public function definition() {
        $config = get_config('search_elastic');
        $mform = $this->_form;
        $this->customdata = $this->_customdata;
        $mform->setDisableShortforms(); // Shortforms don't work well with the form replacement.

        if (isset($this->customdata['fileindexing'])) {
            $indexfiles = $this->customdata['fileindexing'];
        } elseif (isset($config->fileindexing)) {
            $indexfiles = $config->fileindexing;
        } else {
            $indexfiles = 0;
        }

        // File indexing settings.
        // Heading.
        $mform->addElement('header', 'fileindexsettings', get_string('fileindexsettings', 'search_elastic'));
        $desccontent = html_writer::div(get_string('fileindexsettingsdesc', 'search_elastic'), 'form_description');
        $mform->addElement('html', $desccontent);

        // Enable file indexing.
        $mform->addElement('advcheckbox',
                'fileindexing',
                get_string ('fileindexing', 'search_elastic'),
                'Enable', array(), array(0, 1));
        $mform->setType('fileindexing', PARAM_INT);
        $mform->addHelpButton('fileindexing', 'fileindexing', 'search_elastic');
        $this->setDefault('fileindexing', 0, $mform, $config);

        // Text extraction settings.
        // Heading.
        $mform->addElement('header', 'textextractionsettings', get_string('textextractionsettings', 'search_elastic'));
        $desccontent = html_writer::div(get_string('textextractionsettingsdesc', 'search_elastic'), 'form_description');
        $mform->addElement('html', $desccontent);

        // Text extraction processor selection.
        $fileprocessors = array(
            0 => get_string('none', 'search_elastic'),
            1 => get_string('tika', 'search_elastic')
        );
        $select = $mform->addElement('select', 'fileindexselect', get_string('fileindexselect', 'search_elastic'), $fileprocessors);
        $mform->addHelpButton('fileindexselect', 'fileindexselect', 'search_elastic');
        if (isset($this->customdata['fileindexselect'])) {
            $select->setSelected($this->customdata['fileindexselect']);
            $fileprocessor = $this->customdata['fileindexselect'];
        } elseif (isset($config->fileindexselect)) {
            $select->setSelected($config->fileindexselect);
            $fileprocessor = $config->fileindexselect;
        } else {
            $select->setSelected(0);
            $fileprocessor = 0;
        }

        // Add file processing form elements based on processor selection.
        // TODO: Make this class based or similar. We don't want it conditional when there will be multiple providers.
        if ($fileprocessor == 1 && $indexfiles == 1) {
            $mform->addElement('text', 'tikahostname',  get_string ('tikahostname', 'search_elastic'));
            $mform->setType('tikahostname', PARAM_URL);
            $mform->addHelpButton('tikahostname', 'tikahostname', 'search_elastic');
            $this->setDefault('tikahostname', 'http://127.0.0.1', $mform, $config);

            $mform->addElement('text', 'tikaport',  get_string ('tikaport', 'search_elastic'));
            $mform->setType('tikaport', PARAM_INT);
            $mform->addHelpButton('tikaport', 'tikaport', 'search_elastic');
            $this->setDefault('tikaport', 9998, $mform, $config);

            $mform->addElement('text', 'tikasendsize',  get_string ('tikasendsize', 'search_elastic'));
            $mform->setType('tikasendsize', PARAM_ALPHANUMEXT);
            $mform->addHelpButton('tikasendsize', 'tikasendsize', 'search_elastic');
            $this->setDefault('tikasendsize', 512000000, $mform, $config);
        }

        // Image recognition settings.
        // Heading.
        $mform->addElement('header', 'imagerecognitionsettings', get_string('imagerecognitionsettings', 'search_elastic'));
        $desccontent = html_writer::div(get_string('imagerecognitionsettingsdesc', 'search_elastic'), 'form_description');
        $mform->addElement('html', $desccontent);

        // Image recognition processor selection.
        $imageprocessors = array(
            0 => get_string('none', 'search_elastic'),
            1 => get_string('aws', 'search_elastic')
        );
        $select = $mform->addElement('select', 'imageindexselect', get_string('imageindexselect', 'search_elastic'), $imageprocessors);
        $mform->addHelpButton('imageindexselect', 'imageindexselect', 'search_elastic');
        if (isset($this->customdata['imageindexselect'])) {
            $select->setSelected($this->customdata['imageindexselect']);
            $imageprocessor = $this->customdata['imageindexselect'];
        } elseif (isset($config->imageindexselect)) {
            $select->setSelected($config->imageindexselect);
            $imageprocessor = $config->imageindexselect;
        } else {
            $select->setSelected(0);
            $imageprocessor = 0;
        }

        // Add image recognition form elements based on processor selection.
        // TODO: Make this class based or similar. We don't want it conditional when there will be multiple providers.
        if ($imageprocessor == 1 && $indexfiles == 1) {
            // AWS Rekognition settings.
            $mform->addElement('text', 'rekkeyid',  get_string ('rekkeyid', 'search_elastic'));
            $mform->setType('rekkeyid', PARAM_TEXT);
            $mform->addHelpButton('rekkeyid', 'rekkeyid', 'search_elastic');
            $this->setDefault('rekkeyid', '', $mform, $config);

            $mform->addElement('text', 'reksecretkey',  get_string ('reksecretkey', 'search_elastic'));
            $mform->setType('reksecretkey', PARAM_TEXT);
            $mform->addHelpButton('reksecretkey', 'reksecretkey', 'search_elastic');
            $this->setDefault('reksecretkey', '', $mform, $config);

            $mform->addElement('text', 'rekregion',  get_string ('rekregion', 'search_elastic'));
            $mform->setType('rekregion', PARAM_TEXT);
            $mform->addHelpButton('rekregion', 'rekregion', 'search_elastic');
            $this->setDefault('rekregion', 'us-west-2', $mform, $config);

            $mform->addElement('text', 'maxlabels',  get_string ('maxlabels', 'search_elastic'));
            $mform->setType('maxlabels', PARAM_INT);
            $mform->addHelpButton('maxlabels', 'maxlabels', 'search_elastic');
            $this->setDefault('maxlabels', 10, $mform, $config);

            $mform->addElement('text', 'minconfidence',  get_string ('minconfidence', 'search_elastic'));
            $mform->setType('minconfidence', PARAM_INT);
            $mform->addHelpButton('minconfidence', 'minconfidence', 'search_elastic');
            $this->setDefault('minconfidence', 90, $mform, $config);
        }

        $this->add_action_buttons();
    }

}
