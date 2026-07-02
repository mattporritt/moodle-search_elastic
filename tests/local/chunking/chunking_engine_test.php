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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');
require_once($CFG->dirroot . '/search/tests/fixtures/mock_search_area.php');
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/mock_search_area.php');
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/testable_engine.php');

use advanced_testcase;
use ArrayIterator;
use context_module;
use core_mocksearch\search\mock_search_area;
use ReflectionMethod;
use search_elastic\esrequest;
use search_elastic\task\delete_document_task;
use search_elastic\testable_engine;
use stdClass;
use testable_core_search;
use testing_data_generator;

/**
 * Unit test for engine chunking integration.
 *
 * @package    search_elastic
 * @author     Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright  2026 Monash University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \search_elastic\engine
 */
final class chunking_engine_test extends advanced_testcase {
    /**
     * @var core_search::manager
     */
    protected $search = null;

    /**
     * @var testing_data_generator
     */
    protected $generator = null;

    /**
     * @var testable_engine
     */
    protected $engine = null;

    /**
     * @var string the Apache Lucene version of the attached Elasticsearch / OpenSearch service.
     */
    protected string $luceneversion;

    /**
     * @var mock_search_area
     */
    protected mock_search_area $area;

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        set_config('enableglobalsearch', true);

        // Allow setting of test server info via Env Var or define
        // to cater for mulitiple test setups.
        $hostname = getenv('TEST_SEARCH_ELASTIC_HOSTNAME');
        $port = getenv('TEST_SEARCH_ELASTIC_PORT');
        $index = getenv('TEST_SEARCH_ELASTIC_INDEX');

        if (!$hostname && defined('TEST_SEARCH_ELASTIC_HOSTNAME')) {
            $hostname = TEST_SEARCH_ELASTIC_HOSTNAME;
        }
        if (!$port && defined('TEST_SEARCH_ELASTIC_PORT')) {
            $port = TEST_SEARCH_ELASTIC_PORT;
        }
        if (!$index && defined('TEST_SEARCH_ELASTIC_INDEX')) {
            $index = TEST_SEARCH_ELASTIC_INDEX;
        }

        if (!$hostname || !$port || !$index) {
            $this->markTestSkipped('Elastic extension test server not set.');
        }

        set_config('hostname', $hostname, 'search_elastic');
        set_config('port', $port, 'search_elastic');
        set_config('index', $index, 'search_elastic');
        set_config('enablechunking', 1, 'search_elastic');
        set_config('chunkingstrategy', 'search_elastic\\local\\chunking\\fixed_size', 'search_elastic');
        set_config('fs_chunkmaxsize', 2000, 'search_elastic');
        set_config('fs_chunkoverlapwords', 0, 'search_elastic');
        set_config('chunksuccessthreshold', 50, 'search_elastic');

        $this->generator = self::getDataGenerator()->get_plugin_generator('core_search');
        $this->generator->setup();

        $this->engine = new testable_engine();
        $this->luceneversion = $this->engine->get_es_lucene_version();
        $this->search = testable_core_search::instance($this->engine);
        $areaid = \core_search\manager::generate_areaid('core_mocksearch', 'mock_search_area');
        $this->search->add_search_area($areaid, new mock_search_area());
        $this->area = new mock_search_area();

        $this->setAdminUser();
        $this->search->index(true);
    }

    public function tearDown(): void {
        // For unit tests before PHP 7, teardown is called even on skip. So only do our teardown if we did setup.
        if ($this->generator) {
            // Moodle DML freaks out if we don't teardown the temp table after each run.
            $this->generator->teardown();
            $this->generator = null;
        }
        if ($this->engine) {
            $this->engine->delete();
            sleep(1);
        }
        parent::tearDown();
    }

    /**
     * Test small document is not chunked.
     */
    public function test_small_document_no_chunking(): void {
        // Generate course.
        $course = self::getDataGenerator()->create_course();
        $courseid = $course->id;

        $rec = new stdClass();
        $rec->content = "Hello world!";
        $rec->courseid = $courseid;
        $record = $this->generator->create_record($rec);
        $doc = $this->area->get_document($record);

        set_config('fs_chunkmaxsize', 200, 'search_elastic');

        $indexed = $this->engine->add_document($doc, false, $this->luceneversion);
        $this->assertTrue($indexed);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        // This is a mock of the search form submission.
        $querydata = new stdClass();
        $querydata->q = '*';
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        // Execute the search.
        $results = $this->search->search($querydata);
        $resultdocdata = $results[0]->export_for_engine();

        $this->assertCount(1, $results);
        $this->assertEquals($rec->content, $resultdocdata['content']);
        $this->assertArrayNotHasKey('original_id', $resultdocdata);
        $this->assertArrayNotHasKey('chunk_number', $resultdocdata);
        $this->assertArrayNotHasKey('chunk_total', $resultdocdata);
    }

    /**
     * Test large document is chunked.
     */
    public function test_large_document_is_chunked(): void {
        // Generate course.
        $course = self::getDataGenerator()->create_course();
        $courseid = $course->id;

        $rec = new stdClass();
        $rec->courseid = $courseid;

        // About 595 bytes.
        $rec->content = <<<EOF
        What is Lorem Ipsum?
        Lorem Ipsum is simply dummy text of the printing and typesetting industry.
        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
        when an unknown printer took a galley of type and scrambled it to make a type
        specimen book. It has survived not only five centuries, but also the leap into
        electronic typesetting, remaining essentially unchanged. It was popularised in
        the 1960s with the release of Letraset sheets containing Lorem Ipsum passages,
        and more recently with desktop publishing software like Aldus PageMaker including
        versions of Lorem Ipsum.
        EOF;

        $record = $this->generator->create_record($rec);
        $doc = $this->area->get_document($record);
        $docdata = $doc->export_for_engine();

        set_config('fs_chunkmaxsize', 200, 'search_elastic');
        set_config('fs_chunkoverlapwords', 0, 'search_elastic');

        $indexed = $this->engine->add_document($doc, false, $this->luceneversion);
        $this->assertTrue($indexed);

        // We need to wait for Elastic search to update its index
        // this happens in near realtime, not immediately.
        sleep(1);

        $matchparams = [
            ['match' => ['original_id' => 'core_mocksearch-mock_search_area-1']],
        ];
        [$totalhits, $searchresults] = $this->get_indexed_document($matchparams);
        $this->assertEquals(3, $totalhits);

        foreach ($searchresults as $key => $result) {
            $index = $key + 1;
            $this->assertEquals($docdata['areaid'], $result->_source->areaid);
            $this->assertEquals("{$docdata['id']}_c{$index}", $result->_source->id);
            $this->assertEquals($docdata['itemid'], $result->_source->itemid);
            $this->assertEquals($docdata['title'], $result->_source->title);
            $this->assertEquals($docdata['contextid'], $result->_source->contextid);
            $this->assertEquals($docdata['parentid'], $result->_source->parentid);
            $this->assertEquals($docdata['id'], $result->_source->original_id);
            $this->assertEquals($index, $result->_source->chunk_number);
            $this->assertEquals($totalhits, $result->_source->chunk_total);
        }
    }

    /**
     * Test document chunking end-to-end.
     */
    public function test_document_chunking_end_to_end(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);

        $largecontent = str_repeat("This is a test sentence. ", 2000);

        $post = (object)[
            'discussion' => $this->create_forum_discussion($forum),
            'message' => $largecontent,
            'userid' => 2,
            'created' => time(),
            'modified' => time(),
            'forumid' => $forum->id,
            'courseid' => $course->id,
        ];
        $post->id = $DB->insert_record('forum_posts', $post);

        $searcharea = \core_search\manager::get_search_area('mod_forum-post');
        $recordset = $searcharea->get_document_recordset(time(), context_module::instance($forum->cmid));
        foreach ($recordset as $record) {
            $document = $searcharea->get_document($record);
        }
        $recordset->close();

        $result = $this->engine->add_document($document, false);
        $this->assertTrue($result);

        // Wait for ES to index.
        sleep(1);

        $matchparams = [
            ['match' => ['original_id' => $document->get('id')]],
        ];

        $docdata = $document->export_for_engine();
        [$totalhits, $searchresults] = $this->get_indexed_document($matchparams);

        foreach ($searchresults as $key => $result) {
            $index = $key + 1;
            $this->assertEquals($docdata['areaid'], $result->_source->areaid);
            $this->assertEquals("{$docdata['id']}_c{$index}", $result->_source->id);
            $this->assertEquals($docdata['itemid'], $result->_source->itemid);
            $this->assertEquals($docdata['title'], $result->_source->title);
            $this->assertEquals($docdata['contextid'], $result->_source->contextid);
            $this->assertEquals($docdata['parentid'], $result->_source->parentid);
            $this->assertEquals($docdata['id'], $result->_source->original_id);
            $this->assertEquals($index, $result->_source->chunk_number);
            $this->assertEquals($totalhits, $result->_source->chunk_total);
        }
    }

    /**
     * Test chunk deletion.
     */
    public function test_chunk_deletion(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);

        // Chunk max size of 2000.
        set_config('fs_chunkmaxsize', 2000, 'search_elastic');

        // Create and index a large document.
        $largecontent = str_repeat("This is a test sentence. ", 2000);

        $post = (object)[
            'discussion' => $this->create_forum_discussion($forum),
            'message' => $largecontent,
            'userid' => 2,
            'created' => time(),
            'modified' => time(),
            'forumid' => $forum->id,
            'courseid' => $course->id,
        ];
        $post->id = $DB->insert_record('forum_posts', $post);

        $searcharea = \core_search\manager::get_search_area('mod_forum-post');
        $recordset = $searcharea->get_document_recordset(time(), context_module::instance($forum->cmid));
        foreach ($recordset as $record) {
            $document = $searcharea->get_document($record);
        }
        $recordset->close();

        $result = $this->engine->add_document($document, false);
        $this->assertTrue($result);

        // Wait for ES to index.
        sleep(1);

        $matchparams = [
            ['match' => ['original_id' => $document->get('id')]],
        ];

        // Verify chunks exists.
        [$totalhits] = $this->get_indexed_document($matchparams);
        $this->assertGreaterThan(0, $totalhits);

        // Delete the forum by areaid.
        $result = $this->engine->delete('mod_forum-post');
        $this->assertTrue($result);
        sleep(2);

        $matchparams = [
            ['match' => ['original_id' => $document->get('id')]],
        ];

        // Verify chunks are deleted.
        [$totalhits] = $this->get_indexed_document($matchparams);
        $this->assertEquals(0, $totalhits);
    }

    /**
     * Test delete_by_area with chunks.
     */
    public function test_delete_by_area_with_chunks(): void {
        $this->resetAfterTest();

        $this->engine->index_single_document([
            'id' => 'area1-doc1',
            'content' => 'Area 1 regular',
            'areaid' => 'test_area_1',
            'itemid' => 1,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $this->engine->index_single_document([
                'id' => "area1-doc2_c{$i}",
                'original_id' => 'area1-doc2',
                'chunk_number' => $i,
                'chunk_total' => 5,
                'content' => "Chunk {$i}",
                'areaid' => 'test_area_1',
                'itemid' => 2,
            ]);
        }

        // Index document in area2 should not be deleted.
        $this->engine->index_single_document([
            'id' => 'area2-doc1',
            'content' => 'Area 2',
            'areaid' => 'test_area_2',
            'itemid' => 3,
        ]);

        // Wait for ES to index.
        sleep(2);

        $result = $this->engine->delete('test_area_1');
        $this->assertTrue($result);

        sleep(2);

        // Verify area1 documents are gone.
        $matchparams = [
            ['match' => ['areaid' => 'test_area_1']],
        ];
        [$totalhits] = $this->get_indexed_document($matchparams);
        $this->assertEquals(0, $totalhits);

        // Verify area2 document still exists.
        $matchparams = [
            ['match' => ['areaid' => 'test_area_2']],
        ];
        [$totalhits] = $this->get_indexed_document($matchparams);
        $this->assertEquals(1, $totalhits);
    }

    /**
     * Test search result deduplication.
     */
    public function test_search_deduplication(): void {
        global $DB;

        // Create and index a large document.
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);

        $randombytes = random_bytes(20);
        $uniqueterm = 'uniqueterm' . '_' . bin2hex($randombytes);

        // Create and index a large document.
        $largecontent = str_repeat("This is a test sentence with {$uniqueterm} term. ", 2000);

        $post = (object)[
            'discussion' => $this->create_forum_discussion($forum),
            'message' => $largecontent,
            'userid' => 2,
            'created' => time(),
            'modified' => time(),
            'forumid' => $forum->id,
            'courseid' => $course->id,
        ];
        $post->id = $DB->insert_record('forum_posts', $post);

        $searcharea = \core_search\manager::get_search_area('mod_forum-post');
        $recordset = $searcharea->get_document_recordset(time(), context_module::instance($forum->cmid));
        foreach ($recordset as $record) {
            $document = $searcharea->get_document($record);
        }
        $recordset->close();

        $result = $this->engine->add_document($document, false);
        $this->assertTrue($result);

        // Wait for ES to index.
        sleep(1);

        // Multi term partial words query.
        $querydata = new stdClass();
        $querydata->q = $uniqueterm;
        $querydata->timestart = 0;
        $querydata->timeend = 0;

        // Execute the search.
        $results = $this->search->search($querydata);

        // Should only return one result despite multiple chunks matching.
        $this->assertCount(1, $results);
    }

    /**
     * Test bulk indexing with chunking.
     */
    public function test_add_documents_with_chunking(): void {
        global $DB;

        set_config('chunkingstrategy', 'search_elastic\\local\\chunking\\fixed_size', 'search_elastic');
        set_config('fs_chunkmaxsize', 5000, 'search_elastic');

        // Create and index a large document.
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);

        for ($i = 0; $i < 2; $i++) {
            // Create and index a large document.
            $largecontent = str_repeat("This is a test content for post {$i}. ", 2000);
            $post = (object)[
                'discussion' => $this->create_forum_discussion($forum),
                'message' => $largecontent,
                'userid' => 2,
                'created' => time(),
                'modified' => time(),
                'forumid' => $forum->id,
                'courseid' => $course->id,
            ];
            $post->id = $DB->insert_record('forum_posts', $post);
        }

        $searcharea = \core_search\manager::get_search_area('mod_forum-post');

        $recordset = $searcharea->get_document_recordset(time(), context_module::instance($forum->cmid));
        $documentrecords = [];
        foreach ($recordset as $record) {
            $documentrecords[] = $searcharea->get_document($record);
        }
        $recordset->close();

        $options = [
            'lastindexedtime' => 0,
            'indexfiles' => false,
        ];
        $iterator = new ArrayIterator($documentrecords);

        [$numrecords, $numdocs, $numdocsignored] =
            $this->engine->add_documents($iterator, $searcharea, $options);

        $this->assertEquals(2, $numrecords);
        $this->assertEquals(2, $numdocs);
        $this->assertEquals(0, $numdocsignored);

        // Wait for ES to index.
        sleep(1);

        foreach ($documentrecords as $document) {
            $matchparams = [
                ['match' => ['original_id' => $document->get('id')]],
            ];

            $docdata = $document->export_for_engine();
            [$totalhits, $searchresults] = $this->get_indexed_document($matchparams);
            $this->assertGreaterThan(1, $totalhits);

            foreach ($searchresults as $key => $result) {
                $index = $key + 1;
                $this->assertEquals($docdata['areaid'], $result->_source->areaid);
                $this->assertEquals("{$docdata['id']}_c{$index}", $result->_source->id);
                $this->assertEquals($docdata['itemid'], $result->_source->itemid);
                $this->assertEquals($docdata['title'], $result->_source->title);
                $this->assertEquals($docdata['contextid'], $result->_source->contextid);
                $this->assertEquals($docdata['parentid'], $result->_source->parentid);
                $this->assertEquals($docdata['id'], $result->_source->original_id);
                $this->assertEquals($index, $result->_source->chunk_number);
                $this->assertEquals($totalhits, $result->_source->chunk_total);
            }
        }
    }

    /**
     * Test create_chunk_data helper.
     */
    public function test_create_chunk_data(): void {
        $docdata = [
            'id' => 'test-doc-1',
            'content' => 'Some text content',
            'areaid' => 'test',
        ];

        $contentchunk = ['text' => 'chunk content', 'index' => 0, 'size' => 13];

        // Use reflection to call private method.
        $method = new ReflectionMethod($this->engine, 'create_chunk_data');
        $method->setAccessible(true);

        $chunkdata = $method->invoke($this->engine, $docdata, 1, 5, $contentchunk, null);

        $this->assertEquals('test-doc-1_c1', $chunkdata['id']);
        $this->assertEquals('test-doc-1', $chunkdata['original_id']);
        $this->assertEquals(1, $chunkdata['chunk_number']);
        $this->assertEquals(5, $chunkdata['chunk_total']);
        $this->assertEquals('chunk content', $chunkdata['content']);
    }

    /**
     * Test handle_chunk_results helper.
     */
    public function test_handle_chunk_results_configurable_success_threshold(): void {
        $engine = new testable_engine();

        // Use reflection to call private method.
        $method = new ReflectionMethod($this->engine, 'handle_chunk_results');
        $method->setAccessible(true);

        $docdata = ['id' => 'test-doc-1'];
        $originalid = 'test-doc-1';
        $successcount = 3;
        $total = 10;
        $successpercentage = 30;
        $failedchunks = [1, 2, 3, 4, 5, 6, 7];

        // Test with 25% threshold.
        $threshold = 25;
        $engine->test_set_config('chunksuccessthreshold', $threshold);

        // 30% success (partial) should pass.
        $result = $method->invoke($engine, $originalid, $docdata, $total, $successcount, $failedchunks);
        $this->assertTrue($result);
        $message = get_string('chunkingfailed_partial', 'search_elastic', [
            'docid' => $originalid,
            'success' => $successcount,
            'total' => $total,
            'percentage' => $successpercentage,
            'threshold' => $threshold,
            'failed' => implode(', ', $failedchunks),
        ]);
        $this->assertDebuggingCalled($message);

        $successcount = 2;
        $successpercentage = 20;
        $failedchunks = [1, 2, 3, 4, 5, 6, 7, 8];

        // 20% success should fail.
        $result = $method->invoke($engine, $originalid, $docdata, $total, $successcount, $failedchunks);
        $this->assertFalse($result);
        $message = get_string('chunkingfailed_critical', 'search_elastic', [
            'docid' => $originalid,
            'success' => $successcount,
            'total' => $total,
            'percentage' => $successpercentage,
            'threshold' => $threshold,
            'failed' => implode(', ', $failedchunks),
        ]);
        $this->assertDebuggingCalled($message);

        // Test with 50% threshold.
        $threshold = 50;
        $engine->test_set_config('chunksuccessthreshold', $threshold);

        // 100% success.
        $result = $method->invoke($engine, 'test-doc-1', $docdata, 10, 10, []);
        $this->assertTrue($result);

        $successcount = 8;
        $successpercentage = 80;
        $failedchunks = [8, 9];

        // 80% success (partial).
        $result = $method->invoke($engine, $originalid, $docdata, $total, $successcount, $failedchunks);
        $this->assertTrue($result);
        $message = get_string('chunkingfailed_partial', 'search_elastic', [
            'docid' => $originalid,
            'success' => $successcount,
            'total' => $total,
            'percentage' => $successpercentage,
            'threshold' => $threshold,
            'failed' => implode(', ', $failedchunks),
        ]);
        $this->assertDebuggingCalled($message);

        $successcount = 4;
        $successpercentage = 40;
        $failedchunks = [1, 2, 3, 4, 5, 6];

        // 40% success (critical).
        $result = $method->invoke($engine, $originalid, $docdata, $total, $successcount, $failedchunks);
        $this->assertFalse($result);
        $message = get_string('chunkingfailed_critical', 'search_elastic', [
            'docid' => $originalid,
            'success' => $successcount,
            'total' => $total,
            'percentage' => $successpercentage,
            'threshold' => $threshold,
            'failed' => implode(', ', $failedchunks),
        ]);
        $this->assertDebuggingCalled($message);

        $successcount = 0;
        $failedchunks = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        // 0% success (total failure).
        $result = $method->invoke($engine, $originalid, $docdata, $total, $successcount, $failedchunks);
        $this->assertFalse($result);
        $message = get_string('chunkingfailed_total', 'search_elastic', ['docid' => $originalid, 'total' => $total]);
        $this->assertDebuggingCalled($message);
    }

    /**
     * Test queue_document_deletions.
     */
    public function test_queue_document_deletions(): void {
        global $DB;

        $reflection = new ReflectionMethod($this->engine, 'queue_document_deletions');
        $reflection->setAccessible(true);

        $deletiondocs = [
            ['docid' => 'doc-1', 'is_chunk' => false, 'chunk_id' => 'doc-1'],
            ['docid' => 'doc-2', 'is_chunk' => true, 'chunk_id' => 'doc-2_c1'],
            ['docid' => 'doc-3', 'is_chunk' => false, 'chunk_id' => 'doc-3'],
        ];

        $reflection->invoke($this->engine, $deletiondocs);

        $tasks = $DB->get_records('task_adhoc', [
            'classname' => '\\search_elastic\\task\\delete_document_task',
        ]);
        $this->assertCount(1, $tasks);

        $task = reset($tasks);
        $data = json_decode($task->customdata);
        $this->assertIsArray($data->deletiondocs);
        $this->assertCount(3, $data->deletiondocs);
    }

    /**
     * Test delete_document_task.
     */
    public function test_delete_document_task(): void {
        // Generate course.
        $course = self::getDataGenerator()->create_course();

        set_config('fs_chunkmaxsize', 200, 'search_elastic');
        set_config('fs_chunkoverlapwords', 0, 'search_elastic');

        // Index a document with multiple chunks.
        $rec = new stdClass();
        $rec->content = str_repeat('Task test 1. ', 100);
        $rec->courseid = $course->id;
        $record = $this->generator->create_record($rec);
        $doc = $this->area->get_document($record);
        $this->engine->add_document($doc, false, $this->luceneversion);

        // Index a regular document.
        $rec->content = 'Task test 2';
        $record = $this->generator->create_record($rec);
        $doc = $this->area->get_document($record);

        $this->engine->add_document($doc, false, $this->luceneversion);

        sleep(2);

        // Verify chunks exist.
        $matchparams = [
            ['match' => ['original_id' => 'core_mocksearch-mock_search_area-1']],
        ];
        [$totalhits] = $this->get_indexed_document($matchparams);
        $this->assertEquals(7, $totalhits);

        // Verify regular document index exist.
        $matchparams = [
            ['match' => ['id' => 'core_mocksearch-mock_search_area-2']],
        ];
        [$totalhits] = $this->get_indexed_document($matchparams);
        $this->assertEquals(1, $totalhits);

        // Create and execute deletion task.
        $task = new delete_document_task();
        $task->set_custom_data([
            'deletiondocs' => [
                [
                    'docid' => 'core_mocksearch-mock_search_area-1',
                    'is_chunk' => true,
                    'chunk_id' => 'core_mocksearch-mock_search_area-1_c1',
                ],
                [
                    'docid' => 'core_mocksearch-mock_search_area-2',
                    'is_chunk' => false,
                    'chunk_id' => 'core_mocksearch-mock_search_area-2',
                ],
            ],
        ]);

        ob_start();
        $task->execute();
        ob_end_clean();

        sleep(2);

        // Verify chunks were deleted.
        $matchparams = [
            ['match' => ['original_id' => 'core_mocksearch-mock_search_area-1']],
        ];
        [$totalhits] = $this->get_indexed_document($matchparams);
        $this->assertEquals(0, $totalhits);

        // Verify regular document was deleted.
        $matchparams = [
            ['match' => ['id' => 'core_mocksearch-mock_search_area-2']],
        ];
        [$totalhits] = $this->get_indexed_document($matchparams);
        $this->assertEquals(0, $totalhits);
    }

    /**
     * Helper function get indexed document from Elasticsearch engine.
     *
     * $matchparams e.g [
     *    ['match' => ['original_id' => 'core_mocksearch-mock_search_area-1']],
     *    ['match' => ['areaid' => $document->get('areaid')]],
     *    ['match' => ['parentid' => $document->get('id')]],
     * ],
     *
     * @param array $matchparams
     * @return array
     */
    private function get_indexed_document(array $matchparams): array {
        $index = get_config('index', 'search_elastic');
        $indexurl = $this->engine->get_url() . '/' . $index . '/_search';

        $query = ['query' => ['bool' => ['must' => $matchparams]]];
        $jsonquery = json_encode($query);

        $client = new esrequest();
        $response = $client->post($indexurl, $jsonquery)->getBody();
        $results = json_decode($response);

        if (!isset($results->hits)) {
            $returnarray = [0, []];
        } else {
            if (is_object($results->hits->total)) {
                $totalhits = $results->hits->total->value;
            } else {
                $totalhits = $results->hits->total;
            }
            $returnarray = [$totalhits, $results->hits->hits];
        }
        return $returnarray;
    }

    /**
     * Helper function to create a forum discussion record.
     *
     * @param stdClass $forum
     * @return bool|int true or new id
     */
    private function create_forum_discussion(stdClass $forum): bool|int {
        global $DB;

        $discussion = (object)[
            'course' => $forum->course,
            'forum' => $forum->id,
            'name' => 'Test discussion',
            'userid' => 2,
            'timemodified' => time(),
        ];

        return $DB->insert_record('forum_discussions', $discussion);
    }
}
