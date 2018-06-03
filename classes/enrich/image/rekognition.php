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
 * Extract imformation from image files using AWS Rekognition.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic\enrich\image;

use search_elastic\enrich\base\base_enrich;

defined('MOODLE_INTERNAL') || die;

/**
 * Extract imformation from image files using AWS Rekognition.
 *
 * @package    search_elastic
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rekognition extends base_enrich {

    /**
     * Array of file mimetypes that enrichment class supports
     * processing of / extracting data from.
     *
     * @var array
     */
    protected $acceptedmime = array(
        'image/jpeg',
        'image/png'
    );

    static public function get_step_name() {
        return get_string('aws', 'search_elastic');
    }

    /**
     * Checks if supplied file is can be analyzed by this enrichment class.
     *
     * @param \stored_file $file File to check.
     * @return boolean
     */
    public function can_analyze($file) {
        // TODO: properly override this for rekognition to check dimensions etc.
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
    public function analyze_file($file) {
        return '';
    }

    /**
     * A callback to add fields to the enrich form, specific to enrichment class.
     *
     * @param \moodleform $form
     * @param \MoodleQuickForm $mform
     * @param mixed $customdata
     */
    static public function form_definition_extra($form, $mform, $customdata, $config) {
        $mform->addElement('text', 'rekkeyid',  get_string ('rekkeyid', 'search_elastic'));
        $mform->setType('rekkeyid', PARAM_TEXT);
        $mform->addHelpButton('rekkeyid', 'rekkeyid', 'search_elastic');
        self::setDefault('rekkeyid', '', $mform, $customdata, $config);

        $mform->addElement('text', 'reksecretkey',  get_string ('reksecretkey', 'search_elastic'));
        $mform->setType('reksecretkey', PARAM_TEXT);
        $mform->addHelpButton('reksecretkey', 'reksecretkey', 'search_elastic');
        self::setDefault('reksecretkey', '', $mform, $customdata, $config);

        $mform->addElement('text', 'rekregion',  get_string ('rekregion', 'search_elastic'));
        $mform->setType('rekregion', PARAM_TEXT);
        $mform->addHelpButton('rekregion', 'rekregion', 'search_elastic');
        self::setDefault('rekregion', 'us-west-2', $mform, $customdata, $config);

        $mform->addElement('text', 'maxlabels',  get_string ('maxlabels', 'search_elastic'));
        $mform->setType('maxlabels', PARAM_INT);
        $mform->addHelpButton('maxlabels', 'maxlabels', 'search_elastic');
        self::setDefault('maxlabels', 10, $mform, $customdata, $config);

        $mform->addElement('text', 'minconfidence',  get_string ('minconfidence', 'search_elastic'));
        $mform->setType('minconfidence', PARAM_INT);
        $mform->addHelpButton('minconfidence', 'minconfidence', 'search_elastic');
        self::setDefault('minconfidence', 90, $mform, $customdata, $config);
    }

}
