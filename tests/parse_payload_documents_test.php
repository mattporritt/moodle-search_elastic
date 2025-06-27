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

use advanced_testcase;
use context_system;
use ReflectionMethod;
use ReflectionProperty;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/testable_engine.php');

/**
 * Test for parse_payload_documents method in engine class.
 *
 * @package     search_elastic
 * @copyright   2025 Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \search_elastic\engine::parse_payload_documents
 */
final class parse_payload_documents_test extends advanced_testcase {

    /** @var Instance of testable_engine. */
    protected $engine = null;

    /** @var ReflectionMethod */
    protected $method;

    /**
     * Setup testcase.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        set_config('hostname', 'http://localhost', 'search_elastic');
        set_config('port', '9200', 'search_elastic');
        set_config('index', 'test_index', 'search_elastic');

        $this->engine = new testable_engine();

        $this->method = new ReflectionMethod('\search_elastic\engine', 'parse_payload_documents');
        $this->method->setAccessible(true);
    }

    /**
     * Helper method to set payload and invoke parse_payload_documents.
     *
     * @param  string $payload
     * @return array The result of parse_payload_documents
     */
    private function invoke_parse_payload_documents(string $payload): array {
        $payloadproperty = new ReflectionProperty('\search_elastic\engine', 'payload');
        $payloadproperty->setAccessible(true);
        $payloadproperty->setValue($this->engine, $payload);

        return $this->method->invoke($this->engine);
    }

    /**
     * Test parse_payload_documents with empty payload.
     */
    public function test_parse_payload_documents_empty_payload(): void {
        $result = $this->invoke_parse_payload_documents('');
        $this->assertEquals([], $result);
    }

    /**
     * Test parse_payload_documents with valid single document.
     */
    public function test_parse_payload_documents_single_document(): void {
        $metadata = [
            '_index' => 'test_index',
            '_type' => 'doc',
            '_id' => 'mod_assign-activity-37',
        ];
        $docdata = [
            'areaid' => 'mod_assign-activity',
            'id' => 'mod_assign-activity-37',
            'itemid' => 37,
            'title' => 'Assignment 1',
            'content' => 'Test assign 1',
            'contextid' => '353',
            'courseid' => '8',
            'owneruserid' => '0',
            'modified' => '1753265148',
            'type' => 1,
            'parentid' => 'mod_assign-activity-37',
        ];

        $jsonmeta = json_encode($metadata);
        $jsondoc = json_encode($docdata);
        $payload = $jsonmeta . "\n" . $jsondoc. "\n";

        $result = $this->invoke_parse_payload_documents($payload);
        $this->assertCount(1, $result);
        $this->assertEquals('mod_assign-activity-37', $result[0]['id']);
        $this->assertEquals(37, $result[0]['itemid']);
        $this->assertEquals(353, $result[0]['contextid']);
        $this->assertEquals('mod_assign-activity', $result[0]['areaid']);
        $this->assertEquals('1753265148', $result[0]['modified']);
    }

    /**
     * Test parse_payload_documents with valid multiple documents.
     */
    public function test_parse_payload_documents_multiple_documents(): void {
        $payload = file_get_contents(__DIR__ . '/fixtures/test_payload.json');
        $result = $this->invoke_parse_payload_documents($payload);

        // Verify it has 4 documents parsed.
        $this->assertCount(4, $result);

        // Test the first document (folder activity).
        $this->assertEquals('mod_folder-activity-6', $result[0]['id']);
        $this->assertEquals(6, $result[0]['itemid']);
        $this->assertEquals(418, $result[0]['contextid']);
        $this->assertEquals('mod_folder-activity', $result[0]['areaid']);
        $this->assertEquals('1753319565', $result[0]['modified']);

        // Test the second document (test1.pdf file).
        $this->assertEquals('588', $result[1]['id']);
        $this->assertEquals(6, $result[1]['itemid']);
        $this->assertEquals(418, $result[1]['contextid']);
        $this->assertEquals('mod_folder-activity', $result[1]['areaid']);
        $this->assertEquals('1753319565', $result[1]['modified']);

        // Test the third document (test2.pdf file).
        $this->assertEquals('590', $result[2]['id']);
        $this->assertEquals(6, $result[2]['itemid']);
        $this->assertEquals(418, $result[2]['contextid']);
        $this->assertEquals('mod_folder-activity', $result[2]['areaid']);
        $this->assertEquals('1753319565', $result[2]['modified']);

        // Test the fourth document (test3.pdf file).
        $this->assertEquals('591', $result[3]['id']);
        $this->assertEquals(6, $result[3]['itemid']);
        $this->assertEquals(418, $result[3]['contextid']);
        $this->assertEquals('mod_folder-activity', $result[3]['areaid']);
        $this->assertEquals('1753319565', $result[3]['modified']);
    }

    /**
     * Test parse_payload_documents index alignment with multiple documents.
     */
    public function test_parse_payload_documents_index_alignment(): void {
        $payload = file_get_contents(__DIR__ . '/fixtures/test_payload.json');
        $result = $this->invoke_parse_payload_documents($payload);

        // Test that document IDs from metadata match document data at each index.
        // This is important for the error handling where we match Elasticsearch response
        // items to our parsed documents by index position.
        $expectedmapping = [
            0 => 'mod_folder-activity-6',
            1 => '588',
            2 => '590',
            3 => '591',
        ];

        foreach ($expectedmapping as $index => $expectedid) {
            $this->assertEquals($expectedid, $result[$index]['metadata']['index']['_id'],
                "Metadata ID at index $index should match expected");
            $this->assertEquals($expectedid, $result[$index]['id'],
                "Extracted ID at index $index should match expected");
        }
    }

    /**
     * Test parse_payload_documents with missing fields.
     */
    public function test_parse_payload_documents_missing_fields(): void {
        $payload = <<<'PAYLOAD'
        {"index":{"_index":"test_index","_type":"doc","_id":"doc1"}}
{"id":"doc1"}
PAYLOAD;

        $result = $this->invoke_parse_payload_documents($payload);
        $this->assertCount(1, $result);
        $this->assertEquals('doc1', $result[0]['id']);
        $this->assertEquals(0, $result[0]['itemid']);
        $this->assertEquals('unknown', $result[0]['areaid']);
        $this->assertEquals(context_system::instance(), $result[0]['contextid']);
        $this->assertNull($result[0]['modified']);
    }

    /**
     * Test parse_payload_documents with malformed JSON document data.
     */
    public function test_parse_payload_documents_malformed_document(): void {
        $payload = <<<'PAYLOAD'
            {"index":{"_index":"test_index","_type":"doc","_id":"doc1"}}
    {invalid JSON}
    PAYLOAD;

        $result = $this->invoke_parse_payload_documents($payload);
        $this->assertCount(1, $result);
        $this->assertNull($result[0]);
    }

    /**
     * Test parse_payload_documents with mixed valid and invalid documents.
     */
    public function test_parse_payload_documents_mixed_valid_with_invalid(): void {
        $payload = file_get_contents(__DIR__ . '/fixtures/mixed_valid_invalid_payload.json');
        $result = $this->invoke_parse_payload_documents($payload);
        $this->assertCount(4, $result);

        // First document should be valid.
        $this->assertEquals('mod_folder-activity-1', $result[0]['id']);
        $this->assertEquals(1, $result[0]['itemid']);
        $this->assertEquals('mod_folder-activity', $result[0]['areaid']);
        $this->assertEquals(100, $result[0]['contextid']);
        $this->assertEquals('1753319565', $result[0]['modified']);

        // Second document should be null due to invalid metadata.
        $this->assertNull($result[1]);

        // Third document should be valid.
        $this->assertEquals('mod_folder-activity-2', $result[2]['id']);
        $this->assertEquals(3, $result[2]['itemid']);
        $this->assertEquals('mod_folder-activity', $result[2]['areaid']);
        $this->assertEquals(120, $result[2]['contextid']);
        $this->assertEquals('1753319565', $result[2]['modified']);

        // Fourth document should be null due to invalid docdata.
        $this->assertNull($result[3]);
    }

    /**
     * Test tearDown.
     */
    public function tearDown(): void {
        parent::tearDown();
        $this->engine = null;
        $this->method = null;
    }
}
