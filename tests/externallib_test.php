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
 * External function Elastic search engine unit tests.
 *
 * @package    search_elastic
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');
require_once($CFG->dirroot . '/search/tests/fixtures/mock_search_area.php');
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/testable_engine.php');
require_once($CFG->dirroot . '/search/engine/elastic/externallib.php');

/**
 * Elasticsearch engine.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_elastic_engine_external_testcase extends advanced_testcase {
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
        $this->engine->delete('core_mocksearch-mock_search_area');
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
     * Test the actual basic search functionality.
     * Make sure we can index a document and get the content back via external method.
     */
    public function test_external_search() {

        // Construct the search object and add it to the engine.
        $rec = new \stdClass();
        $rec->content = "this is a video";
        $rec->courseid = 1;
        $area = new core_mocksearch\search\mock_search_area();
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $this->engine->add_document($doc);

        $rec2 = new \stdClass();
        $rec2->content = "this is an assignment on frogs and toads";
        $rec2->courseid = 2;
        $area = new core_mocksearch\search\mock_search_area();
        $record2 = $this->generator->create_record($rec2);
        $doc2 = $area->get_document($record2);
        $this->engine->add_document($doc2);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        $results = search_elastic_external::search(
                'video', 0, 0, '', 100,
                [1],
                ['core_mocksearch-mock_search_area']);

        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(search_elastic_external::search_returns(), $results);

        // Check the results.
        $this->assertEquals($results[0]['content'], 'this is a <span class="highlight">video</span>');
        $this->assertEquals($results[0]['componentname'], 'core_mocksearch');
        $this->assertEquals($results[0]['areaname'], 'mock_search_area');
    }

    /**
     * Test the actual basic search functionality.
     * Make sure we can index a document and get the content back via external method.
     */
    public function test_external_search_areas() {

        $results = search_elastic_external::search_areas(false);

        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(search_elastic_external::search_areas_returns(), $results);

        $this->assertEquals($results[0]['areaid'], 'core_mocksearch-mock_search_area');

    }

}
