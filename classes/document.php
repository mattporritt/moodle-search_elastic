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
                    'type' => 'keyword'
            ),
            'parentid' => array(
                    'type' => 'keyword'
            ),
            'itemid' => array(
                    'type' => 'integer'
            ),
            'title' => array(
                    'type' => 'text'
            ),
            'content' => array(
                    'type' => 'text'
            ),
            'contextid' => array(
                    'type' => 'integer'
            ),
            'areaid' => array(
                    'type' => 'keyword'
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
    * All optional fields docs can contain.
    *
    * Search engine plugins are responsible of setting their appropriate field types and map these
    * naming to whatever format they need.
    *
    * This format suits Elasticsearh mapping
    *
    * @var array
    */
    protected static $optionalfields = array(
            'userid' => array(
                    'type' => 'integer'
            ),
            'groupid' => array(
                    'type' => 'integer'
            ),
            'description1' => array(
                    'type' => 'text'
            ),
            'description2' => array(
                    'type' => 'text'
            ),
            'filetext' => array(
                    'type' => 'text'
            ),
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
        $this->fileindexing = (isset($this->config->fileindexing) ? (bool)$this->config->fileindexing : false);
    }

    /**
     * Overwritten to use markdown format as we use markdown for solr highlighting.
     *
     * @return int
     */
    protected function get_text_format() {
        return FORMAT_HTML;
    }

    /**
     * Formats a text string coming from the search engine.
     *
     * @param  string $text Text to format
     * @return string HTML text to be renderer
     */
    protected function format_text($text) {
        // Since we allow output for highlighting, we need to encode html entities.
        // This ensures plaintext html chars don't become valid html.
        $out = s($text);

        $startcount = 0;
        $endcount = 0;

        // Remove end/start pairs that span a few common seperation characters. Allows us to highlight phrases instead of words.
        $regex = '|'.query::HIGHLIGHT_END.'([ .,-]{0,3})'.query::HIGHLIGHT_START.'|';
        $out = preg_replace($regex, '$1', $out);

        // Now replace our start and end highlight markers.
        $out = str_replace(query::HIGHLIGHT_START, '<span class="highlight">', $out, $startcount);
        $out = str_replace(query::HIGHLIGHT_END, '</span>', $out, $endcount);

        // This makes sure any highlight tags are balanced, incase truncation or the highlight text contained our markers.
        while ($startcount > $endcount) {
            $out .= '</span>';
            $endcount++;
        }
        while ($startcount < $endcount) {
            $out = '<span class="highlight">' . $out;
            $endcount++;
        }

        return parent::format_text($out);
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
     * Get the enabled enrichment processors
     *
     * @return array $processors
     */
    private function get_enrichment_processors() {
        $processors = array();

        if ($this->fileindexing == true) { // Only look for processors if file indexing is enabled.

            $processors[] = '\search_elastic\enrich\text\plain_text';  // Plain text processing is always enabled.

            // Text extraction processing.
            if (isset($this->config->fileindexselect) && $this->config->fileindexselect != '') {
                $processors[] = $this->config->fileindexselect;
            }

            // Image recognition processing.
            if (isset($this->config->imageindexselect) && $this->config->imageindexselect != '') {
                $processors[] = $this->config->imageindexselect;
            }
        }

        return $processors;
    }

    /**
     * Export the data for the given file in relation to this document.
     *
     * @param \stored_file $file The stored file we are talking about.
     * @return array
     */
    public function export_file_for_engine($file) {
        $data = $this->export_for_engine();
        $filetext = '';

        $processors = $this->get_enrichment_processors();  // Make a list of enabled enrichment processors.
        foreach ($processors as $processor) {  // Loop thorugh processors to see if they support this files mimetype.
            $proc = new $processor($this->config);
            if ($proc->can_analyze($file)) {  // Sequentially process the file apppending results to $filetext.
                $filetext .= $proc->analyze_file($file);
            }
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
     * Returns all required field definitions.
     *
     * @return array
     */
    public static function get_required_fields_definition() {
        return static::$requiredfields;
    }

    /**
     * Returns all optional field definitions.
     *
     * @return array
     */
    public static function get_optional_fields_definition() {
        return static::$optionalfields;
    }
}