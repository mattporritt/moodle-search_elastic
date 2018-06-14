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

    /**
     * @var mixed $customdata Customdata passed to the form.
     */
    private $customdata;

    /**
     * Form element default set helper method.
     *
     * @param mixed $element Element name to set default for.
     * @param mixed $default Default value to set for element.
     * @param \moodleform $mform Moodle form object.
     * @param mixed $config Search plugin configuration.
     */
    private function set_default($element, $default, &$mform, $config) {
        if (isset($this->customdata[$element])) {
            $mform->setDefault($element, $this->customdata[$element]);
        } else if (isset($config->{$element})) {
            $mform->setDefault($element, $config->{$element});
        } else {
            $mform->setDefault($element, $default);
        }
    }

    /**
     * Given an enrichment class type return an array of all the
     * available class names for that type.
     *
     * @param string $type
     * @return array $classnames Array of enrich classes names.
     */
    private function get_enrich_classes($type) {
        $classnames = array();
        $typedir = __DIR__ . '/enrich/' . $type;
        $handle = opendir($typedir);
        while (($file = readdir($handle)) !== false) {
            preg_match('/\b([^.]*)/', $file, $matches);
            foreach ($matches as $classname) {
                $classnames[] = '\search_elastic\enrich\\' . $type . '\\' . $classname;
            }
        }
        closedir($handle);
        $classnames = array_unique($classnames);

        return $classnames;
    }

    /**
     * Given an array of data enrichment classnames return an array of values, and classnames
     * to be used in the file enrichment form.
     *
     * @param array $classnames Array of classnames.
     * @return array $options Array with classes as key and human readbale names as values.
     */
    private function get_enrich_options($classnames) {
        $options = array();
        foreach ($classnames as $classname) {
            if ($classname != '\search_elastic\enrich\text\plain_text') { // Filter out plain text process as it always applies.
                $options[$classname] = $classname::get_enrich_name();
            }
        }

        return $options;
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
        } else if (isset($config->fileindexing)) {
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
        $this->set_default('fileindexing', 0, $mform, $config);

        // Text extraction settings.
        // Heading.
        $mform->addElement('header', 'textextractionsettings', get_string('textextractionsettings', 'search_elastic'));
        $desccontent = html_writer::div(get_string('textextractionsettingsdesc', 'search_elastic'), 'form_description');
        $mform->addElement('html', $desccontent);

        // Text extraction processor selection.
        $fileprocessors = array('' => get_string('none', 'search_elastic'));
        $classnames = $this->get_enrich_classes('text');
        $fileprocessors = array_merge($fileprocessors, $this->get_enrich_options($classnames));

        $select = $mform->addElement('select', 'fileindexselect', get_string('fileindexselect', 'search_elastic'), $fileprocessors);
        $mform->addHelpButton('fileindexselect', 'fileindexselect', 'search_elastic');

        if (isset($this->customdata['fileindexselect'])) {
            $select->setSelected($this->customdata['fileindexselect']);
            $fileprocessor = $this->customdata['fileindexselect'];
        } else if (isset($config->fileindexselect)) {
            $select->setSelected($config->fileindexselect);
            $fileprocessor = $config->fileindexselect;
        } else {
            $select->setSelected('');
            $fileprocessor = '';
        }

        // Add file processing form elements based on processor selection.
        if ($fileprocessor != '' && $indexfiles == 1) {
            $fileprocessor::form_definition_extra($this, $this->_form, $this->_customdata, $config);
        }

        // Image recognition settings.
        // Heading.
        $mform->addElement('header', 'imagerecognitionsettings', get_string('imagerecognitionsettings', 'search_elastic'));
        $desccontent = html_writer::div(get_string('imagerecognitionsettingsdesc', 'search_elastic'), 'form_description');
        $mform->addElement('html', $desccontent);

        // Image recognition processor selection.
        $imageprocessors = array('' => get_string('none', 'search_elastic'));
        $classnames = $this->get_enrich_classes('image');
        $imageprocessors = array_merge($imageprocessors, $this->get_enrich_options($classnames));

        $select = $mform->addElement('select', 'imageindexselect',
            get_string('imageindexselect', 'search_elastic'), $imageprocessors);
        $mform->addHelpButton('imageindexselect', 'imageindexselect', 'search_elastic');

        if (isset($this->customdata['imageindexselect'])) {
            $select->setSelected($this->customdata['imageindexselect']);
            $imageprocessor = $this->customdata['imageindexselect'];
        } else if (isset($config->imageindexselect)) {
            $select->setSelected($config->imageindexselect);
            $imageprocessor = $config->imageindexselect;
        } else {
            $select->setSelected('');
            $imageprocessor = '';
        }

        // Add image recognition form elements based on processor selection.
        if ($imageprocessor != '' && $indexfiles == 1) {
            $imageprocessor::form_definition_extra($this, $this->_form, $this->_customdata, $config);
        }

        $this->add_action_buttons();
    }

}
