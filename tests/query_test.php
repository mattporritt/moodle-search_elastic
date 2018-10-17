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
 * Elastic search engine query unit tests.
 *
 * @package    search_elastic
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Elasticsearch engine.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_elastic_query_testcase extends advanced_testcase {

    /**
     * Test getting areas that have been boosted in plugin config
     */
    public function test_get_boosted_areas() {
        $this->resetAfterTest();
        set_config('boost_mod_assign-activity', 20, 'search_elastic');
        set_config('boost_mod_feedback-activity', 10, 'search_elastic');

        $query = new \search_elastic\query();

        $bosstedareas = $query->get_boosted_areas();

        $this->assertEquals($bosstedareas['mod_assign-activity'], 2); // Check the results.
        $this->assertEquals(count($bosstedareas), 1);

    }

    /**
     * Test getting areas return empty area when there is no boosting.
     */
    public function test_get_boosted_areas_empty() {
        $this->resetAfterTest();

        $query = new \search_elastic\query();

        $bosstedareas = $query->get_boosted_areas();

        $this->assertEquals(empty($bosstedareas), true); // Check the results.
        $this->assertEquals(count($bosstedareas), 0);

    }

    /**
     * Test query boosting construction.
     */
    public function test_construct_boosting() {
        $boostedareas = array('boost_mod_assign-activity' => 2);

        // We're testing a private method, so we need to setup reflector magic.
        $method = new ReflectionMethod('\search_elastic\query', 'consruct_boosting');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke(new \search_elastic\query, $boostedareas); // Get result of invoked method.

        $expected = array('match' => array('areaid' => array('query' => 'boost_mod_assign-activity', 'boost' => 2)));

        $this->assertEquals($proxy[0], $expected);
    }

    /**
     * Test query boosting construction empty.
     */
    public function test_construct_boosting_empty() {
        $boostedareas = array();

        // We're testing a private method, so we need to setup reflector magic.
        $method = new ReflectionMethod('\search_elastic\query', 'consruct_boosting');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke(new \search_elastic\query, $boostedareas); // Get result of invoked method.

        $expected = array();

        $this->assertEquals($proxy, $expected);
    }

    /**
     * Test query response highlighting.
     */
    public function test_set_highlightingg() {
        $this->resetAfterTest();

        $query = new \search_elastic\query();
        $queryarray = array('query' => array(
                'bool' => array(
                        'must' => array(),
                        'should' => array(),
                        'filter' => array('bool' => array('must' => array()))
                )),
                'size' => 100,
                '_source' => array('excludes' => array('filetext'))
        );

        $hightlighting = $query->set_highlighting($queryarray);
        $jsonresult = json_encode($hightlighting);

        $jsonexpected = '{"query":{"bool":{"must":[],"should":[],"filter":{"bool":{"must":[]}}}},"size":100,'.
                        '"_source":{"excludes":["filetext"]},"highlight":{"pre_tags":["@@HI_S@@"],'.
                        '"post_tags":["@@HI_E@@"],"fragment_size":510,"encoder":"html","fields":{"title":{},'.
                        '"content":{},"description1":{},"description2":{}}}}';

        $this->assertEquals($jsonresult, $jsonexpected);
    }

    /**
     * Test query location boosting construction.
     */
    public function test_consruct_location_boosting() {
        // We're testing a private method, so we need to setup reflector magic.
        $method = new ReflectionMethod('\search_elastic\query', 'consruct_location_boosting');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke(new \search_elastic\query, 'courseid', '4' , 2); // Get result of invoked method.

        $expected = array('match' => array('courseid' => array('query' => 4, 'boost' => 2)));

        $this->assertEquals($proxy[0], $expected);
    }

    /**
     * Test date based sorting asc.
     */
    public function test_get_query_date_sort_asc() {
        global $CFG;
        // This is a mock of the search form submission.
        $querydata = new stdClass();
        $querydata->q = '*';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->order = 'asc';

        $query = new \search_elastic\query();

        $result = $query->get_query($querydata, true);
        $this->assertEquals($result['sort']['modified']['order'], 'asc');
    }

    /**
     * Test date based sorting desc.
     */
    public function test_get_query_date_sort_desc() {
        global $CFG;
        // This is a mock of the search form submission.
        $querydata = new stdClass();
        $querydata->q = '*';
        $querydata->timestart = 0;
        $querydata->timeend = 0;
        $querydata->order = 'desc';

        $query = new \search_elastic\query();

        $result = $query->get_query($querydata, true);
        $this->assertEquals($result['sort']['modified']['order'], 'desc');
    }

    /**
     * Test query timerange construction timestart only.
     */
    public function test_construct_time_range_timestart() {
        $filters = new stdClass();
        $filters->timestart = 123456;
        $filters->timeend = 0;

        // We're testing a private method, so we need to setup reflector magic.
        $method = new ReflectionMethod('\search_elastic\query', 'construct_time_range');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke(new \search_elastic\query, $filters); // Get result of invoked method.

        $expected = array('range' => array('modified' => array('gte' => $filters->timestart)));

        $this->assertEquals($proxy, $expected);
    }

    /**
     * Test query timerange construction timeend only.
     */
    public function test_construct_time_range_timeend() {
        $filters = new stdClass();
        $filters->timestart = 0;
        $filters->timeend = 123456;

        // We're testing a private method, so we need to setup reflector magic.
        $method = new ReflectionMethod('\search_elastic\query', 'construct_time_range');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke(new \search_elastic\query, $filters); // Get result of invoked method.

        $expected = array('range' => array('modified' => array('lte' => $filters->timeend)));

        $this->assertEquals($proxy, $expected);
    }

    /**
     * Test query timerange construction.
     */
    public function test_construct_time_range_timestart_timeend() {
        $filters = new stdClass();
        $filters->timestart = 123456;
        $filters->timeend = 567890;

        // We're testing a private method, so we need to setup reflector magic.
        $method = new ReflectionMethod('\search_elastic\query', 'construct_time_range');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke(new \search_elastic\query, $filters); // Get result of invoked method.

        $expected = array('range' => array('modified' => array('lte' => $filters->timeend, 'gte' => $filters->timestart)));

        $this->assertEquals($proxy, $expected);
    }

    /**
     * Test we can extract usercontexts from access info data.
     */
    public function test_extract_usercontexts() {
        $method = new ReflectionMethod('\search_elastic\query', 'extract_usercontexts');
        $method->setAccessible(true); // Allow accessing of private method.

        $actual = $method->invoke(new \search_elastic\query, false);
        $this->assertEquals(null, $actual);

        $actual = $method->invoke(new \search_elastic\query, null);
        $this->assertEquals(null, $actual);

        $actual = $method->invoke(new \search_elastic\query, 'Test');
        $this->assertEquals(null, $actual);

        $actual = $method->invoke(new \search_elastic\query, ['Test']);
        $this->assertEquals(['Test'], $actual);

        $accessinfo = new stdClass();
        $accessinfo->usercontexts = ['Test'];
        $actual = $method->invoke(new \search_elastic\query, $accessinfo);
        $this->assertEquals(['Test'], $actual);

        $accessinfo = new stdClass();
        $accessinfo->usercontexts = ['Test'];
        $accessinfo->everything = true;
        $actual = $method->invoke(new \search_elastic\query, $accessinfo);
        $this->assertEquals(null, $actual);

        $accessinfo = new stdClass();
        $accessinfo->everything = false;
        $actual = $method->invoke(new \search_elastic\query, $accessinfo);
        $this->assertEquals(null, $actual);

        $accessinfo = new stdClass();
        $accessinfo->usercontexts = ['Test'];
        $accessinfo->everything = false;
        $actual = $method->invoke(new \search_elastic\query, $accessinfo);
        $this->assertEquals(['Test'], $actual);
    }
}
