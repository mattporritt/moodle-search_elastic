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
 * Extract text from files using Tika.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic\enrich\text;

use search_elastic\enrich\base\base_enrich;

defined('MOODLE_INTERNAL') || die;

/**
 * Extract text from files using Tika.
 *
 * @package    search_elastic
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tika extends base_enrich {

    /**
     * Array of file mimetypes that enrichment class supports
     * processing of / extracting data from.
     *
     * @var array
     */
    protected $acceptedmime = array(
            'application/pdf',
            'text/html',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'application/vnd.ms-word.document.macroEnabled.12',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'application/vnd.ms-excel.sheet.macroEnabled.12',
            'application/vnd.ms-excel.template.macroEnabled.12',
            'application/vnd.ms-excel.addin.macroEnabled.12',
            'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.presentationml.template',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.presentation',
            'application/epub+zip'
    );

    /**
     * The constructor for the class, will be overwritten in most cases.
     *
     * @param mixed $config Search plugin configuration.
     */
    public function __construct($config) {
        $this->config = $config;
        $this->tikaport = $this->config->tikaport;
        $this->tikahostname = rtrim($this->config->tikahostname, "/");
    }

    /**
     * Returns the step name.
     *
     * @return string human readable step name.
     */
    static public function get_step_name() {
        return get_string('tika', 'search_elastic');
    }

    /**
     * Use tika to extract text from file.
     *
     * @param file $file
     * @param esrequest\client $client client
     * @return string|boolean
     */
    public function extract_text($file, $client) {
        // TODO: add timeout and retries for tika.
        $extractedtext = '';
        $port = $this->tikaport;
        $hostname = $this->tikahostname;
        $url = $hostname . ':'. $port . '/tika/form';
        $filesize = $file->get_filesize();

        if ($filesize <= $this->config->tikasendsize) {
            $response = $client->postfile($url, $file);

            if ($response->getStatusCode() == 200) {
                $extractedtext = (string) $response->getBody();
            }
        }

        return $extractedtext;

    }

    /**
     * Analyse file and return results.
     *
     * @param \stored_file $file The image file to analyze.
     * @return string $filetext Text of file description labels.
     */
    public function analyze_file($file) {
        $filetext = '';

        $client = new \search_elastic\esrequest();
        $filetext = $this->extract_text($file, $client);

        return $filetext;
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
        $mform->addElement('text', 'tikahostname',  get_string ('tikahostname', 'search_elastic'));
        $mform->setType('tikahostname', PARAM_URL);
        $mform->addHelpButton('tikahostname', 'tikahostname', 'search_elastic');
        self::set_default('tikahostname', 'http://127.0.0.1', $mform, $customdata, $config);

        $mform->addElement('text', 'tikaport',  get_string ('tikaport', 'search_elastic'));
        $mform->setType('tikaport', PARAM_INT);
        $mform->addHelpButton('tikaport', 'tikaport', 'search_elastic');
        self::set_default('tikaport', 9998, $mform, $customdata, $config);

        $mform->addElement('text', 'tikasendsize',  get_string ('tikasendsize', 'search_elastic'));
        $mform->setType('tikasendsize', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('tikasendsize', 'tikasendsize', 'search_elastic');
        self::set_default('tikasendsize', 512000000, $mform, $customdata, $config);
    }

}

