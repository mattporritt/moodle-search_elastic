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

namespace search_elastic;

use search_elastic\local\chunking\manager;
use search_elastic\local\chunking\strategy_interface;

/**
 * Extends chunking manager to allow injecting test strategies.
 *
 * @package     search_elastic
 * @author      Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright   2026 Monash University (http://www.monash.edu)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_chunking_manager extends manager {
    /**
     * @var array|null Strategies to return for testing.
     */
    private static ?array $teststrategies = null;

    /**
     * Override to return test strategies if set.
     * @return array
     */
    public static function get_strategies(): array {
        if (self::$teststrategies !== null) {
            ksort(self::$teststrategies);
            return self::$teststrategies;
        }

        return parent::get_strategies();
    }

    /**
     * Override to allow configuring test strategies.
     * @return strategy_interface
     */
    public static function get_configured_strategy(): strategy_interface {
        $strategyname = get_config('search_elastic', 'chunkingstrategy') ?: 'search_elastic\\local\\chunking\\fixed_size';
        $strategies = self::get_strategies();
        return $strategies[$strategyname];
    }

    /**
     * Set test strategies.
     * @param array $strategies Array of strategy instances.
     */
    public static function set_test_strategies(array $strategies): void {
        self::$teststrategies = $strategies;
    }

    /**
     * Clear test strategies.
     */
    public static function clear_test_strategies(): void {
        self::$teststrategies = null;
    }
}
