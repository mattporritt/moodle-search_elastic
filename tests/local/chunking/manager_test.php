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

namespace search_elastic\local\chunking;

use advanced_testcase;
use search_elastic\test_chunking_manager;
use search_elastic\test_chunking_strategy;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/test_chunking_manager.php');
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/test_chunking_strategy.php');

/**
 * Unit test for chunking manager.
 *
 * @package    search_elastic
 * @author     Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright  2026 Monash University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \search_elastic\local\chunking\manager
 */
final class manager_test extends advanced_testcase {
    /**
     * Setup testcase.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test get_configured_strategy returns default.
     */
    public function test_get_configured_strategy_default(): void {
        // Should return default (fixed_size) if config is not set.
        $strategy = manager::get_configured_strategy();
        $this->assertInstanceOf(strategy_interface::class, $strategy);
    }

    /**
     * Test get_configured_strategy returns configured strategy.
     */
    public function test_get_configured_strategy(): void {
        // Since we only have fixed_size implementation available at the moment,
        // create a test strategy verify we are able to get the configured strategy.
        $strategies = manager::get_strategies();
        $teststrategy = new test_chunking_strategy();

        // Inject via test manager.
        test_chunking_manager::set_test_strategies(array_merge($strategies, ['test_strategy' => $teststrategy]));
        set_config('chunkingstrategy', 'test_strategy', 'search_elastic');

        $strategy = test_chunking_manager::get_configured_strategy();
        $this->assertInstanceOf(test_chunking_strategy::class, $strategy);

        // Clean up.
        test_chunking_manager::clear_test_strategies();
    }

    /**
     * Test get_configured_strategy falls back on invalid config.
     */
    public function test_get_configured_strategy_invalid(): void {
        set_config('chunkingstrategy', 'invalidstrategy', 'search_elastic');

        $strategy = manager::get_configured_strategy();

        // Should fall back to default.
        $this->assertInstanceOf(fixed_size::class, $strategy);
        $this->assertDebuggingCalled("Configured chunking strategy 'invalidstrategy' not found. Falling back to 'fixed_size'");
    }

    /**
     * Test strategy names are retrievable.
     */
    public function test_get_strategy_name(): void {
        $strategies = manager::get_strategies();
        foreach ($strategies as $strategy) {
            $this->assertIsString($strategy->get_name());
        }
    }

    /**
     * Test strategy auto-discovery.
     */
    public function test_strategy_auto_discovery(): void {
        $strategies = manager::get_strategies();
        $this->assertArrayHasKey('fixed_size', $strategies);
        foreach ($strategies as $strategy) {
            $this->assertInstanceOf(strategy_interface::class, $strategy);
        }
    }

    /**
     * Test default options are available for each strategy.
     */
    public function test_get_default_options(): void {
        $strategies = manager::get_strategies();
        foreach ($strategies as $strategy) {
            $this->assertIsArray($strategy->get_default_options());
        }
    }
}
