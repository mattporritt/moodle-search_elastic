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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');
require_once($CFG->dirroot . '/search/tests/fixtures/mock_search_area.php');
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/mock_search_area.php');
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/testable_engine.php');

use \GuzzleHttp\Handler\MockHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Psr7\Response;

/**
 * Elasticsearch engine.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_elastic_engine_testcase extends advanced_testcase {
    /**
     * @var \core_search::manager
     */
    protected $search = null;

    /**
     * @var Instace of core_search_generator.
     */
    protected $generator = null;

    /**
     * @var Instace of testable_engine.
     */
    protected $engine = null;

    public function setUp() {
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
        if (!$port &&defined('TEST_SEARCH_ELASTIC_PORT')) {
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
        $this->search = testable_core_search::instance($this->engine);
        $areaid = \core_search\manager::generate_areaid('core_mocksearch', 'mock_search_area');
        $this->search->add_search_area($areaid, new core_mocksearch\search\mock_search_area());
        $this->area = new core_mocksearch\search\mock_search_area();
        $areaboostid = \core_search\manager::generate_areaid('core_mocksearch', 'mock_boost_area');
        $this->search->add_search_area($areaboostid, new core_mocksearch\search\mock_boost_area());
        $this->areaboost = new core_mocksearch\search\mock_boost_area();

        $this->setAdminUser();
        $this->search->index(true);
    }

    public function tearDown() {
        // For unit tests before PHP 7, teardown is called even on skip. So only do our teardown if we did setup.
        if ($this->generator) {
            // Moodle DML freaks out if we don't teardown the temp table after each run.
            $this->generator->teardown();
            $this->generator = null;
        }
        $this->engine->delete();
        sleep(1);
    }

    /**
     * Simple data provider to allow tests to be run with file indexing on and off.
     */
    public function file_indexing_provider() {
        return array(
                'file-indexing-off' => array(0)
        );
    }

    /**
     * Test check if Elasticsearch server is ready.
     */
    public function test_is_server_ready() {
        // Create a mock stack and queue a response.
        $container = [];
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'])
        ]);

        $stack = HandlerStack::create($mock);

        // Reflection magic as we are directly testing a private method.
        $method = new ReflectionMethod('\search_elastic\engine', 'is_server_ready');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke(new \search_elastic\engine, $stack);

        // Check the results.
        $this->assertEquals(true, $proxy);
    }

    /**
     * Test check if Elasticsearch server is ready.
     */
    public function test_is_server_ready_false() {
        // Create a mock stack and queue a response.
        $container = [];
        $mock = new MockHandler([
            new Response(404, ['Content-Type' => 'application/json'])
        ]);

        $stack = HandlerStack::create($mock);

        // Reflection magic as we are directly testing a private method.
        $method = new ReflectionMethod('\search_elastic\engine', 'is_server_ready');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke(new \search_elastic\engine, $stack);

        $expected = 'Elasticsearch endpoint unreachable';

        // Check the results.
        $this->assertEquals($expected, $proxy);
    }


    /**
     * Test deleting docs by type id.
     */
    public function test_delete_by_areaid() {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "elastic";
        $area = new core_mocksearch\search\mock_search_area();
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // Delete all entries in the area.
        $this->engine->delete('core_mocksearch-mock_search_area');

        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new stdClass();
        $querydata->q = 'elastic';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        // Execute the search.
        $results = $this->search->search($querydata);

        // Check the results there shouldn't be any.
        $this->assertEquals(count($results), 0);

    }

    /**
     * Test mapping updates for old versions of Elasticsearch.
     */
    public function test_get_mapping_old() {
        $mapping = $this->engine->get_mapping(2.4);

        // Check mapping has been updated.
        $this->assertEquals($mapping['mappings']['doc']['properties']['id']['type'], 'string');
        $this->assertEquals($mapping['mappings']['doc']['properties']['id']['index'], 'not_analyzed');
        $this->assertEquals($mapping['mappings']['doc']['properties']['parentid']['type'], 'string');
        $this->assertEquals($mapping['mappings']['doc']['properties']['parentid']['index'], 'not_analyzed');
        $this->assertEquals($mapping['mappings']['doc']['properties']['title']['type'], 'string');
        $this->assertEquals($mapping['mappings']['doc']['properties']['content']['type'], 'string');
        $this->assertEquals($mapping['mappings']['doc']['properties']['areaid']['type'], 'string');
        $this->assertEquals($mapping['mappings']['doc']['properties']['areaid']['index'], 'not_analyzed');
    }

    /**
     * Test mapping updates for new versions of Elasticsearch.
     */
    public function test_get_mapping() {
        $mapping = $this->engine->get_mapping(6);

        // Check mapping has not been updated.
        $this->assertEquals($mapping['mappings']['doc']['properties']['id']['type'], 'keyword');;
        $this->assertEquals($mapping['mappings']['doc']['properties']['parentid']['type'], 'keyword');;
        $this->assertEquals($mapping['mappings']['doc']['properties']['title']['type'], 'text');
        $this->assertEquals($mapping['mappings']['doc']['properties']['content']['type'], 'text');
        $this->assertEquals($mapping['mappings']['doc']['properties']['areaid']['type'], 'keyword');
    }

    /**
     * Test the actual basic search functionality.
     * Make sure we can index a document and get the content back via search
     */
    public function test_basic_search() {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "elastic";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new stdClass();
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
    public function test_multi_term_search() {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is a test quiz on frogs and toads";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        // Multi term in order query.
        $querydata = new stdClass();
        $querydata->q = 'test quiz';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(
                $results[0]->get('content'),
                'this is a @@HI_S@@test@@HI_E@@ @@HI_S@@quiz@@HI_E@@ on frogs and toads'); // Check the results.

        // Multi term out of order query.
        $querydata = new stdClass();
        $querydata->q = 'quiz test';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(
                $results[0]->get('content'),
                'this is a @@HI_S@@test@@HI_E@@ @@HI_S@@quiz@@HI_E@@ on frogs and toads'); // Check the results.

        // Multi term partial words query.
        $querydata = new stdClass();
        $querydata->q = 'test frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(
                $results[0]->get('content'),
                'this is a @@HI_S@@test@@HI_E@@ quiz on @@HI_S@@frogs@@HI_E@@ and toads'); // Check the results.

    }

    /**
     * Test results are returned for modifier term search.
     */
    public function test_modifier_search() {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc);

        $rec2 = new \stdClass();
        $rec2->content = "this is a quiz on fish and birds";
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2);

        $rec3 = new \stdClass();
        $rec3->content = "this is an activity about volcanic rocks";
        $area = $this->area;
        $record3 = $this->generator->create_record($rec3);
        $doc3 = $area->get_document($record3);
        $this->engine->add_document($doc3);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        // Multi term in order query.
        $querydata = new stdClass();
        $querydata->q = 'assignment AND frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(
                $results[0]->get('content'),
                'this is an @@HI_S@@assignment@@HI_E@@ on @@HI_S@@frogs@@HI_E@@ and toads'); // Check the results.
        $this->assertEquals(count($results), 1);

        // Multi term out of order query.
        $querydata = new stdClass();
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
    public function test_course_filter_search() {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->courseid = 1;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc);

        $rec2 = new \stdClass();
        $rec2->content = "this is an assignment on frogs and toads";
        $rec2->courseid = 2;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new stdClass();
        $querydata->q = 'assignment on frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->courseids = [1, ];

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(
                $results[0]->get('content'),
                'this is an @@HI_S@@assignment@@HI_E@@ @@HI_S@@on@@HI_E@@ @@HI_S@@frogs@@HI_E@@ and toads'); // Check the results.
        $this->assertEquals(count($results), 1);

    }

    /**
     * Test results are returned for filtered search.
     * Filter areas.
     */
    public function test_area_filter_search() {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new stdClass();
        $querydata->q = 'assignment on frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->areaids = ['mod_book-chapter', ];

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(count($results), 0);

        $querydata = new stdClass();
        $querydata->q = 'assignment on frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->areaids = ['core_mocksearch-mock_search_area', ];

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(count($results), 1);

    }

    /**
     * Test results are returned for filtered search.
     * Filter courses and areas.
     */
    public function test_course_area_filter_search() {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $rec->courseid = 1;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc);

        $rec2 = new \stdClass();
        $rec2->content = "this is an assignment on frogs and toads";
        $rec2->courseid = 2;
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new stdClass();
        $querydata->q = 'assignment on frogs';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->courseids = [1, ];
        $querydata->areaids = ['core_mocksearch-mock_search_area', ];

        $results = $this->search->search($querydata); // Execute the search.
        $this->assertEquals(
                $results[0]->get('content'),
                'this is an @@HI_S@@assignment@@HI_E@@ @@HI_S@@on@@HI_E@@ @@HI_S@@frogs@@HI_E@@ and toads'); // Check the results.
        $this->assertEquals(count($results), 1);

    }

    /**
     * Test results are returned for filtered search.
     * Filter courses and areas.
     */
    public function test_course_area_boosting() {
        set_config('boost_core_mocksearch-mock_boost_area', 20, 'search_elastic');

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is an assignment on frogs and toads";
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc);

        $rec2 = new \stdClass();
        $rec2->content = "this is a quiz on fish and frogs";
        $area = $this->area;
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2);

        $rec3 = new \stdClass();
        $rec3->content = "this is an assignment about volcanic rocks";
        $area = $this->areaboost;
        $record3 = $this->generator->create_record($rec3);
        $doc3 = $area->get_document($record3);
        $this->engine->add_document($doc3);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new stdClass();
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
    public function test_highlight_result() {
        $result = new \stdClass();
        $result->highlight = new \stdClass();
        $result->_source = new \stdClass();
        $result->highlight->content = array('search test @@HI_S@@book@@HI_E@@ description description');
        $result->_source->content = 'search test @@HI_S@@book@@HI_E@@ description description';

        $this->engine->highlight_result($result);
    }

}
