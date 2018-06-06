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
 * Elastic search engine enrichment text tika unit tests.
 *
 * @package    search_elastic
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;


use \GuzzleHttp\Handler\MockHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Middleware;
use \GuzzleHttp\Psr7\Response;

/**
 * Elastic search engine enrichment text tika unit tests.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_elastic_text_tika_testcase extends advanced_testcase {

     /**
      * Test binary text file extraction request
      */
    public function test_export_text_tika() {
        $this->resetAfterTest();
        global $CFG;
        set_config('tikahostname', 'http://127.0.0.1', 'search_elastic');
        set_config('tikaport', 9998, 'search_elastic');
        set_config('tikasendsize', 512000000, 'search_elastic');

        $config = get_config('search_elastic');

        // Create file to analyze.
        $fs = get_file_storage();
        $filerecord = array(
                'contextid' => 1,
                'component' => 'mod_test',
                'filearea' => 'search',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => 'testfile.pdf');
        $content = 'All the news that\'s fit to print';
        $fileurl = $CFG->dirroot . '/search/engine/elastic/tests/fixtures/test.pdf';
        $file = $fs->create_file_from_pathname($filerecord, $fileurl);

        // Create es request client with mocked stack.
        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain'], $content)
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $esclient = new \search_elastic\esrequest($stack);
        $tika = new \search_elastic\enrich\text\tika($config);

        $result = $tika->extract_text($file, $esclient);
        $this->assertEquals($content, $result);
    }

}
