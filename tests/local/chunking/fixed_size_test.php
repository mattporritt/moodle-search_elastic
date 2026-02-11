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
 * Unit test for fixed_size chunking strategy.
 *
 * @package     search_elastic
 * @author      Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright   2026 Monash University (http://www.monash.edu)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic\local\chunking;

use advanced_testcase;
use core_mocksearch\search\mock_search_area;
use stdClass;
use testing_data_generator;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/mock_search_area.php');

/**
 * Elasticsearch engine.
 *
 * @package     search_elastic
 * @copyright   Trisha Milan <trishamilan@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \search_elastic\local\chunking\fixed_size
 */
final class fixed_size_test extends advanced_testcase {
    /**
     * @var testing_data_generator
     */
    protected $generator = null;

    /**
     * @var mock_search_area
     */
    protected mock_search_area $area;

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $this->area = new mock_search_area();
        $this->generator = self::getDataGenerator()->get_plugin_generator('core_search');
        $this->generator->setup();
    }

    public function tearDown(): void {
        // For unit tests before PHP 7, teardown is called even on skip. So only do our teardown if we did setup.
        if ($this->generator) {
            // Moodle DML freaks out if we don't teardown the temp table after each run.
            $this->generator->teardown();
            $this->generator = null;
        }
        parent::tearDown();
    }

    /**
     * Data provider for chunking with overlap.
     */
    public static function chunk_overlap_provider(): array {
        return [
            'No overlap' => [
                'maxsize' => 200,
                'overlap' => 0,
                'expectednumchunks' => 3,
            ],
            'No overlap larger max size' => [
                'maxsize' => 2000,
                'overlap' => 0,
                'expectednumchunks' => 1,
            ],
            'Max size greater than text length with overlap' => [
                'maxsize' => 2000,
                'overlap' => 100,
                'expectednumchunks' => 1,
            ],
            'Max size less than text length with overlap' => [
                'maxsize' => 400,
                'overlap' => 10,
                'expectednumchunks' => 2,
            ],
        ];
    }

    /**
     * Test chunking with overlap.
     *
     * @dataProvider chunk_overlap_provider
     * @param int $maxsize
     * @param int $overlap
     * @param int $exceptednumchunks
     */
    public function test_chunk_with_overlap(int $maxsize, int $overlap, int $exceptednumchunks): void {
        $rec = new stdClass();
        $rec->content = <<<EOF
        Lorem Ipsum is simply dummy text of the printing and typesetting industry.
        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
        when an unknown printer took a galley of type and scrambled it to make a type specimen book.
        It has survived not only five centuries, but also the leap into electronic typesetting,
        remaining essentially unchanged. It was popularised in the 1960s with the release of
        Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing
        software like Aldus PageMaker including versions of Lorem Ipsum.
        EOF;
        $area = $this->area;
        $record = $this->generator->create_record($rec);
        $doc = $area->get_document($record);
        $docdata = $doc->export_for_engine();

        $options = [
            'maxsize' => $maxsize,
            'overlap' => $overlap,
        ];

        $chunkingstrategy = new fixed_size();
        $chunks = $chunkingstrategy->chunk($docdata['content'], $options);
        $this->assertCount($exceptednumchunks, $chunks);

        // Last chunk should include end of text.
        $lastchunk = $chunks[count($chunks) - 1]['text'];
        $lastwords = array_slice(explode(' ', trim($docdata['content'])), - 5);

        foreach ($lastwords as $word) {
            $this->assertStringContainsString($word, $lastchunk);
        }
    }

    /**
     * Test fixed size strategy with small text (no chunking).
     */
    public function test_fixed_size_small_text(): void {
        $strategy = new fixed_size();

        // Small text around 700 bytes.
        $text = str_repeat('testing', 100);

        $chunks = $strategy->chunk($text, ['maxsize' => 50000]);
        $this->assertCount(1, $chunks);
        $this->assertEquals($text, $chunks[0]['text']);
        $this->assertEquals(0, $chunks[0]['index']);
    }

    /**
     * Test fixed size strategy with large text requiring chunking.
     */
    public function test_fixed_size_large_text(): void {
        $strategy = new fixed_size();

        // Create text larger than max chunk size.
        $text = str_repeat('testing ', 10000);

        $chunks = $strategy->chunk($text, ['maxsize' => 50000]);
        $this->assertCount(2, $chunks);

        foreach ($chunks as $index => $chunk) {
            $this->assertArrayHasKey('text', $chunk);
            $this->assertArrayHasKey('index', $chunk);
            $this->assertArrayHasKey('size', $chunk);
            $this->assertTrue($chunk['size'] <= 50000);
            $this->assertEquals($index, $chunk['index']);
        }
    }
}
