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

use context;
use core_search\manager;
use Exception;
use search_elastic\engine;
use search_elastic\enrich\text\tika;
use search_elastic\local\chunking\manager as chunking_manager;
use search_elastic\local\model\error;
use stdClass;
use stored_file;
use Throwable;

/**
 * Service class for managing Elasticsearch errors.
 *
 * @package    search_elastic
 * @author     Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright  2025 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class error_service {
    /**
     * Retry a specific error by ID.
     *
     * @param  int $id The error ID to retry
     * @return array
     */
    public static function retry_error(int $id): array {
        try {
            $error = new error($id);

            if (!$error->can_retry()) {
                return ['success' => false, 'message' => get_string('exceededmaxretries', 'search_elastic')];
            }

            $error->mark_retrying();
            $result = self::execute_retry($error);

            // Reset the instance to the values in the database.
            $error->read();

            if ($result['success']) {
                // Delete the error record once it's successfully re-indexed.
                $error->delete();
            } else {
                $error->increment_retry();
            }

            return $result;
        } catch (\dml_missing_record_exception $e) {
            return ['success' => false, 'message' => get_string('erroridnotfound', 'search_elastic', $id)];
        } catch (Exception $e) {
            $error->increment_retry();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Saves an error to the database, creating new record or updating an existing one.
     *
     * @param string $documentid Document ID that failed
     * @param int $itemid Item ID
     * @param int|null $contextid Context ID
     * @param string $areaid Search area ID
     * @param string $errortype Type of error (indexing, tika)
     * @param string $errormessage Error message
     * @param int|null $contentmodified Content modification timestamp
     * @param string $parentid The parent ID used for file documents
     */
    public static function save_error(
        $documentid,
        $itemid,
        $contextid,
        $areaid,
        $errortype,
        $errormessage,
        $contentmodified = null,
        $parentid = null
    ): void {
        global $DB;

        $now = time();

        $select = $DB->sql_compare_text('docid') . ' = :docid AND errortype = :errortype';
        $params = [
            'docid' => $documentid,
            'errortype' => $errortype,
        ];

        $existing = error::get_records_select($select, $params);
        if ($existing) {
            // Update existing error (get the first one if multiple).
            $error = reset($existing);
            $error->set('errortype', $errortype);
            $error->set('errormessage', $errormessage);
            $error->set('status', error::STATUS_FAILED);
            $error->set('timemodified', $now);
            $error->set('contentmodified', $contentmodified);
            $error->save();
        } else {
            // Create new error.
            $error = new error();
            $error->set('docid', $documentid);
            $error->set('itemid', $itemid);
            $error->set('areaid', $areaid);
            $error->set('errortype', $errortype);
            $error->set('errormessage', $errormessage);
            $error->set('retrycount', 0);
            $error->set('status', error::STATUS_FAILED);
            $error->set('timecreated', $now);
            $error->set('timemodified', $now);
            $error->set('contentmodified', $contentmodified);
            $error->set('contextid', $contextid);
            $error->set('parentid', $parentid);
            $error->create();
        }
    }

    /**
     * Returns count of errors by status.
     *
     * @param  string $status Error status
     * @return int
     */
    public static function get_error_count_by_status($status): int {
        return error::count_records(['status' => $status]);
    }

    /**
     * Record a batch processing error by parsing the payload.
     *
     * @param  string $message Error message
     * @param  string $payload The json payload that failed
     * @param  int $debuglevel The level at which the debugging statement should show
     */
    public static function record_batch_error(string $message, string $payload, int $debuglevel = DEBUG_NORMAL): void {
        $lines = explode("\n", trim($payload));
        for ($i = 0; $i < count($lines); $i += 2) {
            if (isset($lines[$i]) && isset($lines[$i + 1])) {
                $meta = json_decode($lines[$i], true);
                $doc = json_decode($lines[$i + 1], true);

                if ($meta && $doc && isset($meta['index']['_id'])) {
                    $docid = $meta['index']['_id'];
                    $itemid = $doc['itemid'] ?? 0;
                    $areaid = $doc['areaid'] ?? 'unknown';
                    $contextid = $doc['contextid'] ?? 0;
                    $modified = $doc['modified'] ?? null;

                    debugging($message, $debuglevel);

                    self::save_error(
                        $docid,
                        $itemid,
                        $contextid,
                        $areaid,
                        error::TYPE_INDEXING,
                        $message . ' (Document: ' . $docid . ')',
                        $modified
                    );
                }
            }
        }
    }

    /**
     * Record an individual document error from Elasticsearch response.
     *
     * @param  string $message Error message
     * @param  array|null $docdata Array of document data
     * @param  int $debuglevel The level at which the debugging statement should show
     */
    public static function record_document_error(
        string $message,
        ?array $docdata = null,
        int $debuglevel = DEBUG_NORMAL
    ): void {
        self::store_document_error($message, $docdata, $debuglevel, error::TYPE_INDEXING);
    }

    /**
     * Record a chunking-related error for a document.
     *
     * @param  string $message Error message
     * @param  array|null $docdata Array of document data
     * @param  int $debuglevel The level at which the debugging statement should show
     */
    public static function record_chunking_error(
        string $message,
        ?array $docdata = null,
        int $debuglevel = DEBUG_NORMAL
    ): void {
        self::store_document_error($message, $docdata, $debuglevel, error::TYPE_CHUNKING);
    }

    /**
     * Core error recorder used by the public helpers.
     *
     * @param  string $message Error message
     * @param  array|null $docdata Array of document data
     * @param  int $debuglevel The level at which the debugging statement should show
     * @param  string Error type
     */
    private static function store_document_error(
        string $message,
        ?array $docdata = null,
        int $debuglevel = DEBUG_NORMAL,
        string $errortype = error::TYPE_CHUNKING
    ): void {
        if (is_null($docdata) || !isset($docdata['id'])) {
            return;
        }

        $documentinfo = self::extract_document_info($docdata);
        self::save_error(
            $documentinfo['docid'],
            $documentinfo['itemid'],
            $documentinfo['contextid'],
            $documentinfo['areaid'],
            $errortype,
            $message,
            $documentinfo['modified']
        );

        debugging($message, $debuglevel);
    }

    /**
     * Record a Tika-related error.
     *
     * @param  stored_file $file The file that caused an error
     * @param  string $message Error message
     * @param  string|null $parentid The file document parent ID
     * @param  int $debuglevel The level at which the debugging statement should show
     */
    public static function record_tika_error(
        stored_file $file,
        string $message,
        ?string $parentid = null,
        int $debuglevel = DEBUG_DEVELOPER
    ): void {
        $areaid = self::get_file_areaid($file);

        $errormessage = $message . ' (File: ' . $file->get_filename() . ', Size: ' . $file->get_filesize() . ' bytes' .
            ', Component: ' . $file->get_component() . ', Filearea: ' . $areaid . ')';

        $docid = $file->get_id();
        self::save_error(
            $docid,
            $file->get_itemid(),
            $file->get_contextid(),
            $areaid,
            error::TYPE_TIKA,
            $errormessage,
            $file->get_timemodified(),
            $parentid
        );

        debugging($errormessage, $debuglevel);
    }

    /**
     * Extract document information from document data array.
     *
     * @param  array|null $docdata Document data array
     * @return array Extracted document information
     */
    private static function extract_document_info(?array $docdata = null): array {
        $info = [
            'docid' => null,
            'areaid' => 'unknown',
            'itemid' => 0,
            'contextid' => null,
            'modified' => null,
        ];

        if ($docdata) {
            $info['docid'] = $docdata['id'] ?? null;
            $info['areaid'] = $docdata['areaid'] ?? $info['areaid'];
            $info['itemid'] = $docdata['itemid'] ?? $info['itemid'];
            $info['contextid'] = $docdata['contextid'] ?? $info['contextid'];
            $info['modified'] = $docdata['modified'] ?? $info['modified'];
        }

        return $info;
    }

    /**
     * Get search area ID for a file.
     *
     * @param  stored_file $file
     * @return string
     */
    private static function get_file_areaid(stored_file $file): string {
        $component = $file->get_component();
        $filearea = $file->get_filearea();

        $searchareas = manager::get_search_areas_list(true);
        $corecomponent = 'core_' . $component;

        foreach ($searchareas as $areaid => $searcharea) {
            if (strpos($areaid, $component) === 0 || strpos($areaid, $corecomponent) === 0) {
                if (method_exists($searcharea, 'get_search_fileareas')) {
                    $fileareas = $searcharea->get_search_fileareas();
                    if (in_array($filearea, $fileareas)) {
                        return $areaid;
                    }
                }
            }
        }

        return $component . '-' . $filearea;
    }

    /**
     * Execute the actual retry logic based on error type.
     *
     * @param error $error Error instance
     * @return array Result array with 'success' and 'message' keys
     */
    private static function execute_retry(error $error): array {
        try {
            $engine = new engine();
            $searcharea = manager::get_search_area($error->get('areaid'));
            if (!$searcharea) {
                $error->mark_obsolete();
                return ['success' => false, 'message' => get_string('searchareanotfound', 'search_elastic')];
            }
        } catch (Throwable $e) {
            $error->mark_obsolete();
            return ['success' => false, 'message' => get_string('searchareanotfound', 'search_elastic')];
        }

        switch ($error->get('errortype')) {
            case error::TYPE_TIKA:
                return self::retry_file_extraction_and_indexing($error, $searcharea, $engine);
            case error::TYPE_INDEXING:
            default:
                return self::retry_failed_document_indexing($error, $searcharea, $engine);
        }
    }

    /**
     * Check if the document associated with an error has files that can be indexed.
     *
     * @param error $error Error instance
     * @return bool True if the document has files available for indexing
     */
    public static function error_has_indexable_files($error): bool {
        $searcharea = manager::get_search_area($error->get('areaid'));
        if (!$searcharea) {
            return false;
        }

        $context = context::instance_by_id($error->get('contextid'));
        $record = self::get_record_for_context($searcharea, $context, $error->get('itemid'));
        if (!$record) {
            return false;
        }

        $document = $searcharea->get_document($record);
        if (!$document) {
            return false;
        }

        $searcharea->attach_files($document);
        $files = $document->get_files();

        return !empty($files);
    }

    /**
     * Retry a failed document indexing operation.
     *
     * @param error $error Error instance
     * @param \core_search\base $searcharea Search area instance
     * @param engine $engine Elasticsearch engine
     * @return array Result array
     */
    private static function retry_failed_document_indexing($error, $searcharea, $engine) {
        try {
            $itemid = $error->get('itemid');
            $context = context::instance_by_id($error->get('contextid'));
            $config = get_config('search_elastic');

            // Get the record from search area.
            $record = self::get_record_for_context($searcharea, $context, $itemid);
            if (!$record) {
                $error->mark_obsolete();
                return['success' => false, 'message' => 'Content no longer exists'];
            }

            // Let the search area convert the record to a document.
            $document = $searcharea->get_document($record);
            if (!$document) {
                $error->mark_failed();
                return ['success' => false, 'message' => 'Search area could not create document from record'];
            }

            $chunkingenabled = chunking_manager::is_chunking_enabled();
            if (!$chunkingenabled) {
                // Check document size before attempting to index.
                $docdata = $document->export_for_engine();
                $docsize = strlen(json_encode($docdata));
                $maxsize = (int)$config->sendsize;
                if ($docsize > $maxsize) {
                    // Can't index this document because it's too large.
                    $error->mark_failed();
                    return [
                        'success' => false,
                        'message' => "Document too large ($docsize) bytes exceeds $config->sendsize bytes limit).",
                    ];
                }
            }

            // Index the parent document first.
            $success = $engine->add_document($document, false);
            if (!$success) {
                $error->mark_failed();
                return ['success' => false, 'message' => 'Failed to index document'];
            }

            // Check if we need to index files.
            $indexfiles = $engine->file_indexing_enabled() && $searcharea->uses_file_indexing();
            if (!$indexfiles) {
                return ['success' => true, 'message' => 'Content reindexed successfully'];
            }

            // Index files individually.
            $searcharea->attach_files($document);
            $files = $document->get_files();
            if (empty($files)) {
                return ['success' => true, 'message' => 'Content reindexed successfully'];
            }

            $fileerrorcount = 0;
            $filesskipped = 0;

            foreach ($files as $file) {
                $filedocdata = $document->export_file_for_engine($file);

                if (!$chunkingenabled) {
                    // Check file document size.
                    $filesize = strlen(json_encode($filedocdata));
                    if ($filesize > $config->sendsize) {
                        $filesskipped++;
                        debugging("Skipping file (too large): {$file->get_filename()} " .
                            "($filesize bytes, exceeds $maxsize bytes limit)");
                        continue;
                    }
                }

                $success = $engine->index_single_document($filedocdata);
                if (!$success) {
                    $fileerrorcount++;
                }
            }

            if ($fileerrorcount == 0) {
                return ['success' => true, 'message' => 'Content reindexed successfully'];
            } else if ($filesskipped > 0 && $fileerrorcount == 0) {
                return [
                    'success' => true, 'message' => "Content reindexed $filesskipped file(s) skipped - too large",
                ];
            } else if ($filesskipped > 0 && $fileerrorcount > 0) {
                $error->mark_failed();
                return [
                    'success' => false,
                    'message' => "Failed to reindex some files. $filesskipped file(s) skipped (too large).",
                ];
            }

            $error->mark_failed();
            return ['success' => false, 'message' => 'Failed to reindex content'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Exception during retry: {$e->getMessage()}"];
        }
    }

    /**
     * Get record for context and item ID.
     *
     * @param \core_search\base $searcharea Search area instance
     * @param context $context Context instance
     * @param int $itemid Item ID
     * @return stdClass|null Record or null if not found
     */
    private static function get_record_for_context($searcharea, $context, $itemid): ?stdClass {
        $recordset = $searcharea->get_document_recordset(0, $context);
        if (!$recordset) {
            return null;
        }

        $foundrecord = null;
        foreach ($recordset as $record) {
            if ($record->id == $itemid) {
                $foundrecord = $record;
                break;
            }
        }

        $recordset->close();
        return $foundrecord;
    }

    /**
     * Retry a file content extraction and parent document re-indexing.
     *
     * This method takes a Tika processing error and attempts to re-extract the file content
     * and re-index it with its parent document using the parentid reference.
     *
     * @param error $error Error instance
     * @param \core_search\base $searcharea Search area instance
     * @param engine $engine Elasticsearch engine
     * @return array Result array
     */
    private static function retry_file_extraction_and_indexing($error, $searcharea, $engine): array {
        if (!$error->get('docid')) {
            return ['success' => false, 'message' => 'No file ID associated with Tika error'];
        }

        try {
            $fs = get_file_storage();
            // The docid for Tika errors should be the file ID.
            $file = $fs->get_file_by_id($error->get('docid'));
            // We should also check if the actual file exists.
            $handle = $file->get_content_file_handle();
            if (!$file || !$handle) {
                $error->mark_obsolete();
                return ['success' => false, 'message' => 'File no longer exists'];
            }

            // Get the record from search area.
            $parentid = $error->get('parentid');
            $parentidparts = explode('-', $parentid);
            $parentitemid = isset($parentidparts[2]) ? (int)$parentidparts[2] : null;
            $context = context::instance_by_id($error->get('contextid'));
            $parentrecord = self::get_record_for_context($searcharea, $context, $parentitemid);
            if (!$parentrecord) {
                $error->mark_obsolete();
                return['success' => false, 'message' => 'Content no longer exists'];
            }

            // Create the parent document.
            $parentdocument = $searcharea->get_document($parentrecord);
            if (!$parentdocument) {
                $error->mark_failed();
                return ['success' => false, 'message' => 'Failed to create parent document'];
            }

            // Try to extract file contents using Tika. The export_file_for_engine does not throw
            // an exception if there is any issue with the file, so we try to validate the file
            // here instead of just indexing a file with empty file text.
            $extractedtext = self::extract_file_contents($file);
            if ($extractedtext === false) {
                $error->mark_failed();
                return ['success' => false, 'message' => 'Tika extraction failed'];
            }

            $parentdocument->add_stored_file($file);

            // Create a file-specific document for indexing.
            $filedocdata = $parentdocument->export_file_for_engine($file);

            // Index just this specific file document.
            $success = $engine->index_single_document($filedocdata);

            if ($success) {
                return ['success' => true, 'message' => 'File content extracted and indexed successfully'];
            } else {
                $error->mark_failed();
                return ['success' => false, 'message' => 'File extraction succeeded but indexing failed'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Exception during Tika retry: ' . $e->getMessage()];
        }
    }

    /**
     * Extract file contents using Tika.
     *
     * @param  stored_file $file File to extract.
     * @return string Extracted text or false on failure
     */
    private static function extract_file_contents(stored_file $file) {
        $config = get_config('search_elastic');

        if (empty($config->tikahostname) || empty($config->tikaport)) {
            debugging('Tika not configured for retry');
            return false;
        }

        try {
            $tikaprocessor = new tika($config);
            $extractedtext = $tikaprocessor->analyze_file($file);

            if ($extractedtext == false || $extractedtext == null) {
                debugging('Tika returned false/null for file' . $file->get_filename());
                return false;
            }

            $extractedtext = trim($extractedtext);
            if (empty($extractedtext)) {
                debugging('Tika returned empty text for file: ' . $file->get_filename());
                return false;
            }

            return $extractedtext;
        } catch (Exception $e) {
            debugging('Exception during Tika extraction: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }
}
