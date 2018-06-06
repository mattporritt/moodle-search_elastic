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
 * Elastic search engine enrichment text plain unit tests.
 *
 * @package    search_elastic
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Elastic search engine enrichment text plain unit tests.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_elastic_text_plain_text_testcase extends advanced_testcase {

    /**
     * Test binary text file extraction request.
     */
    public function test_export_text_plain_text() {
        $this->resetAfterTest();
        global $CFG;
        $config = get_config('search_elastic');

        // Create file to analyze.
        $fs = get_file_storage();
        $filerecord = array(
                'contextid' => 1,
                'component' => 'mod_test',
                'filearea' => 'search',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => 'testfile.txt');
        $content = 'All the news that\'s fit to print';
        $file = $fs->create_file_from_string($filerecord, $content);

        $plaintext = new \search_elastic\enrich\text\plain_text($config);

        $result = $plaintext->analyze_file($file);
        $this->assertEquals($content, $result);
    }

}
