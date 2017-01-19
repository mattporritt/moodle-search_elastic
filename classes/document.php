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

class document extends \core_search\document {

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

        $data['type'] = \core_search\manager::TYPE_FILE;
        $data['title'] = $file->get_filename();
        $data['modified'] = $file->get_timemodified();
        $data['filetext'] = $filetext;

        return $data;
    }
}
