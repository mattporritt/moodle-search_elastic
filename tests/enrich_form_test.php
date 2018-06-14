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
 * Elastic search engine enrichment form unit tests.
 *
 * @package    search_elastic
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Elastic search engine enrichment form unit tests.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_elastic_enrich_form_testcase extends advanced_testcase {

    /**
     * Test getting enrich classes class names.
     */
    public function test_get_enrich_classes() {
        $expected = array(
            '\search_elastic\enrich\text\tika',
                '\search_elastic\enrich\text\plain_text',

        );

        $builder = $this->getMockBuilder('\search_elastic\enrich_form');
        $builder->disableOriginalConstructor();
        $stub = $builder->getMock();

        // We're testing a private method, so we need to setup reflector magic.
        $method = new ReflectionMethod('\search_elastic\enrich_form', 'get_enrich_classes');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke($stub, 'text'); // Get result of invoked method.

        $result = array_diff($expected, $proxy);

        $this->assertEmpty($result);
    }

    /**
     * Test getting enrich options for form.
     */
    public function test_get_enrich_options() {
        $classname = array('\search_elastic\enrich\text\tika');

        $expected = array(
                '\search_elastic\enrich\text\tika' => 'Apache Tika'
        );

        $builder = $this->getMockBuilder('\search_elastic\enrich_form');
        $builder->disableOriginalConstructor();
        $stub = $builder->getMock();

        // We're testing a private method, so we need to setup reflector magic.
        $method = new ReflectionMethod('\search_elastic\enrich_form', 'get_enrich_options');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke($stub, $classname); // Get result of invoked method.

        $this->assertEquals($expected, $proxy);
    }
}
