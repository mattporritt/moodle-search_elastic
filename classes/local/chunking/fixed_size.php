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

use admin_settingpage;
use admin_setting_configtext;
use search_elastic\admin_setting_chunking_overlap;

/**
 * Fixed-size chunking strategy.
 *
 * Splits text into fixed-size chunks with optional overlap.
 *
 * @package    search_elastic
 * @author     Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright  2026 Monash University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fixed_size implements strategy_interface {
    /**
     * When chunking, we try to split at these word boundaries to avoid
     * cutting words in half.
     */
    private const WORD_BOUNDARY_CHARS = [' ', '\n', '\t', '\r'];

    /**
     * Default maximum chunk size in bytes (8 MB).
     */
    private const DEFAULT_MAXSIZE = 8000000;

    /**
     * Default number of overlapping words between chunks.
     */
    private const DEFAULT_OVERLAPWORDS = 100;

    /**
     * Return the human-readable name for this strategy.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('fixedsizestrategy', 'search_elastic');
    }

    /**
     * Return options derived from plugin configuration.
     *
     * @return array
     */
    public function get_options(): array {
        return [
            'maxsize' => get_config('search_elastic', 'fs_chunkmaxsize') ?? self::DEFAULT_MAXSIZE,
            'overlap' => get_config('search_elastic', 'fs_chunkoverlapwords') ?? self::DEFAULT_OVERLAPWORDS,
        ];
    }

    /**
     * Add admin settings specific to the fixed-size chunking strategy.
     *
     * @param admin_settingpage $settings
     */
    public function add_settings(admin_settingpage $settings): void {
        // Fixed size specific settings.
        $settings->add(new admin_setting_configtext(
            'search_elastic/fs_chunkmaxsize',
            get_string('fs_chunkmaxsize', 'search_elastic'),
            get_string('fs_chunkmaxsize_desc', 'search_elastic'),
            self::DEFAULT_MAXSIZE,
            PARAM_INT
        ));
        $settings->add(new admin_setting_chunking_overlap(
            'search_elastic/fs_chunkoverlapwords',
            get_string('fs_chunkoverlapwords', 'search_elastic'),
            get_string('fs_chunkoverlapwords_desc', 'search_elastic'),
            self::DEFAULT_OVERLAPWORDS,
            PARAM_INT
        ));

        // Hide unless chunking is enabled and this strategy is selected.
        $settings->hide_if('search_elastic/fs_chunkmaxsize', 'search_elastic/enablechunking', 'notchecked');
        $settings->hide_if('search_elastic/fs_chunkmaxsize', 'search_elastic/chunkingstrategy', 'neq', self::class);

        $settings->hide_if('search_elastic/fs_chunkoverlapwords', 'search_elastic/enablechunking', 'notchecked');
        $settings->hide_if('search_elastic/fs_chunkoverlapwords', 'search_elastic/chunkingstrategy', 'neq', self::class);
    }

    /**
     * Chunk text into multiple pieces with optional overlap.
     *
     * - Divides text into chunks of maximum byte size
     * - Each chunk overlaps with the next by N words (default 100)
     * - Finds word boundaries to avoid splitting mid-word at the beginning of chunks
     *
     * @param string $text Text to chunk
     * @param array $options Chunking options
     * @return array Array of chunks containing 'text', 'index' and 'size' keys
     */
    public function chunk(string $text, array $options = []): array {
        if (empty($text) || strlen($text) <= $options['maxsize']) {
            return [[
                'text' => $text,
                'index' => 0,
                'size' => strlen($text),
            ]];
        }

        // Extract configuration options.
        $maxsize = $options['maxsize'];
        $overlapwords = $options['overlap'];

        // Split into chunks.
        $chunks = [];
        $position = 0;
        $index = 0;
        $textlength = strlen($text);

        // Main chunking loop - continue until we've processed all text.
        while ($position < $textlength) {
            // Determine chunk end position. Take up to maxsize bytes from current position,
            // but do not exceed the text length.
            $chunkend = min($position + $maxsize, $textlength);

            // Extract the chunk text.
            $chunktext = substr($text, $position, $chunkend - $position);

            // Store this chunk.
            $chunks[] = [
                'text' => $chunktext,
                'index' => $index,
                'size' => strlen($chunktext),
            ];

            // Calculate next position with overlap (if not at the end of text).
            if ($chunkend < $textlength) {
                // Calculate how many bytes to overlap based on word count.
                // The overlap will help ensure chunks share context to maintain searchability across boundaries.
                $overlapbytes = $this->calculate_overlap_bytes($chunktext, $overlapwords);
                // Move back by overlap amount to create chunk's starting position.
                $nextposition = $chunkend - $overlapbytes;

                // Align to word boundary if we have valid overlap.
                if ($overlapbytes > 0 && $nextposition > $position && $nextposition < $textlength) {
                    $nextposition = $this->find_word_start($text, $nextposition, $position);
                }

                // Ensure we move at least 1 byte forward. This will prevent infinite loops
                // if overlap calculation goes wrong.
                $position = max($position + 1, $nextposition);
            } else {
                // End of the text - no more chunks needed.
                $position = $textlength;
            }

            // Increment chunk index for next iteration.
            $index++;
        }

        return $chunks;
    }

    /**
     * Find the start of the word at the given position.
     *
     * Searches backward to find whitespace, newline, tab, or carriage return (word boundary),
     * then returns the position immediately after that word boundary.
     *
     * For example:
     * Text: "The quick brown fox"
     * Position: 13 (at 'w' in 'brown')
     * Searches backward:
     * - Position 12: 'o'
     * - Position 11: 'r'
     * - Position 10: 'b'
     * - Position 9: ' ' (Whitespace/word boundary found!)
     * Returns position 10 (N+1) which is the start of 'brown' after the space.
     *
     * @param string $text Full text
     * @param int $position Position to adjust
     * @param int $minposition Minimum position
     * @return int Position at start of word
     */
    private function find_word_start(string $text, int $position, int $minposition): int {
        $textlength = strlen($text);

        if ($position <= 0) {
            return 0;
        }

        if ($position >= $textlength) {
            return $textlength;
        }

        // Check if we are already at the start of a word (previous char is a whitespace).
        $prevchar = substr($text, $position - 1, 1);
        if (in_array($prevchar, self::WORD_BOUNDARY_CHARS)) {
            return $position;
        }

        // We are at the mid of a word - search backward to find the beginning of the word.
        $searchpos = $position - 1;
        while ($searchpos >= $minposition) {
            $char = substr($text, $searchpos, 1);
            if (in_array($char, self::WORD_BOUNDARY_CHARS)) {
                // We found the word boundary, return position after the start of the word.
                return $searchpos + 1;
            }
            $searchpos--;

            // Don't search more than 100 bytes back. This should cover longest real words in english
            // (45 bytes) while preventing excessive backtracking through base64/URLs that have no spaces.
            if ($position - $searchpos > 100) {
                return $position;
            }
        }

        // Return the original position if no boundary found.
        return $minposition;
    }

    /**
     * Calculate overlap size in bytes based on word count.
     *
     * Convert a word-based overlap (e.g. 100 words) into a byte count by:
     * - Extracting the last N words from the chunk
     * - Measuring their byte length
     * - Capping at 50% of chunk size to ensure forward progress
     *
     * Example:
     * - Chunk text: "The quick brown fox jumps over the lazy dog"
     * - Overlap: 3 words
     * - Returns: byte length of "the lazy dog" (~12 bytes)
     *
     * @param string $chunktext The chunk text to calculate the overlap from
     * @param int $overlapwords Number of words to overlap
     * @return int Overlap size in bytes
     */
    private function calculate_overlap_bytes(string $chunktext, int $overlapwords): int {
        if ($overlapwords <= 0 || empty($chunktext)) {
            return 0;
        }

        // Split chunk text into words.
        $words = preg_split('/\s+/', trim($chunktext), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($words)) {
            return 0;
        }

        // Cap overlap at number of words available.
        $actualoverlap = min($overlapwords, count($words));

        // Get the last N words.
        $overlapwords = array_slice($words, -$actualoverlap);

        // Calculate byte length of these words (including spaces).
        $overlaptext = implode(' ', $overlapwords);
        $overlapbytes = strlen($overlaptext);

        // Never overlap more than 50% of chunk size.
        // This ensures each chunk contributes at least 50% new content.
        $maxoverlap = (int)(strlen($chunktext) * 0.5);
        return min($overlapbytes, $maxoverlap);
    }
}
