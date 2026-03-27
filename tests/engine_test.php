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
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');
require_once($CFG->dirroot . '/search/tests/fixtures/mock_search_area.php');
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/mock_search_area.php');
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/testable_engine.php');

use core_mocksearch\search\mock_boost_area;
use core_mocksearch\search\mock_search_area;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Elasticsearch engine.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \search_elastic\engine
 */
final class engine_test extends \advanced_testcase {
    /**
     * @var \core_search::manager
     */
    protected $search = null;

    /**
     * @var Instance of core_search_generator.
     */
    protected $generator = null;

    /**
     * @var Instance of testable_engine.
     */
    protected $engine = null;

    /**
     * @var string the Apache Lucene version of the attached Elasticsearch / OpenSearch service.
     */
    protected string $luceneversion;

    /**
     * @var mock_search_area
     */
    protected mock_search_area $area;

    /**
     * @var mock_boost_area
     */
    protected mock_boost_area $areaboost;

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        set_config('enableglobalsearch', true);

        // Allow setting of test server info via Env Var or define
        // to cater for mulitiple test setups.
        $hostname = getenv('TEST_SEARCH_ELASTIC_HOSTNAME');
        $port = getenv('TEST_SEARCH_ELASTIC_PORT');
        $index = getenv('TEST_SEARCH_ELASTIC_INDEX');

        if (!$hostname && defined('TEST_SEARCH_ELASTIC_HOSTNAME')) {
            $hostname = TEST_SEARCH_ELASTIC_HOSTNAME;
        }
        if (!$port && defined('TEST_SEARCH_ELASTIC_PORT')) {
            $port = TEST_SEARCH_ELASTIC_PORT;
        }
        if (!$index && defined('TEST_SEARCH_ELASTIC_INDEX')) {
            $index = TEST_SEARCH_ELASTIC_INDEX;
        }

        if (!$hostname || !$port || !$index) {
            $this->markTestSkipped('Elastic extension test server not set.');
        }

        set_config('hostname', $hostname, 'search_elastic');
        set_config('port', $port, 'search_elastic');
        set_config('index', $index, 'search_elastic');

        $this->generator = self::getDataGenerator()->get_plugin_generator('core_search');
        $this->generator->setup();

        $this->engine = new \search_elastic\testable_engine();
        $this->luceneversion = $this->engine->get_es_lucene_version();
        $this->search = \testable_core_search::instance($this->engine);
        $areaid = \core_search\manager::generate_areaid('core_mocksearch', 'mock_search_area');
        $this->search->add_search_area($areaid, new \core_mocksearch\search\mock_search_area());
        $this->area = new \core_mocksearch\search\mock_search_area();
        $areaboostid = \core_search\manager::generate_areaid('core_mocksearch', 'mock_boost_area');
        $this->search->add_search_area($areaboostid, new \core_mocksearch\search\mock_boost_area());
        $this->areaboost = new \core_mocksearch\search\mock_boost_area();

        $this->setAdminUser();
        $this->search->index(true);
    }

    public function tearDown(): void {
        // For unit tests before PHP 7, teardown is called even on skip. So only do our teardown if we did setup.
        if ($this->generator) {
            // Moodle DML freaks out if we don't teardown the temp table after each run.
            $this->generator->teardown();
            $this->generator = null;
        }
        $this->engine->delete();
        sleep(1);
        parent::tearDown();
    }

    /**
     * Simple data provider to allow tests to be run with file indexing on and off.
     */
    public function file_indexing_provider() {
        return [
                'file-indexing-off' => [0],
        ];
    }

    /**
     * Provides values to test_is_server_ready
     *
     * @return array
     */
    public static function is_server_ready_provider(): array {
        return [
          '200' => [
              'code' => 200,
              'ok' => true,
              'host' => null,
              'searchengine' => 'elastic',
              'status' => true,
          ],
          '404' => [
              'code' => 404,
              'ok' => false,
              'host' => null,
              'searchengine' => 'elastic',
              'status' => '404',
          ],
          '503' => [
              'code' => 503,
              'ok' => false,
              'host' => 'localhost',
              'searchengine' => 'elastic',
              'status' => '503',
          ],
          'na' => [
              'code' => 200,
              'ok' => false,
              'host' => '',
              'searchengine' => 'other',
              'status' => get_string('connection:na', 'search_elastic'),
          ],
        ];
    }

    /**
     * Test check if Elasticsearch server is ready.
     *
     * @param int $code Simulated HTTP status code
     * @param bool $expectedok If the server should be expected to be ready/ok.
     * @param string|null $host elastic host.
     * @param string $searchengine Selected search engine.
     * @param string|bool $expectedstatus Expected return status.
     * @dataProvider is_server_ready_provider
     */
    public function test_is_server_ready(
        int $code,
        bool $expectedok,
        string|null $host,
        string $searchengine,
        string|bool $expectedstatus
    ): void {
        // Create a mock stack and queue a response.
        $mock = new MockHandler([
            new Response($code, ['Content-Type' => 'application/json']),
        ]);
        if (isset($host)) {
            set_config('hostname', $host, 'search_elastic');
        }
        set_config('searchengine', $searchengine);

        $stack = HandlerStack::create($mock);

        $engine = new \search_elastic\engine();
        $result = $engine->is_server_ready($stack);

        if ($expectedok) {
            $this->assertTrue(is_bool($result));
            $this->assertEquals($result, $expectedstatus);
        } else {
            $this->assertTrue(is_string($result));
            $this->assertTrue($result === $expectedstatus || strpos($result, $expectedstatus) != false);
        }
    }

    /**
     * Test deleting docs by type id.
     */
    public function test_delete_by_areaid(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "elastic";
        $area = new \core_mocksearch\search\mock_search_area();
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // Delete all entries in the area.
        $this->engine->delete('core_mocksearch-mock_search_area');

        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'elastic';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        // Execute the search.
        $results = $this->search->search($querydata);

        // Check the results there shouldn't be any.
        $this->assertEquals(count($results), 0);
    }

    /**
     * Test mapping updates for old Apache Lucene version 7 of Elasticsearch / OpenSearch.
     */
    public function test_get_mapping_old(): void {
        $mapping = $this->engine->get_mapping(7);

        // Check mapping has been updated.
        $this->assertEquals($mapping['mappings']['doc']['properties']['id']['type'], 'keyword');
        ;
        $this->assertEquals($mapping['mappings']['doc']['properties']['parentid']['type'], 'keyword');
        ;
        $this->assertEquals($mapping['mappings']['doc']['properties']['title']['type'], 'text');
        $this->assertEquals($mapping['mappings']['doc']['properties']['content']['type'], 'text');
        $this->assertEquals($mapping['mappings']['doc']['properties']['areaid']['type'], 'keyword');
    }

    /**
     * Test mapping updates for new Apache Lucene version 8 of Elasticsearch / OpenSearch.
     */
    public function test_get_mapping(): void {
        $mapping = $this->engine->get_mapping(8);

        // Check mapping has not been updated.
        $this->assertEquals($mapping['mappings']['properties']['id']['type'], 'keyword');
        ;
        $this->assertEquals($mapping['mappings']['properties']['parentid']['type'], 'keyword');
        ;
        $this->assertEquals($mapping['mappings']['properties']['title']['type'], 'text');
        $this->assertEquals($mapping['mappings']['properties']['content']['type'], 'text');
        $this->assertEquals($mapping['mappings']['properties']['areaid']['type'], 'keyword');
    }

    /**
     * Test the actual basic search functionality.
     * Make sure we can index a document and get the content back via search
     */
    public function test_basic_search(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "elastic";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'elastic';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        // Execute the search.
        $results = $this->search->search($querydata);

        // Check the results.
        $this->assertEquals($results[0]->get('content'), '@@HI_S@@elastic@@HI_E@@');
    }

    /**
     * Test results are returned for multiple term search.
     */
    public function test_multi_term_search(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is a test quiz on frogs and toads";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        // Multi term in order query.
        $querydata = new \stdClass();
        $querydata->q = 'test quiz';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(
            $results[0]->get('content'),
            'this is a @@HI_S@@test@@HI_E@@ @@HI_S@@quiz@@HI_E@@ on frogs and toads'
        ); // Check the results.

        // Multi term out of order query.
        $querydata = new \stdClass();
        $querydata->q = 'quiz test';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(
            $results[0]->get('content'),
            'this is a @@HI_S@@test@@HI_E@@ @@HI_S@@quiz@@HI_E@@ on frogs and toads'
        ); // Check the results.

        // Multi term partial words query.
        $querydata = new \stdClass();
        $querydata->q = 'test frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(
            $results[0]->get('content'),
            'this is a @@HI_S@@test@@HI_E@@ quiz on @@HI_S@@frogs@@HI_E@@ and toads'
        ); // Check the results.
    }

    /**
     * Test results are returned for modifier term search.
     */
    public function test_modifier_search(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is a quiz on fish and birds";
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        $rec3 = new \stdClass();
        $rec3->content = "this is an activity about volcanic rocks";
        $area = $this->area;
        $record3 = $this->generator->create_record($rec3);
        $doc3 = $area->get_document($record3);
        $this->engine->add_document($doc3, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        // Multi term in order query.
        $querydata = new \stdClass();
        $querydata->q = 'assignment AND frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(
            $results[0]->get('content'),
            'this is an @@HI_S@@assignment@@HI_E@@ on @@HI_S@@frogs@@HI_E@@ and toads'
        ); // Check the results.
        $this->assertEquals(count($results), 1);

        // Multi term out of order query.
        $querydata = new \stdClass();
        $querydata->q = 'assignment OR fish';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(count($results), 2);
    }


    /**
     * Test results are returned for filtered search.
     * Filter courses.
     */
    public function test_course_filter_search(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->courseid = 1;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is an assignment on frogs and toads";
        $rec2->courseid = 2;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'assignment on frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->courseids = [1 ];

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(
            $results[0]->get('content'),
            'this is an @@HI_S@@assignment@@HI_E@@ @@HI_S@@on@@HI_E@@ @@HI_S@@frogs@@HI_E@@ and toads'
        ); // Check the results.
        $this->assertEquals(count($results), 1);
    }

    /**
     * Test results are returned for filtered search.
     * Filter areas.
     */
    public function test_area_filter_search(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'assignment on frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->areaids = ['mod_book-chapter' ];

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(count($results), 0);

        $querydata = new \stdClass();
        $querydata->q = 'assignment on frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->areaids = ['core_mocksearch-mock_search_area' ];

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(count($results), 1);
    }

    /**
     * Test results are returned for filtered search.
     * Filter courses and areas.
     */
    public function test_course_area_filter_search(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->courseid = 1;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is an assignment on frogs and toads";
        $rec2->courseid = 2;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'assignment on frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->courseids = [1 ];
        $querydata->areaids = ['core_mocksearch-mock_search_area' ];

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(
            $results[0]->get('content'),
            'this is an @@HI_S@@assignment@@HI_E@@ @@HI_S@@on@@HI_E@@ @@HI_S@@frogs@@HI_E@@ and toads'
        ); // Check the results.
        $this->assertEquals(count($results), 1);
    }

    /**
     * Test results are returned for filtered search.
     * Filter courses and areas.
     */
    public function test_course_area_boosting(): void {
        set_config('boost_core_mocksearch_mock_boost_area', 20, 'search_elastic');

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is a quiz on fish and frogs";
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        $rec3 = new \stdClass();
        $rec3->content = "this is an assignment about volcanic rocks";
        $area = $this->areaboost;
        $record3 = $this->generator->create_record($rec3);
        $doc3 = $area->get_document($record3);
        $this->engine->add_document($doc3, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'assignment frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        // Execute the search.
        $results = $this->search->search($querydata);

        // Check the results.
        $this->assertEquals($results[0]->get('content'), 'this is an @@HI_S@@assignment@@HI_E@@ about volcanic rocks');
    }

    /**
     * Test result highlighting is applied.
     */
    public function test_highlight_result(): void {
        $result = new \stdClass();
        $result->highlight = new \stdClass();
        $result->_source = new \stdClass();
        $result->highlight->content = ['search test @@HI_S@@book@@HI_E@@ description description'];
        $result->_source->content = 'search test @@HI_S@@book@@HI_E@@ description description';

        $this->engine->highlight_result($result);
    }

    /**
     * Test context order prioritisation course level.
     */
    public function test_location_boosting(): void {
        $course = self::getDataGenerator()->create_course();
        $courseid = $course->id;
        $coursecontext = \context_course::instance($courseid);

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->courseid = $courseid;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is a quiz on fish and frogs";
        $rec->courseid = 2;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'fish and frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->order = 'location';
        $querydata->context = $coursecontext;

        // Execute the search.
        $results = $this->search->search($querydata);

        // Check the results.
        $this->assertEquals(
            $results[0]->get('content'),
            'this is an assignment on @@HI_S@@frogs@@HI_E@@ @@HI_S@@and@@HI_E@@ toads'
        );
    }

    /**
     * Test context order prioritisation activity level.
     */
    public function test_location_boosting_activity(): void {
        // Generate course.
        $course = self::getDataGenerator()->create_course();
        $courseid = $course->id;

        // Generate forum.
        $now = time();
        $forum = self::getDataGenerator()->create_module('forum', ['course' => $courseid]);
        $forumid = $forum->id;
        $cm = get_coursemodule_from_instance('forum', $forumid, $courseid);
        $forumcontext = \context_module::instance($cm->id);

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->courseid = $courseid;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is a quiz on fish and frogs";
        $rec->courseid = 2;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'fish and frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->order = 'location';
        $querydata->context = $forumcontext;

        // Execute the search.
        $results = $this->search->search($querydata);

        // Check the results.
        $this->assertEquals(
            $results[0]->get('content'),
            'this is an assignment on @@HI_S@@frogs@@HI_E@@ @@HI_S@@and@@HI_E@@ toads'
        );
    }

    /**
     * Test timerange search.
     */
    public function test_timestart_search(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->courseid = 1;
        $rec->timemodified = 123456;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is an assignment on frogs and toads";
        $rec2->courseid = 1;
        $rec2->timemodified = 654321;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = '*';
        $querydata->timestart = 123457;
        $querydata->timeend = 0;

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals($results[0]->get('modified'), 654321); // Check the results.
        $this->assertEquals(count($results), 1);
    }

    /**
     * Test timerange search.
     */
    public function test_timeend_search(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->courseid = 1;
        $rec->timemodified = 123456;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is an assignment on frogs and toads";
        $rec2->courseid = 1;
        $rec2->timemodified = 654321;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = '*';
        $querydata->timestart = 0;
        $querydata->timeend = 123457;

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals($results[0]->get('modified'), 123456); // Check the results.
        $this->assertEquals(count($results), 1);
    }

    /**
     * Test timerange search.
     */
    public function test_timestart_timeend_search(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->courseid = 1;
        $rec->timemodified = 123456;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is an assignment on frogs and toads";
        $rec2->courseid = 1;
        $rec2->timemodified = 654321;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = '*';
        $querydata->timestart = 123457;
        $querydata->timeend = 123458;

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(count($results), 0);
    }

    /**
     * Test results sort ascending.
     */
    public function test_result_timesort_asc(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->courseid = 1;
        $rec->timemodified = 123456;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is an quiz on frogs and toads";
        $rec2->courseid = 1;
        $rec2->timemodified = 654321;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = '*';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->order = 'asc';

        $results = $this->search->search($querydata); // Execute the search.

        $this->assertEquals($results[0]->get('content'), 'this is an assignment on frogs and toads'); // Check the results.
        $this->assertEquals($results[1]->get('content'), 'this is an quiz on frogs and toads');
        $this->assertEquals(count($results), 2);
    }

    /**
     * Test results sort descending.
     */
    public function test_result_timesort_desc(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->courseid = 1;
        $rec->timemodified = 123456;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is an quiz on frogs and toads";
        $rec2->courseid = 1;
        $rec2->timemodified = 654321;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = '*';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->order = 'desc';

        $results = $this->search->search($querydata); // Execute the search.

        $this->assertEquals($results[1]->get('content'), 'this is an assignment on frogs and toads'); // Check the results.
        $this->assertEquals($results[0]->get('content'), 'this is an quiz on frogs and toads');
        $this->assertEquals(count($results), 2);
    }

    /**
     * Test results are returned for filtered search.
     * Filter users.
     */
    public function test_user_filter_search(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->userid = 1;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is an assignment on frogs and toads";
        $rec2->userid = 2;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'assignment on frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->userids = [1 ];

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals($results[0]->get('userid'), 1); // Check the results.
        $this->assertEquals(count($results), 1);
    }

    /**
     * Test results are returned for filtered search.
     * Filter users.
     */
    public function test_users_filter_search(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->userid = 1;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        $rec2 = new \stdClass();
        $rec2->content = "this is an assignment on frogs and toads";
        $rec2->userid = 2;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'assignment on frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->userids = [1, 2];

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(count($results), 2);
    }

    /**
     * Test the implicit wildcard search functionality.
     * Should not return results.
     */
    public function test_search_no_implicit_wildcard(): void {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'frog';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        // Execute the search.
        $results = $this->search->search($querydata);

        // Check the results.
        $this->assertEquals(count($results), 0);
    }

    /**
     * Test the wildcard search functionality when wildcardstart is enabled.
     */
    public function test_search_wildcardstart_enabled(): void {
        set_config('wildcardstart', 1, 'search_elastic');

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'ogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        // Execute the search.
        $results = $this->search->search($querydata);

        // Check the results.
        $this->assertEquals(count($results), 1);
        $this->assertEquals($results[0]->get('content'), 'this is an assignment on @@HI_S@@frogs@@HI_E@@ and toads');
    }

    /**
     * Test the wildcard search functionality when wildcardend is enabled.
     */
    public function test_search_wildcardend_enabled(): void {
        $searchuser = $this->getDataGenerator()->create_user();
        $this->setUser($searchuser);
        set_config('wildcardend', 1, 'search_elastic');

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new \stdClass();
        $querydata->q = 'fro';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        // Execute the search.
        $results = $this->search->search($querydata);

        // Check the results.
        $this->assertEquals(count($results), 1);
        $this->assertEquals($results[0]->get('content'), 'this is an assignment on @@HI_S@@frogs@@HI_E@@ and toads');
    }

    /**
     * Test validate index method with good index.
     */
    public function test_validate_index(): void {

        $result = $this->engine->validate_index();

        $this->assertTrue($result);
    }

    /**
     * Test validate index method with broken index.
     */
    public function test_broken_index(): void {
        $config = get_config('search_elastic');
        // Delete existing index, as we want to try to make a broken one.
        $url = rtrim($config->hostname, "/");
        $port = $config->port;
        $url .= ':' . $port;
        $indexeurl = $url . '/' . $config->index;
        $client = new \search_elastic\esrequest();
        $response = json_decode($client->delete($indexeurl)->getBody());

        // Assert index was deleted.
        $this->assertTrue($response->acknowledged);

        // Add a document this will create a crate a broken index.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        $result = $this->engine->validate_index();

        // We should now have a broken index.
        $this->assertFalse($result);
    }

    /**
     * Test docoffset is incremented correctly for multiple pages of search results.
     */
    public function test_execute_query_docoffset(): void {
        $searchuser = $this->getDataGenerator()->create_user();
        $this->setUser($searchuser);
        // Generate 2000 indexed documents.
        for ($i = 1, $j = 2000; $i <= $j; $i++) {
            $rec = (object)['content' => 'test message ' . $i];
            // Deny user access to all but 100 documents, spaced evenly throughout the index.
            // The engine returns 1000 results at a time, so there should be 50 visible in the first set, and 50 in the second.
            if ($i % 20 != 0) {
                $rec->denyuserids = [$searchuser->id];
            }
            $area = $this->area;
            $record = $this->generator->create_record($rec);
            $doc = $area->get_document($record);
            $this->engine->add_document($doc, false, $this->luceneversion);
        }
        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        $querydata = (object) [
            'q' => '*',
            'timestart' => 0,
            'timeend' => 0,
        ];

        // Execute the search.
        $results = $this->search->search($querydata);

        $this->assertCount(100, $results);

        // Confirm that each result is unique. This proves that we are not re-querying the same documents multiple times.
        $seenresults = [];
        foreach ($results as $result) {
            $content = $result->get('content');
            $this->assertFalse(in_array($content, $seenresults), "'{$content}' included multiple times in search results.");
            $seenresults[] = $content;
        }
    }

    /**
     * Test handle_413_retry with all documents under size limit.
     */
    public function test_handle_413_retry_all_succeed(): void {
        global $DB;

        $docs = [
            ['id' => 'doc1', 'content' => 'Content1', 'areaid' => 'test-area',
                'contextid' => 1, 'itemid' => 1, 'modified' => time()],
            ['id' => 'doc2', 'content' => 'Content2', 'areaid' => 'test-area',
                'contextid' => 1, 'itemid' => 2, 'modified' => time()],
        ];
        $payload = $this->create_test_payload($docs);
        $this->engine->set_test_payload($payload);

        // Mock successful indexing.
        $this->engine->set_mock_index_results([true, true]);

        // Call the handler.
        $ignored = $this->engine->test_handle_413_retry();

        // Verify there are no errors.
        $errors = $DB->count_records('search_elastic_errors');
        $this->assertEquals(0, $ignored);
        $this->assertEquals(0, $errors);
    }

    /**
     * Test handle_413_retry with oversized document.
     */
    public function test_handle_413_retry_oversized_document(): void {
        global $DB;

        $config = get_config('search_elastic');
        $maxsize = $config->sendsize ?? 9000000;

        $docs = [
            ['id' => 'doc1', 'content' => 'Small content', 'areaid' => 'test-area-413-oversized',
                'contextid' => 1, 'itemid' => 1, 'modified' => time()],
            ['id' => 'doc2', 'content' => str_repeat('Content', $maxsize + 100), 'areaid' => 'test-area-413-oversized',
                'contextid' => 1, 'itemid' => 2, 'modified' => time()],
        ];
        $payload = $this->create_test_payload($docs);
        $this->engine->set_test_payload($payload);

        // Mock doc1 succeeds but doc2 won't be tried due to size.
        $this->engine->set_mock_index_results([true]);

        // Once document ignored (doc2).
        $ignored = $this->engine->test_handle_413_retry();
        $this->assertEquals(1, $ignored);
        $this->assertDebuggingCalledCount(1);

        // Verify error message mentions size.
        $error = $DB->get_record('search_elastic_errors', ['areaid' => 'test-area-413-oversized']);
        $this->assertStringContainsString('too large', strtolower($error->errormessage));
        $this->assertStringContainsString('doc2', strtolower($error->errormessage));
    }

    /**
     * Test handle_413_retry with failed retry.
     */
    public function test_handle_413_retry_individual_failure(): void {
        global $DB;

        $docs = [
            ['id' => 'doc1', 'content' => 'Content1', 'areaid' => 'test-area-413-individual-retry-failure',
                'contextid' => 1, 'itemid' => 1, 'modified' => time()],
            ['id' => 'doc2', 'content' => 'Content2', 'areaid' => 'test-area-413-individual-retry-failure',
                'contextid' => 1, 'itemid' => 2, 'modified' => time()],
        ];
        $payload = $this->create_test_payload($docs);
        $this->engine->set_test_payload($payload);

        // Mock doc1 succeeds but doc2 failed.
        $this->engine->set_mock_index_results([true, false]);

        $ignored = $this->engine->test_handle_413_retry();
        $this->assertEquals(1, $ignored);
        $this->assertDebuggingCalledCount(1);

        // Verify error message mentions individual retry failure.
        $error = $DB->get_record('search_elastic_errors', ['areaid' => 'test-area-413-individual-retry-failure']);
        $this->assertStringContainsString('individual retry', strtolower($error->errormessage));
        $this->assertStringContainsString('doc2', strtolower($error->errormessage));
    }

    /**
     * Test handle_413_retry mixed scenarios.
     */
    public function test_handle_413_retry_mixed_scenarios(): void {
        global $DB;

        $config = get_config('search_elastic');
        $maxsize = $config->sendsize ?? 9000000;

        $docs = [
            ['id' => 'doc1', 'content' => 'Content1', 'areaid' => 'test-area-413-mixed-scenarios',
                'contextid' => 1, 'itemid' => 1, 'modified' => time()],
            ['id' => 'doc2', 'content' => str_repeat('Content', $maxsize + 100), 'areaid' => 'test-area-413-mixed-scenarios',
                'contextid' => 1, 'itemid' => 2, 'modified' => time()],
            ['id' => 'doc3', 'content' => 'Content3', 'areaid' => 'test-area-413-mixed-scenarios',
                'contextid' => 1, 'itemid' => 2, 'modified' => time()],
        ];
        $payload = $this->create_test_payload($docs);
        $this->engine->set_test_payload($payload);

        // Mock doc1 succeeds, doc2 skipped (oversized), doc 3 failed.
        $this->engine->set_mock_index_results([true, false, false]);

        // 2 documents ignored.
        $ignored = $this->engine->test_handle_413_retry();
        $this->assertEquals(2, $ignored);
        $this->assertDebuggingCalledCount(2);

        // 2 errors logged.
        $errors = $DB->count_records('search_elastic_errors');
        $this->assertEquals(2, $errors);
    }

    /**
     * Test log_bulk_response_item_errors with all successful items.
     */
    public function test_log_bulk_response_item_errors_all_success(): void {
        global $DB;

        $docs = [
            ['id' => 'doc1', 'content' => 'Content1', 'areaid' => 'test-area',
                'contextid' => 1, 'itemid' => 1, 'modified' => time()],
            ['id' => 'doc2', 'content' => 'Content2', 'areaid' => 'test-area',
                'contextid' => 1, 'itemid' => 2, 'modified' => time()],
        ];
        $payload = $this->create_test_payload($docs);
        $this->engine->set_test_payload($payload);

        // Mock response with all successful items.
        $responsebody = (object)[
            'errors' => false,
            'items' => [
                (object)[
                    'index' => (object)[
                        '_id' => 'doc1',
                        'status' => 201,
                        'result' => 'created',
                    ],
                ],
                (object)[
                    'index' => (object)[
                        '_id' => 'doc2',
                        'status' => 201,
                        'result' => 'created',
                    ],
                ],
            ],
        ];

        // No documents ignored.
        $ignored = $this->engine->test_log_bulk_response_item_errors($responsebody);
        $this->assertEquals(0, $ignored);

        // No errors.
        $errors = $DB->count_records('search_elastic_errors');
        $this->assertEquals(0, $errors);
    }

    /**
     * Test log_bulk_response_item_errors with failures.
     */
    public function test_log_bulk_response_item_errors_with_failures(): void {
        global $DB;

        $docs = [
            ['id' => 'doc1', 'content' => 'Content1', 'areaid' => 'test-area-bulk-item-fail',
                'contextid' => 1, 'itemid' => 1, 'modified' => time()],
            ['id' => 'doc2', 'content' => 'Content2', 'areaid' => 'test-area-bulk-item-fail',
                'contextid' => 1, 'itemid' => 2, 'modified' => time()],
        ];
        $payload = $this->create_test_payload($docs);
        $this->engine->set_test_payload($payload);

        // Mock response with all successful items.
        $responsebody = (object)[
            'errors' => true,
            'items' => [
                (object)[
                    'index' => (object)[
                        '_id' => 'doc1',
                        'status' => 429,
                        'error' => (object)[
                            'type' => 'some_exception',
                            'reason' => 'Too Many Requests',
                        ],
                    ],
                ],
                (object)[
                    'index' => (object)[
                        '_id' => 'doc2',
                        'status' => 201,
                        'result' => 'created',
                    ],
                ],
            ],
        ];

        // One document failed.
        $ignored = $this->engine->test_log_bulk_response_item_errors($responsebody);
        $this->assertEquals(1, $ignored);
        $this->assertDebuggingCalledCount(1);

        // Verify error details.
        $error = $DB->get_record('search_elastic_errors', ['areaid' => 'test-area-bulk-item-fail']);
        $this->assertStringContainsString('some_exception', strtolower($error->errormessage));
        $this->assertStringContainsString('too many requests', strtolower($error->errormessage));
    }

    /**
     * Test log_bulk_response_item_errors with document ID mismatch.
     */
    public function test_log_bulk_response_item_errors_id_mismatch(): void {
        global $DB;

        $docs = [
            ['id' => 'doc1', 'content' => 'Content1', 'areaid' => 'test-area-id-mismatch',
                'contextid' => 1, 'itemid' => 1, 'modified' => time()],
        ];
        $payload = $this->create_test_payload($docs);
        $this->engine->set_test_payload($payload);

        // Mock response with mismatched ID.
        $responsebody = (object)[
            'errors' => true,
            'items' => [
                (object)[
                    'index' => (object)[
                        '_id' => 'wrong-id',
                        'status' => 400,
                        'error' => (object)[
                            'type' => 'test_error',
                            'reason' => 'test reason',
                        ],
                    ],
                ],
            ],
        ];

        $ignored = $this->engine->test_log_bulk_response_item_errors($responsebody);
        $errors = $DB->count_records('search_elastic_errors');

        // Error is not logged but debugging is called with mismatch.
        $this->assertDebuggingCalled('Document ID mismatch at index 0: expected wrong-id, got doc1');
        $this->assertEquals(1, $ignored);
        $this->assertEquals(0, $errors);
    }

    /**
     * Helper method to create a test payload from document arrays.
     * @param array $docs
     * @return string
     */
    private function create_test_payload(array $docs): string {
        $config = get_config('search_elastic');
        $payload = '';
        foreach ($docs as $doc) {
            $meta = ['index' => ['_index' => $config->index, '_id' => $doc['id']]];
            $payload .= json_encode($meta) . "\n" . json_encode($doc) . "\n";
        }
        return $payload;
    }
}
