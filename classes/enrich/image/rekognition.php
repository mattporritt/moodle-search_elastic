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

require($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');

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

    /**
     * The constructor for the class, will be overwritten in most cases.
     *
     * @param mixed $config Search plugin configuration.
     */
    public function __construct($config) {
        $this->config = $config;
        $this->rekregion = $this->config->rekregion;
        $this->rekkey = $this->config->rekkeyid;
        $this->reksecret = $this->config->reksecretkey;
        $this->maxlabels = $this->config->maxlabels;
        $this->minconfidence = $this->config->minconfidence;
    }


    /**
     * Returns the step name.
     *
     * @return string human readable step name.
     */
    static public function get_enrich_name() {
        return get_string('aws', 'search_elastic');
    }

    /**
     * Create AWS Rekognition client.
     *
     * @return client $rekclient Rekognition client.
     */
    public function get_rekognition_client() {
        $rekclient = new \Aws\Rekognition\RekognitionClient([
            'version' => 'latest',
            'region'  => $this->rekregion,
            'credentials' => [
                'key'    => $this->rekkey,
                'secret' => $this->reksecret
            ]
        ]);
        return $rekclient;
    }

    /**
     * Analyse file and return results.
     *
     * @param \stored_file $file The image file to analyze.
     * @return string $imagetext Text of file description labels.
     */
    public function analyze_file($file) {
        $imageinfo = $file->get_imageinfo();
        $imagetext = '';
        $cananalyze = false;
        $filesize = $file->get_filesize();

        // Check if we can analyze this type of file.
        if ($imageinfo['height'] >= 80 &&
            $imageinfo['width'] >= 80 &&
            $filesize <= 5000000
            ) {
                $cananalyze = true;
        }

        if ($cananalyze) {
            // Send image to AWS Rekognition for analysis.
            $client = $this->get_rekognition_client();

            // Detect labels from Rekognition.
            $result = $client->detectLabels(array(
                'Image' => array(
                    'Bytes' => $file->get_content(),
                ),
                'Attributes' => array('ALL'),
                'MaxLabels' => (int)$this->maxlabels,
                'MinConfidence' => (float)$this->minconfidence
            ));

            // Process the results from AWS Rekognition service
            // and extra result labels.
            $labelarray = array ();
            foreach ($result['Labels'] as $label) {
                $labelarray[] = $label['Name'];
            }
            $imagetext = implode(', ', $labelarray);

            // Detect text from reckognition
            $result = $client->detectText(array(
                'Image' => array(
                    'Bytes' => $file->get_content(),
                )
            ));

            // Process results.
            $textarray = array();
            foreach ($result['TextDetections'] as $text) {
                $textarray[] = $text['DetectedText'];
            }
            $imagetext .= implode(', ', $textarray);
        }

        return $imagetext;
    }

    /**
     * A callback to add fields to the enrich form, specific to enrichment class.
     *
     * @param \moodleform $form
     * @param \MoodleQuickForm $mform
     * @param mixed $customdata
     * @param mixed $config
     */
    static public function form_definition_extra($form, $mform, $customdata, $config) {
        $mform->addElement('text', 'rekkeyid',  get_string ('rekkeyid', 'search_elastic'));
        $mform->setType('rekkeyid', PARAM_TEXT);
        $mform->addHelpButton('rekkeyid', 'rekkeyid', 'search_elastic');
        self::set_default('rekkeyid', '', $mform, $customdata, $config);

        $mform->addElement('text', 'reksecretkey',  get_string ('reksecretkey', 'search_elastic'));
        $mform->setType('reksecretkey', PARAM_TEXT);
        $mform->addHelpButton('reksecretkey', 'reksecretkey', 'search_elastic');
        self::set_default('reksecretkey', '', $mform, $customdata, $config);

        $mform->addElement('text', 'rekregion',  get_string ('rekregion', 'search_elastic'));
        $mform->setType('rekregion', PARAM_TEXT);
        $mform->addHelpButton('rekregion', 'rekregion', 'search_elastic');
        self::set_default('rekregion', 'us-west-2', $mform, $customdata, $config);

        $mform->addElement('text', 'maxlabels',  get_string ('maxlabels', 'search_elastic'));
        $mform->setType('maxlabels', PARAM_INT);
        $mform->addHelpButton('maxlabels', 'maxlabels', 'search_elastic');
        self::set_default('maxlabels', 10, $mform, $customdata, $config);

        $mform->addElement('text', 'minconfidence',  get_string ('minconfidence', 'search_elastic'));
        $mform->setType('minconfidence', PARAM_INT);
        $mform->addHelpButton('minconfidence', 'minconfidence', 'search_elastic');
        self::set_default('minconfidence', 90, $mform, $customdata, $config);
    }

}
