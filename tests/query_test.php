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
     * Test query boostin construction.
     */
    public function test_construct_boosting() {
        $boostedareas = array('boost_mod_assign-activity'=> 2);

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
}
