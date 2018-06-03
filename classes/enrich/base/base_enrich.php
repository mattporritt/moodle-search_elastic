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
 * Base data enrichment class.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic\enrich\base;

defined('MOODLE_INTERNAL') || die;

/**
 * Base data enrichment class.
 *
 * @package    search_elastic
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_enrich {

    /**
     * The constructor for the class, will be overwritten in most cases.
     *
     * @param mixed $config Search plugin configuration.
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Returns the step name.
     *
     * @return string human readable step name.
     */
    abstract static public function get_step_name();

    /**
     * Array of file mimetypes that enrichment class supports
     * processing of / extracting data from.
     *
     * @var array
     */
    protected static $acceptedmime = array();

    /**
     * Returns all accepted file types.
     *
     * @return array
     */
    public static function get_accepted_file_types() {
        return self::acceptedmime;
    }

    /**
     * Checks if supplied file is can be analyzed by this enrichment class.
     *
     * @param \stored_file $file File to check.
     * @return boolean
     */
    public function can_analyze($file) {
        $mimetype = $file->get_mimetype();
        $cananalyze = false;

        if (in_array($mimetype, $this->get_accepted_file_types())) {
            $cananalyze = true;
        }

        return $cananalyze;
    }

    /**
     * Analyse file and return results.
     *
     * @param \stored_file $file The image file to analyze.
     * @return string $imagetext Text of file description labels.
     */
    abstract public function analyze_file($file);

    /**
     * A callback to add fields to the enrich form, specific to enrichment class.
     *
     * @param \moodleform $form
     * @param \MoodleQuickForm $mform
     * @param mixed $customdata
     * @param mixed $config
     */
    abstract public static function form_definition_extra($form, $mform, $customdata, $config);

    /**
     * Form element default set helper method.
     *
     * @param mixed $element Element name to set default for.
     * @param mixed $default Default value to set for element.
     * @param \moodleform $mform Moodle form object.
     * @param mixed $customdata Customdata passed to the form.
     * @param mixed $config Search plugin configuration.
     */
    static protected function set_default($element, $default, &$mform, $customdata, $config) {
        if (isset($customdata[$element])) {
            $mform->setDefault($element, $customdata[$element]);
        } else if (isset($config->{$element})) {
            $mform->setDefault($element, $config->{$element});
        } else {
            $mform->setDefault($element, $default);
        }
    }

}
