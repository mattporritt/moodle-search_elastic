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

use search_elastic\local\chunking\strategy_interface;

/**
 * Test-only chunking strategy.
 *
 * @package     search_elastic
 * @author      Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright   2026 Monash University (http://www.monash.edu)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_chunking_strategy implements strategy_interface {
    /**
     * Return the human-readable name for this strategy.
     *
     * @return string
     */
    public static function get_name(): string {
        return 'Test Strategy';
    }

    /**
     * Chunk the given document data according to strategy.
     *
     * @param string $text
     * @param array $options
     * @return array Array of chunk data
     */
    public function chunk(string $text, array $options = []): array {
        $maxsize = $options['maxsize'];
        $chunks = str_split($text, $maxsize);

        $result = [];
        foreach ($chunks as $index => $text) {
            $result[] = [
                'text' => $text,
                'index' => $index,
                'size' => strlen($text),
            ];
        }

        return $result;
    }

    /**
     * Get the default options for this strategy.
     *
     * @return array
     */
    public static function get_default_options(): array {
        return [];
    }
}
