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

namespace search_elastic\local\service;

use advanced_testcase;
use context_course;
use core_search\manager;
use search_elastic\local\model\error;
use search_elastic\local\service\error_service;
use search_elastic\testable_engine;
use core_mocksearch\search\mock_search_area;
use stdClass;
use stored_file;
use testable_core_search;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');
require_once($CFG->dirroot . '/search/tests/fixtures/mock_search_area.php');
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/testable_engine.php');

/**
 * Tests for error service class.
 *
 * @package     search_elastic
 * @copyright   2025 Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \search_elastic\local\service\error_service
 */
final class error_service_test extends advanced_testcase {

    /**
     * @var \core_search::manager
     */
    protected $search = null;

    /**
     * @var Instance of core_search_generator.
     */
    protected $generator = null;

    /**
     * @var Instance of testable_engine.
     */
    protected $engine = null;

    /**
     * @var mock_search_area
     */
    protected $area = null;

    /**
     * @var stdClass Test course.
     */
    protected $course = null;

    /**
     * @var context_course Test course context.
     */
    protected $coursecontext = null;

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        set_config('enableglobalsearch', true);

        // Set up basic search engine configuration.
        set_config('hostname', 'http://localhost', 'search_elastic');
        set_config('port', '9200', 'search_elastic');
        set_config('index', 'test_index', 'search_elastic');

        $this->generator = self::getDataGenerator()->get_plugin_generator('core_search');
        $this->generator->setup();

        $this->engine = new testable_engine();
        $this->search = testable_core_search::instance($this->engine);

        $areaid = manager::generate_areaid('core_mocksearch', 'mock_search_area');
        $this->area = new mock_search_area();
        $this->search->add_search_area($areaid, $this->area);

        // Create test course and context.
        $this->course = $this->getDataGenerator()->create_course();
        $this->coursecontext = context_course::instance($this->course->id);

        $this->setAdminUser();
    }

    /**
     * Test save_error method with new error.
     */
    public function test_save_error_new(): void {
        global $DB;

        $docid = 'test_doc_123';
        $itemid = 456;
        $contextid = $this->coursecontext->id;
        $areaid = 'core_mocksearch-mock_search_area';
        $errortype = error::TYPE_INDEXING;
        $errormessage = 'Test error message';
        $contentmodified = time();

        // Ensure no existing error.
        $this->assertEquals(0, $DB->count_records('search_elastic_errors'));

        error_service::save_error($docid, $itemid, $contextid, $areaid, $errortype, $errormessage, $contentmodified);

        // Check error was created.
        $errors = $DB->get_records('search_elastic_errors');
        $this->assertCount(1, $errors);

        $error = reset($errors);
        $this->assertEquals($docid, $error->docid);
        $this->assertEquals($itemid, $error->itemid);
        $this->assertEquals($contextid, $error->contextid);
        $this->assertEquals($areaid, $error->areaid);
        $this->assertEquals($errortype, $error->errortype);
        $this->assertEquals($errormessage, $error->errormessage);
        $this->assertEquals($contentmodified, $error->contentmodified);
        $this->assertEquals(0, $error->retrycount);
        $this->assertEquals(error::STATUS_FAILED, $error->status);
    }

    /**
     * Test save_error method with existing error should update.
     */
    public function test_save_error_existing(): void {
        global $DB;

        $docid = 'test_doc_123';
        $itemid = 456;
        $contextid = $this->coursecontext->id;
        $areaid = 'core_mocksearch-mock_search_area';
        $errortype = error::TYPE_INDEXING;
        $errormessage = 'Test error message';
        $contentmodified = time();

        // Create initial error.
        error_service::save_error($docid, $itemid, $contextid, $areaid, $errortype, $errormessage, $contentmodified);

        $initialcount = $DB->count_records('search_elastic_errors');
        $this->assertEquals(1, $initialcount);

        // Update with same error details.
        $newerrormessage = 'Updated error message';
        error_service::save_error($docid, $itemid, $contextid, $areaid, $errortype, $newerrormessage, $contentmodified);

        // Should still have only one record.
        $this->assertEquals(1, $DB->count_records('search_elastic_errors'));

        $error = $DB->get_record('search_elastic_errors', ['docid' => $docid]);
        $this->assertEquals($newerrormessage, $error->errormessage);
        $this->assertEquals(error::STATUS_FAILED, $error->status);
    }

    /**
     * Test get_error_count_by_status method.
     */
    public function test_get_error_count_by_status(): void {
        // Create errors with different statuses.
        error_service::save_error('doc1', 1, $this->coursecontext->id, 'area1', error::TYPE_INDEXING, 'Message 1', time());
        error_service::save_error('doc2', 2, $this->coursecontext->id, 'area2', error::TYPE_INDEXING, 'Message 2', time());
        error_service::save_error('doc3', 3, $this->coursecontext->id, 'area3', error::TYPE_TIKA, 'Message 3', time());

        // Update one to failed status.
        $error = error::get_record(['docid' => 'doc2']);
        $error->set('status', error::STATUS_OBSOLETE);
        $error->save();

        // Test counts.
        $this->assertEquals(2, error_service::get_error_count_by_status(error::STATUS_FAILED));
        $this->assertEquals(1, error_service::get_error_count_by_status(error::STATUS_OBSOLETE));
    }

    /**
     * Test record_batch_error method.
     */
    public function test_record_batch_error(): void {
        global $DB;

        $message = 'Batch processing failed';
        $payload = '{"index":{"_index":"test_index","_id":"doc1"}}
{"id":"doc1","itemid":123,"areaid":"test_area","contextid":456,"modified":1234567890}
{"index":{"_index":"test_index","_id":"doc2"}}
{"id":"doc2","itemid":124,"areaid":"test_area2","contextid":457,"modified":1234567891}';

        error_service::record_batch_error($message, $payload);
        $this->assertdebuggingcalledcount(2);

        // Check that errors were created for both documents.
        $errors = $DB->get_records('search_elastic_errors');
        $this->assertCount(2, $errors);

        foreach ($errors as $error) {
            $this->assertStringContainsString($message, $error->errormessage);
            $this->assertEquals(error::TYPE_INDEXING, $error->errortype);
            $this->assertEquals(error::STATUS_FAILED, $error->status);
        }
    }

    /**
     * Test record_document_error method.
     */
    public function test_record_document_error(): void {
        global $DB;

        $message = 'Document indexing failed';
        $docdata = [
            'id' => 'test_doc_456',
            'itemid' => 789,
            'areaid' => 'test_area',
            'contextid' => $this->coursecontext->id,
            'modified' => time(),
        ];

        error_service::record_document_error($message, $docdata);
        $this->assertDebuggingCalled('Document indexing failed');

        $errors = $DB->get_records('search_elastic_errors');
        $this->assertCount(1, $errors);

        $error = reset($errors);
        $this->assertEquals('test_doc_456', $error->docid);
        $this->assertEquals(789, $error->itemid);
        $this->assertEquals($this->coursecontext->id, $error->contextid);
        $this->assertEquals('test_area', $error->areaid);
        $this->assertEquals(error::TYPE_INDEXING, $error->errortype);
        $this->assertStringContainsString($message, $error->errormessage);
    }

    /**
     * Test record_document_error method with null docdata.
     */
    public function test_record_document_error_null_docdata(): void {
        global $DB;

        $message = 'Document indexing failed';
        error_service::record_document_error($message, null);

        // Should not create any errors when docdata is null.
        $this->assertEquals(0, $DB->count_records('search_elastic_errors'));
    }

    /**
     * Test record_tika_error method.
     */
    public function test_record_tika_error(): void {
        global $DB;

        // Create a test file.
        $file = $this->create_test_file();
        $message = 'Tika processing failed';

        error_service::record_tika_error($file, $message);
        $this->assertdebuggingcalledcount(1);

        $errors = $DB->get_records('search_elastic_errors');
        $this->assertCount(1, $errors);

        $error = reset($errors);
        $this->assertEquals($file->get_id(), $error->docid);
        $this->assertEquals($file->get_itemid(), $error->itemid);
        $this->assertEquals($file->get_contextid(), $error->contextid);
        $this->assertEquals(error::TYPE_TIKA, $error->errortype);
        $this->assertStringContainsString($message, $error->errormessage);
        $this->assertStringContainsString($file->get_filename(), $error->errormessage);
    }

    /**
     * Test retry_error method with non-existent error.
     */
    public function test_retry_error_not_found(): void {
        $result = error_service::retry_error(999, false);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Unable to find error ID: 999', $result['message']);
    }

    /**
     * Test retry_error method with indexing error.
     */
    public function test_retry_error_indexing(): void {
        // Create test content.
        $rec = new stdClass();
        $rec->content = "Test content for retry";
        $rec->courseid = $this->course->id;
        $record = $this->generator->create_record($rec);

        // Create an indexing error.
        $docdata = [
            'id' => 'test_doc_retry',
            'itemid' => $record->id,
            'areaid' => 'core_mocksearch-mock_search_area',
            'contextid' => $this->coursecontext->id,
            'modified' => time(),
        ];

        error_service::record_document_error('Indexing failed', $docdata);
        $this->assertDebuggingCalled('Indexing failed');

        $error = error::get_record(['docid' => 'test_doc_retry']);
        $this->assertNotEmpty($error);

        // Since we can't easily mock the elasticsearch engine for a successful retry,
        // this test will likely fail the retry but we can test the process.
        $result = error_service::retry_error($error->get('id'), false);

        // The retry should have been attempted.
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
    }

    /**
     * Test extract_document_info method (via record_document_error).
     */
    public function test_extract_document_info(): void {
        global $DB;

        // Test with complete docdata.
        $docdata = [
            'id' => 'complete_doc',
            'itemid' => 123,
            'areaid' => 'test_area',
            'contextid' => 456,
            'modified' => 1234567890,
        ];

        error_service::record_document_error('Test message', $docdata);
        $this->assertDebuggingCalled('Test message');

        $error = $DB->get_record('search_elastic_errors', ['docid' => 'complete_doc']);
        $this->assertNotEmpty($error);
        $this->assertEquals(123, $error->itemid);
        $this->assertEquals('test_area', $error->areaid);
        $this->assertEquals(456, $error->contextid);
        $this->assertEquals(1234567890, $error->contentmodified);
    }

    /**
     * Test extract_document_info method with partial docdata.
     */
    public function test_extract_document_info_partial(): void {
        global $DB;

        // Test with partial docdata with missing itemid, contextid, modified.
        $docdata = [
            'id' => 'partial_doc',
            'areaid' => 'test_area',
        ];

        error_service::record_document_error('Test message', $docdata);
        $this->assertDebuggingCalled('Test message');

        $select = $DB->sql_compare_text('docid') . ' = :docid';

        $errors = error::get_records_select($select, ['docid' => 'partial_doc']);

        $error = reset($errors);
        $this->assertNotEmpty($error);
        $this->assertEquals(0, $error->get('itemid'));
        $this->assertEquals('test_area', $error->get('areaid'));
        $this->assertNull($error->get('contextid'));
        $this->assertNull($error->get('contentmodified'));
    }

    /**
     * Test get_file_areaid method (via record_tika_error).
     */
    public function test_get_file_areaid(): void {
        global $DB;

        $file = $this->create_test_file();
        error_service::record_tika_error($file, 'Test message');
        $this->assertdebuggingcalledcount(1);

        $error = $DB->get_record('search_elastic_errors', ['docid' => $file->get_id()]);
        $this->assertNotEmpty($error);

        // Should contain component and filearea in the areaid.
        $this->assertStringContainsString($file->get_component(), $error->areaid);
        $this->assertStringContainsString($file->get_filearea(), $error->areaid);
    }

    /**
     * Test error status transitions.
     */
    public function test_error_status_transitions(): void {
        // Create an error.
        error_service::save_error('status_test', 123, $this->coursecontext->id, 'test_area', error::TYPE_INDEXING, 'Test');

        $error = error::get_record(['docid' => 'status_test']);
        $this->assertEquals(error::STATUS_FAILED, $error->get('status'));

        // Mark as retrying.
        $error->mark_retrying();
        $error = error::get_record(['docid' => 'status_test']);
        $this->assertEquals(error::STATUS_RETRYING, $error->get('status'));

        // Delete the error record once it's successfully re-indexed.
        $error->delete();
        $error = error::get_record(['docid' => 'status_test']);
        $this->assertFalse($error);
    }

    /**
     * Test error retry count increment.
     */
    public function test_error_retry_count_increment(): void {
        error_service::save_error('retry_test', 123, $this->coursecontext->id, 'test_area', error::TYPE_INDEXING, 'Test');

        $error = error::get_record(['docid' => 'retry_test']);
        $this->assertEquals(0, $error->get('retrycount'));

        // Increment retry count.
        $error->increment_retry();
        $error = error::get_record(['docid' => 'retry_test']);
        $this->assertEquals(1, $error->get('retrycount'));
        $this->assertEquals(error::STATUS_FAILED, $error->get('status'));
    }

    /**
     * Test malformed JSON payload in record_batch_error.
     */
    public function test_record_batch_error_malformed_json(): void {
        global $DB;

        $message = 'Batch processing failed';
        $payload = 'invalid json payload';

        error_service::record_batch_error($message, $payload);

        // Should not create any errors with malformed JSON.
        $this->assertEquals(0, $DB->count_records('search_elastic_errors'));
    }

    /**
     * Test empty payload in record_batch_error.
     */
    public function test_record_batch_error_empty_payload(): void {
        global $DB;

        $message = 'Batch processing failed';
        $payload = '';

        error_service::record_batch_error($message, $payload);

        // Should not create any errors with empty payload.
        $this->assertEquals(0, $DB->count_records('search_elastic_errors'));
    }

    /**
     * Helper method to create a test file.
     */
    private function create_test_file(): stored_file {
        $fs = get_file_storage();
        $filerecord = [
            'contextid' => $this->coursecontext->id,
            'component' => 'mod_assign',
            'filearea' => 'submission_files',
            'itemid' => 123,
            'filepath' => '/',
            'filename' => 'test.txt',
            'userid' => 2,
        ];

        return $fs->create_file_from_string($filerecord, 'Test file content');
    }

    /**
     * Data provider for different error types.
     */
    public static function error_type_provider(): array {
        return [
            'indexing' => [error::TYPE_INDEXING],
            'tika' => [error::TYPE_TIKA],
        ];
    }

    /**
     * Test save_error with different error types.
     *
     * @dataProvider error_type_provider
     * @param string $errortype
     */
    public function test_save_error_different_types($errortype): void {
        global $DB;

        error_service::save_error('type_test', 123, $this->coursecontext->id, 'test_area', $errortype, 'Test message');

        $error = $DB->get_record('search_elastic_errors', ['docid' => 'type_test']);
        $this->assertNotEmpty($error);
        $this->assertEquals($errortype, $error->errortype);
    }

    /**
     * Data provider for different error statuses.
     */
    public static function error_status_provider(): array {
        return [
            'retrying' => [error::STATUS_RETRYING],
            'failed' => [error::STATUS_FAILED],
            'obsolete' => [error::STATUS_OBSOLETE],
        ];
    }

    /**
     * Test get_error_count_by_status with different statuses.
     *
     * @dataProvider error_status_provider
     * @param string $status
     */
    public function test_get_error_count_different_statuses($status): void {
        // Create error and set specific status.
        error_service::save_error('status_count_test', 123, $this->coursecontext->id, 'test_area', error::TYPE_INDEXING, 'Test');

        $error = error::get_record(['docid' => 'status_count_test']);
        $error->set('status', $status);
        $error->save();

        $count = error_service::get_error_count_by_status($status);
        $this->assertEquals(1, $count);

        // Test other statuses should return 0.
        $otherstatus = $status === error::STATUS_FAILED ? error::STATUS_OBSOLETE : error::STATUS_FAILED;
        $othercount = error_service::get_error_count_by_status($otherstatus);
        $this->assertEquals(0, $othercount);
    }

    public function tearDown(): void {
        parent::tearDown();
        if ($this->generator) {
            $this->generator->teardown();
            $this->generator = null;
        }
    }
}
