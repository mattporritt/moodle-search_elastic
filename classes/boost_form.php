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
 * Boost settings form class.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Boost settings form class.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class boost_form extends \moodleform {

    public function definition() {
        $config = get_config('search_elastic');
        $searchareas = \core_search\manager::get_search_areas_list(true);
        $mform = $this->_form;

        foreach ($searchareas as $areaid => $searcharea) {
            $mform->addElement('text', $areaid,  $searcharea->get_visible_name());
            $mform->setType($areaid, PARAM_INT);
            $mform->addHelpButton($areaid, 'boostvalue', 'search_elastic');
            if (isset($config->$areaid)) {
                $mform->setDefault($areaid, $config->$areaid);
            } else {
                $mform->setDefault($areaid, 10);
            }
        }

        $this->add_action_buttons();
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}

