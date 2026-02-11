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

/**
 * Interface for document chunking strategies.
 *
 * @package    search_elastic
 * @author     Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright  2026 Monash University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface strategy_interface {
    /**
     * Get the default options for this strategy.
     *
     * @return array
     */
    public static function get_default_options(): array;

    /**
     * Return the human-readable name for this strategy.
     *
     * @return string
     */
    public static function get_name(): string;

    /**
     * Chunk the given document data according to strategy.
     *
     * @param string $text Text to chunk
     * @param array $options Chunking options
     * @return array Array of chunk data
     */
    public function chunk(string $text, array $options): array;
}
