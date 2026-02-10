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

namespace search_elastic\chunking;

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
     * Return the human-readable name for this strategy.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('fixedsizestrategy', 'search_elastic');
    }

    /**
     * Get the default options for this strategy.
     *
     * @return array
     */
    public static function get_default_options(): array {
        return [
            'maxsize' => 8000000,
            'overlap' => 100,
        ];
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
        // Merge provided options with defaults.
        $options = array_merge($this->get_default_options(), $options);

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
     * If position is mid-word, backs up to the start of that word.
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
        if ($prevchar === ' ' || $prevchar === '\n' || $prevchar === '\t' || $prevchar === '\r') {
            return $position;
        }

        // We are at the mid of a word - search backward to find the start.
        $searchpos = $position - 1;
        while ($searchpos >= $minposition) {
            $char = substr($text, $searchpos, 1);
            if ($char === ' ' || $char === '\n' || $char === '\t' || $char === '\r') {
                return $searchpos + 1;
            }
            $searchpos--;

            // Don't search more than 100 bytes back.
            if ($position - $searchpos > 100) {
                return $position;
            }
        }

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
     * - Returns: byte length of "the lazy dog" (~13 bytes)
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
