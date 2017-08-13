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
 * Document representation.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic;

defined('MOODLE_INTERNAL') || die();

require($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Elasticsearch engine.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class document extends \core_search\document {
    /**
     * All required fields any doc should contain.
     *
     * Search engine plugins are responsible of setting their appropriate field types and map these naming to whatever format
     * they need.
     *
     * This format suits Elasticsearh mapping
     *
     * @var array
     */
    protected static $requiredfields = array(
            'id' => array(
                    'type' => 'string',
                    'index' => 'not_analyzed'
            ),
            'parentid' => array(
                    'type' => 'string',
                    'index' => 'not_analyzed'
            ),
            'itemid' => array(
                    'type' => 'integer'
            ),
            'title' => array(
                    'type' => 'string'
            ),
            'content' => array(
                    'type' => 'string'
            ),
            'contextid' => array(
                    'type' => 'integer'
            ),
            'areaid' => array(
                    'type' => 'string',
                    'index' => 'not_analyzed'
            ),
            'type' => array(
                    'type' => 'integer'
            ),
            'courseid' => array(
                    'type' => 'integer'
            ),
            'owneruserid' => array(
                    'type' => 'integer'
            ),
            'modified' => array(
                    'type' => 'date',
                    'format' => 'epoch_second'
            ),
    );

    /**
     * Array of file mimetypes that contain plain text that can be fed directly
     * into Elsstic search without text extraction processing.
     *
     * @var array
     */
    protected static $acceptedtext = array(
            'text/html',
            'text/plain',
            'text/csv',
            'text/css',
            'text/javascript',
            'text/ecmascript'
    );

    /**
     * Array of file mimetypes that are compatible with AWS Rekognition.
     * Image types not in this list wont be processed. Currently Rekognition
     * only supports JPEG and PNG formats.
     *
     * @var array
     */
    protected static $acceptedimages = array(
            'image/jpeg',
            'image/png'
    );

    /**
     * Constructor for document class.
     * Makes relevant config available and bootstraps
     * Rekognition client.
     *
     * @param int $itemid An id unique to the search area
     * @param string $componentname The search area component Frankenstyle name
     * @param string $areaname The area name (the search area class name)
     * @return void
     */
    public function __construct($itemid, $componentname, $areaname) {
        parent::__construct($itemid, $componentname, $areaname);
        $this->config = get_config('search_elastic');
        $this->imageindex = (bool)$this->config->imageindex;
        $this->rekregion = $this->config->rekregion;
        $this->rekkey = $this->config->rekkeyid;
        $this->reksecret = $this->config->reksecretkey;
        $this->maxlabels = $this->config->maxlabels;
        $this->minconfidence = $this->config->minconfidence;
        $this->tikaport = $this->config->tikaport;
        $this->tikahostname = rtrim($this->config->tikahostname, "/");
    }

    /**
     * Use tika to extract text from file.
     * @param file $file
     * @return string|boolean
     */
    public function extract_text($file, $client) {
        // TODO: add timeout and retries for tika.
        $extractedtext = '';
        $port = $this->tikaport;
        $hostname = $this->tikahostname;
        $url = $hostname . ':'. $port . '/tika/form';

        $response = $client->postfile($url, $file);

        if ($response->getStatusCode() == 200) {
            $extractedtext = (string) $response->getBody();
        }

        return $extractedtext;

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
     * Analyse image using Rekognition.
     *
     * @param \stored_file $file The image file to analyze.
     * @return string $imagetext Text of file description labels.
     */
    private function analyse_image($file) {
        $imageinfo = $file->get_imageinfo();
        $imagetext = '';
        $cananalyze = false;
        $acceptedtypes = $this->get_accepted_image_types();
        $imagemime = $imageinfo['mimetype'];
        $acceptedimage = in_array($imagemime, $acceptedtypes);
        $filesize = $file->get_filesize();

        // If we are not indexing images return early.
        if (!$this->imageindex) {
            return $imagetext;
        }

        // Check if we can analyze this type of file.
        if ($acceptedimage &&
                $imageinfo['height'] >= 80 &&
                $imageinfo['width'] >= 80 &&
                $filesize <= 5000000
                ) {
                    $cananalyze = true;
        }

        if ($cananalyze) {
            // Send image to AWS Rekognition for analysis.
            $client = $this->get_rekognition_client();
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
        }

        return $imagetext;
    }

    /**
     * Checks if supplied file is plain text that can be directly fed
     * to Elasticsearch without further processing.
     *
     * @param \stored_file $file File to check.
     * @return boolean
     */
    private function is_text($file) {
        $mimetype = $file->get_mimetype();
        $istext = false;

        if (in_array($mimetype, $this->get_accepted_text_types())) {
            $istext = true;
        }

        return $istext;
    }

    /**
     * Apply any defaults to unset fields before export. Called after document building, but before export.
     *
     * Sub-classes of this should make sure to call parent::apply_defaults().
     */
    protected function apply_defaults() {
        parent::apply_defaults();
        // Set the default type, TYPE_TEXT.
        if (!isset($this->data['parentid'])) {
            $this->data['parentid'] = $this->data['id'];
        }
    }

    /**
     * Export the data for the given file in relation to this document.
     *
     * @param \stored_file $file The stored file we are talking about.
     * @return array
     */
    public function export_file_for_engine($file) {
        $data = $this->export_for_engine();
        $imageinfo = $file->get_imageinfo();
        $filetext = '';

        if ($imageinfo) {
            // If file is image send for analysis.
            $filetext = $this->analyse_image($file);
        } else if ($this->is_text($file)) {
            // If file is text don't bother converting.
            $filetext = $file->get_content();
        } else {
            // Pass the file off to Tika to extract content.
            $client = new \search_elastic\esrequest();
            $filetext = $this->extract_text($file, $client);
        }

        // Construct the document.
        unset($data['content']);
        unset($data['description1']);
        unset($data['description2']);

        $data['id'] = $file->get_id();
        $data['parentid'] = $this->data['id'];
        $data['type'] = \core_search\manager::TYPE_FILE;
        $data['title'] = $file->get_filename();
        $data['modified'] = $file->get_timemodified();
        $data['filetext'] = $filetext;
        $data['filecontenthash'] = $file->get_contenthash();

        return $data;
    }

    /**
     * Export the document data to be used as webservice result.
     * @return array
     */
    public function export_for_webservice() {
        list($componentname, $areaname) = \core_search\manager::extract_areaid_parts($this->get('areaid'));
        $title = $this->is_set('title') ? $this->format_text($this->get('title')) : '';
        $data = [
            'componentname' => $componentname,
            'areaname' => $areaname,
            'courseurl' => course_get_url($this->get('courseid'))->out(),
            'coursefullname' => format_string($this->get('coursefullname'), true, array('context' => $this->get('contextid'))),
            'modified' => userdate($this->get('modified')),
            'title' => ($title !== '') ? $title : get_string('notitle', 'search'),
            'docurl' => $this->get_doc_url()->out(),
            'content' => $this->is_set('content') ? $this->format_text($this->get('content')) : null,
            'contexturl' => $this->get_context_url()->out(),
            'description1' => $this->is_set('description1') ? $this->format_text($this->get('description1')) : null,
            'description2' => $this->is_set('description2') ? $this->format_text($this->get('description2')) : null,
        ];
        // Now take any attached any files.
        $files = $this->get_files();
        if (!empty($files)) {
            if (count($files) > 1) {
                $filenames = array();
                foreach ($files as $file) {
                    $filenames[] = format_string($file->get_filename(), true, array('context' => $this->get('contextid')));
                }
                $data['multiplefiles'] = true;
                $data['filenames'] = $filenames;
            } else {
                $file = reset($files);
                $data['filename'] = format_string($file->get_filename(), true, array('context' => $this->get('contextid')));
            }
        }
        if ($this->is_set('userid')) {
            $data['userurl'] = new \moodle_url(
                    '/user/view.php',
                    array('id' => $this->get('userid'), 'course' => $this->get('courseid'))
                    );
            $data['userfullname'] = format_string($this->get('userfullname'),
                    true,
                    array('context' => $this->get('contextid'))
                    );
        }
        return $data;
    }

    /**
     * Returns all required fields definitions.
     *
     * @return array
     */
    public static function get_required_fields_definition() {
        return static::$requiredfields;
    }

    /**
     * Returns all accepted text file types.
     *
     * @return array
     */
    public static function get_accepted_text_types() {
        return self::$acceptedtext;
    }

    /**
     * Returns all accepted text file types.
     *
     * @return array
     */
    public static function get_accepted_image_types() {
        return self::$acceptedimages;
    }
}