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

use core_component;
use moodle_exception;
use Exception;

/**
 * Manager class for chunking strategies.
 *
 * @package    search_elastic
 * @author     Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright  2026 Monash University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /**
     * Get all available chunking strategies.
     *
     * @return array Array of strategy instances keyed by strategy name.
     */
    public static function get_strategies(): array {
        static $strategies = null;

        if ($strategies !== null) {
            return $strategies;
        }

        $strategies = [];
        $classes = core_component::get_component_classes_in_namespace('search_elastic', 'local\\chunking');
        foreach (array_keys($classes) as $classname) {
            // Skip this manager class.
            if ($classname === self::class) {
                continue;
            }

            // Only instantiate actual strategies.
            if (!is_subclass_of($classname, strategy_interface::class)) {
                continue;
            }

            try {
                $strategies[$classname] = new $classname();
            } catch (Exception $e) {
                debugging(
                    "Failed to instantiate chunking strategy {$classname}: " . $e->getMessage(),
                    DEBUG_DEVELOPER
                );
            }
        }

        if (empty($strategies)) {
            throw new moodle_exception('nostrategies', 'search_elastic');
        }

        // Sort strategies by key.
        ksort($strategies);

        return $strategies;
    }

    /**
     * Get the configured chunking strategy from plugin settings.
     *
     * @return strategy_interface The configured strategy instance.
     */
    public static function get_configured_strategy(): strategy_interface {
        $strategy = get_config('search_elastic', 'chunkingstrategy') ?: 'search_elastic\\local\\chunking\\fixed_size';
        try {
            $strategies = self::get_strategies();
            if (!isset($strategies[$strategy])) {
                debugging(
                    "Configured chunking strategy '{$strategy}' not found. Falling back to 'fixed_size'",
                    DEBUG_DEVELOPER
                );
                $strategy = 'search_elastic\\local\\chunking\\fixed_size';
            }

            if (!isset($strategies[$strategy])) {
                throw new moodle_exception('nostrategy', 'search_elastic', '', $strategy);
            }

            return $strategies[$strategy];
        } catch (Exception $e) {
            throw new moodle_exception(
                'strategyfailed',
                'search_elastic',
                '',
                $strategy,
                'Failed to instantiate chunking strategy: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get chunking options from plugin configuration.
     *
     * @return array Configured chunking options
     */
    public static function get_chunking_options(): array {
        $strategy = self::get_configured_strategy();
        return $strategy->get_options();
    }

    /**
     * Get chunking strategy options for admin settings dropdown.
     *
     * @return array Array of strategy names.
     */
    public static function get_strategy_options(): array {
        $options = [];
        $strategies = self::get_strategies();
        foreach ($strategies as $key => $strategy) {
            $options[$key] = $strategy->get_name();
        }
        return $options;
    }

    /**
     * Determines whether document chunking is enabled.
     *
     * @return bool
     */
    public static function is_chunking_enabled(): bool {
        $chunkingenabled = get_config('search_elastic', 'enablechunking');
        return !empty($chunkingenabled);
    }
}
