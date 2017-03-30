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
     * Use tika to extract text from file.
     * @param file $file
     * @return string|boolean
     */
    private function extract_text($file) {
        $config = get_config('search_elastic');
        $extractedtext = '';
        $client = new \curl();
        $port = $config->tikaport;
        $hostname = rtrim($config->tikahostname, "/");
        $url = $hostname . ':'. $port . '/tika/form';

        $response = $client->post($url, array('file' => $file));
        if ($client->info['http_code'] === 200) {
            $extractedtext = $response;
        }

        return $extractedtext;

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

        // Pass the file off to tika to extract content.
        $filetext = $this->extract_text($file);

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
     * Returns all required fields definitions.
     *
     * @return array
     */
    public static function get_required_fields_definition() {
        return static::$requiredfields;
    }
}
