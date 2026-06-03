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
 * Elastic search engine unit tests.
 *
 * @package    search_elastic
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/mock_search_area.php');
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/aws_rekognition.php');

/**
 * Elasticsearch engine.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \search_elastic\document
 */
final class document_test extends \advanced_testcase {
    /**
     * Summary of generator
     * @var testing_data_generator
     */
    private $generator;

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        new \search_elastic\engine();
        $this->generator = self::getDataGenerator()->get_plugin_generator('core_search');
        $this->generator->setup();
    }

    public function tearDown(): void {
        // For unit tests before PHP 7, teardown is called even on skip. So only do our teardown if we did setup.
        if ($this->generator) {
            // Moodle DML freaks out if we don't teardown the temp table after each run.
            $this->generator->teardown();
            $this->generator = null;
        }
        parent::tearDown();
    }

    /**
     * Test hightlight string replacement is done correctly.
     */
    public function test_highlight_text(): void {
        $text = 'search test @@HI_S@@book@@HI_E@@ description description';
        $expected = 'search test <span class="highlight">book</span> description description';

        $builder = $this->getMockBuilder('\search_elastic\document');
        $builder->disableOriginalConstructor();
        $stub = $builder->getMock();

        // We're testing a private method, so we need to setup reflector magic.
        $method = new \ReflectionMethod('\search_elastic\document', 'format_text');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke($stub, $text); // Get result of invoked method.

        $this->assertEquals($expected, $proxy);
    }

    /**
     * Test hightlight string replacement is down correctly for multiple replacements.
     */
    public function test_highlight_text_multiple(): void {
        $text = 'this is an @@HI_S@@assignment@@HI_E@@ @@HI_S@@on@@HI_E@@ @@HI_S@@frogs@@HI_E@@ and toads';
        $expected = 'this is an <span class="highlight">assignment on frogs</span> and toads';

        $builder = $this->getMockBuilder('\search_elastic\document');
        $builder->disableOriginalConstructor();
        $stub = $builder->getMock();

        // We're testing a private method, so we need to setup reflector magic.
        $method = new \ReflectionMethod('\search_elastic\document', 'format_text');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke($stub, $text); // Get result of invoked method.

        $this->assertEquals($expected, $proxy);
    }

    /**
     * Test getting enrichment processors.
     */
    public function test_get_enrichment_processors(): void {
        set_config('fileindexing', 1, 'search_elastic');

        $expected = '\search_elastic\enrich\text\plain_text';

        $builder = $this->getMockBuilder('\search_elastic\document');
        $builder->setConstructorArgs(['1', 'core_mocksearch', 'mock_search_area']);
        $stub = $builder->getMock();

        // We're testing a private method, so we need to setup reflector magic.
        $method = new \ReflectionMethod('\search_elastic\document', 'get_enrichment_processors');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke($stub); // Get result of invoked method.

        $this->assertEquals($expected, $proxy[0]);
    }

    /**
     * Test texport file for engine with no text extraction.
     */
    public function test_export_file_for_engine(): void {
        global $CFG;
        // Create file to analyze.
        $fs = get_file_storage();
        $filerecord = [
            'contextid' => 1,
            'component' => 'mod_test',
            'filearea' => 'search',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'testfile.txt'];
        $content = 'All the news that\'s fit to print';
        $file = $fs->create_file_from_string($filerecord, $content);

        // Construct the search object.
        $rec = new \stdClass();
        $rec->content = "elastic";
        $area = new \core_mocksearch\search\mock_search_area();
        $record = $this->generator->create_record($rec);
        $info = unserialize($record->info);

        // Mock out and add missing data to stub record object.
        $builder = $this->getMockBuilder('\search_elastic\document');
        $builder->onlyMethods([]);
        $builder->setConstructorArgs(['1', 'core_mocksearch', 'mock_search_area']);
        $stub = $builder->getMock();

        $stub->set('title', $info->title);
        $stub->set('content', $info->content);
        $stub->set('description1', $info->description1);
        $stub->set('description1', $info->description2);
        $stub->set('contextid', $info->contextid);
        $stub->set('courseid', $info->courseid);
        $stub->set('userid', $info->userid);
        $stub->set('owneruserid', $info->owneruserid);
        $stub->set('modified', $record->timemodified);

        $data = $stub->export_file_for_engine($file);

        $this->assertEquals('core_mocksearch-mock_search_area-1', $data['parentid']);
        $this->assertEquals('2', $data['type']);
        $this->assertEquals('', $data['filetext']);
        $this->assertEquals('6b6cfc16188deb2e2d7ae8512f059cf20f486d27', $data['filecontenthash']);
    }

    /**
     * Test text file extraction
     */
    public function test_export_text_file_for_engine(): void {
        global $CFG;
        set_config('fileindexing', '1', 'search_elastic');

        // Create file to analyze.
        $fs = get_file_storage();
        $filerecord = [
            'contextid' => 1,
            'component' => 'mod_test',
            'filearea' => 'search',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'testfile.txt'];
        $content = 'All the news that\'s fit to print';
        $file = $fs->create_file_from_string($filerecord, $content);

        // Construct the search object.
        $rec = new \stdClass();
        $rec->content = "elastic";
        $area = new \core_mocksearch\search\mock_search_area();
        $record = $this->generator->create_record($rec);
        $info = unserialize($record->info);

        // Mock out and add missing data to stub record object.
        $builder = $this->getMockBuilder('\search_elastic\document');
        $builder->onlyMethods([]);
        $builder->setConstructorArgs(['1', 'core_mocksearch', 'mock_search_area']);
        $stub = $builder->getMock();

        $stub->set('title', $info->title);
        $stub->set('content', $info->content);
        $stub->set('description1', $info->description1);
        $stub->set('description1', $info->description2);
        $stub->set('contextid', $info->contextid);
        $stub->set('courseid', $info->courseid);
        $stub->set('userid', $info->userid);
        $stub->set('owneruserid', $info->owneruserid);
        $stub->set('modified', $record->timemodified);

        $filearray = $stub->export_file_for_engine($file);
        $this->assertEquals($content, $filearray['filetext']);
    }
}
