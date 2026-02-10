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
 * Elasticsearch engine.
 *
 * Provides an interface between Moodles Global search functionality
 * and the Elasticsearch (https://www.elastic.co/products/elasticsearch)
 * search engine.
 *
 * Elasticsearch presents a REST Webservice API that we communicate with
 * via Curl.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic;

use search_elastic\chunking\manager;
use search_elastic\chunking\fixed_size;
use search_elastic\local\service\error_service;
use stdClass;
use Exception;

/**
 * Elasticsearch engine.
 *
 * @package     search_elastic
 * @copyright   2018 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class engine extends \core_search\engine {
    /**
     * @var int Factor to multiply fetch limit by when getting results.
     */
    protected $totalresultdocs = 0;

    /**
     * @var bool The payload to be sent to the Elasticsearch service.
     */
    protected $payload = false;

    /**
     * @var int The current size of the payload object.
     */
    protected $payloadsize = 0;

    /**
     * @var int Count of how many parent documents are in current payload.
     */
    protected $count = 0;

    /**
     *
     * @var array Configuration defaults.
     */
    protected $configdefaults = [
            'fileindexing' => 0,
            'hostname' => 'http://127.0.0.1',
            'port' => 9200,
            'index' => 'moodle',
            'sendsize' => 9000000,
            'logging' => 0,
    ];

    /**
     * Initialises the search engine configuration.
     *
     * Search engine availability should be checked separately.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->config = (object)array_merge($this->configdefaults, (array)$this->config);
    }

    /**
     * A debug function, dumps to the php log
     *
     * @param string $msg Log message
     */
    private function log($msg) {
        if ($this->config->logging) {
            // @codingStandardsIgnoreStart
            error_log('search_elastic: ' . $msg);
            // @codingStandardsIgnoreEnd
        }
    }

    /**
     * Generates the Elasticsearch server endpoint URL from
     * the config hostname and port.
     *
     * @return url|bool Returns url if succes or false on error.
     */
    public function get_url() {
        $returnval = false;

        if (!empty($this->config->hostname) && !empty($this->config->port)) {
            $url = rtrim($this->config->hostname, "/");
            $port = $this->config->port;
            return $url . ':' . $port;
        }

        return $returnval;
    }

    /**
     * Check if index exists in Elasticssearch backend
     *
     * @return bool True on success False on failure
     */
    private function check_index() {
        $returnval = false;
        $response = 404;
        $url = $this->get_url();
        $client = new \search_elastic\esrequest();

        if (!empty($this->config->index) && $url) {
            $index = $url . '/' . $this->config->index;
            $response = $client->get($index);
            $responsecode = $response->getStatusCode();
        }
        if ($responsecode == 200) {
            $returnval = true;
        }

        return $returnval;
    }

    /**
     * Check if the elasticsearch index is valid.
     *
     * @return boolean $valid If the index is valid.
     */
    public function validate_index() {
        $valid = true;

        // Get existing index definition.
        $url = $this->get_url();
        $indexeurl = $url . '/' . $this->config->index . '/_mapping';
        $client = new \search_elastic\esrequest();
        $response = $client->get($indexeurl);
        $responsebody = json_decode($response->getBody());
        if ($this->get_es_lucene_version() < 8) {
            $indexfields = $responsebody->{$this->config->index}->mappings->doc->properties;
        } else {
            $indexfields = $responsebody->{$this->config->index}->mappings->properties;
        }

        // Iterrate through required fields and compare to index.
        $requiredfields = \search_elastic\document::get_required_fields_definition();
        foreach ($requiredfields as $name => $field) {
            if ($indexfields->{$name}->type != $field['type']) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Get the version of the attached Elasticsearch / OpenSearch service.
     *
     * @return object Apache Lucene version details.
     */
    private function get_es_version_details() {
        $url = $this->get_url();
        $client = new \search_elastic\esrequest();
        $response = $client->get($url);
        $responsebody = json_decode($response->getBody());

        return $responsebody->version;
    }

    /**
     * Get the Apache Lucene version of the attached Elasticsearch / OpenSearch service.
     *
     * @return integer The Apache Lucene version.
     */
    public function get_es_lucene_version() {
        return $this->get_es_version_details()->lucene_version;
    }

    /**
     * Get the version of the attached Elasticsearch / OpenSearch service.
     *
     * @return integer The Elasticsearch / OpenSearch version.
     */
    public function get_es_version() {
        return $this->get_es_version_details()->number;
    }

    /**
     * Get the Elasticsearch mapping.
     *
     * @param integer $luceneversion The version of Apache Lucene to get the mapping for.
     * @return array $mapping  The Elasticsearch mapping.
     */
    public function get_mapping($luceneversion = 0) {
        $requiredfields = \search_elastic\document::get_required_fields_definition();
        $optionalfields = \search_elastic\document::get_optional_fields_definition();
        $fields = array_merge($requiredfields, $optionalfields);

        // We need to change some of the mappings if Apache Lucene version is less than 8.
        if (!$luceneversion) {
            $luceneversion = $this->get_es_lucene_version();
        }
        if ($luceneversion < 8) {
            $mapping = ['mappings' => ['doc' => ['properties' => $fields]]];
        } else {
            $mapping = ['mappings' => ['properties' => $fields]];
        }

        return $mapping;
    }

    /**
     * Create index with mapping in Elasticsearch backend
     */
    private function create_index() {
        $url = $this->get_url();
        $client = new \search_elastic\esrequest();
        if (!empty($this->config->index) && $url) {
            $indexurl = $url . '/' . $this->config->index;
            $mapping = $this->get_mapping();
            $response = $client->put($indexurl, json_encode($mapping));
            $responsecode = $response->getStatusCode();
        } else {
            throw new \moodle_exception('noconfig', 'search_elastic', '');
        }
        if ($responsecode !== 200) {
            throw new \moodle_exception('indexfail', 'search_elastic', '');
        }
    }

    /**
     * Is the Elasticsearch server endpoint configured in Moodle
     * and available.
     *
     * @param object|false $stack Optional custom Guzzle HTTP stack.
     * @return string|bool true if server is ready, else error string.
     */
    public function is_server_ready($stack = false) {
        global $CFG;
        // Not configured yet.
        if (empty($this->get_url())) {
            return get_string('connection:na', 'search_elastic');
        }

        // Test a connection to the configured server.
        $status = $this->get_server_status_code($stack);

        if ($status !== 200) {
            return get_string('connection:status', 'search_elastic', [
              'url' => $this->get_url(),
              'status' => $status,
            ]);
        }

        return true;
    }

    /**
     * Tests connection to the server and returns the HTTP status code.
     *
     * @param object|false $stack Optional custom Guzzle HTTP stack.
     * @return int HTTP response code.
     */
    public function get_server_status_code($stack = false): int {
        $url = $this->get_url();

        // Regardless the setting timeout, check timeout should be always 5.
        $client = new \search_elastic\esrequest($stack, 5);
        $responsecode = 503;
        if ($url) {
            try {
                $response = $client->get($url);
                $responsecode = $response->getStatusCode();
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                return 503;
            }
        }

        return $responsecode;
    }

    /**
     * Called when indexing is triggered.
     * Creates the Index namespace and adds fields if they don't exist.
     *
     * @param bool $fullindex is this a full index of site.
     */
    public function index_starting($fullindex = false) {
        $fullindex = true;  // Always assume full index.
        if ($fullindex) {
            // Check if index exists and create it if it doesn't.
            $hasindex = $this->check_index();
            if (!$hasindex) {
                $this->create_index();
            }
        }
    }

    /**
     * Get the currently indexed files for a particular document, returns the total count, and a subset of files.
     *
     * @param document $document
     * @param int      $start The row to start the results on. Zero indexed.
     * @param int      $rows The number of rows to fetch
     * @return array   A two element array, the first is the total number of availble results, the second is an array
     *                 of documents for the current request.
     */
    private function get_indexed_files($document, $start = 0, $rows = 500) {
        $url = $this->get_url();
        $indexeurl = $url . '/' . $this->config->index . '/_search';
        $client = new \search_elastic\esrequest();
        // TODO: move this to document class.
        $query = ['query' => [
                'bool' => [
                        'must' => [
                            ['match' => ['type' => 2]],
                            ['match' => ['areaid' => $document->get('areaid')]],
                            ['match' => ['parentid' => $document->get('id')]],
                        ],
                ]],
                '_source' => [
                    'id',
                    'modified',
                    'filecontenthash',
                    'title',
                    'original_id',
                ],
                'from' => $start,
                'size' => $rows,
                ];
        $jsonquery = json_encode($query);
        $response = $client->post($indexeurl, $jsonquery)->getBody();
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
     * Return a count of total records for the most recently completed
     * execute_query().
     * Must be implemented to return the number of results that available
     * for the most recent call to execute_query().
     * This is used to determine how many pages will be displayed in the paging bar.
     * For more discussion see MDL-53758.
     *
     * @return int
     */
    public function get_query_total_count() {
        return $this->totalresultdocs;
    }

    /**
     * Given an array of files, remove these from the index.
     *
     * Handles both regular file documents and chunked file documents.
     *
     * @param array $idstodelete Array of file deletion info
     */
    private function delete_indexed_files(array $idstodelete) {
        if (empty($idstodelete)) {
            return;
        }

        // Delete files that are no longer attached.
        foreach ($idstodelete as $deletion) {
            $fileid = $deletion['id'];
            $ischunk = $deletion['is_chunk'] ?? false;
            if ($ischunk) {
                $this->delete_by_original_id($fileid);
            } else {
                $this->delete_by_id($fileid);
            }
        }
    }

    /**
     * Delete all documents in a specific search area.
     *
     * Handles both regular documents and chunked documents. For chunked documents,
     * deletes all chunks associated with each original document.
     *
     * @param string $areaid Area ID to delete
     * @return bool
     */
    private function delete_by_area($areaid): bool {
        return $this->delete_by_query(
            ['term' => ['areaid' => $areaid]],
            "area {$areaid}"
        );
    }

    /**
     * Delete all chunks for a document by original_id.
     *
     * @param string $originaldocid The original document ID.
     * @return bool
     */
    public function delete_by_original_id(string $originaldocid): bool {
        return $this->delete_by_query(
            ['term' => ['original_id' => $originaldocid]],
            "original document {$originaldocid}"
        );
    }

    /**
     * Execute a delete_by_query operation.
     *
     * @param array $queryfilter The query filter (e.g. ['term' => ['areaid' => 'test']])
     * @param string $description Description of what's being deleted for logging
     * @return bool
     */
    private function delete_by_query(array $queryfilter, string $description): bool {
        $url = $this->get_url() . '/' . $this->config->index . '/_delete_by_query';
        $client = new esrequest();
        $query = ['query' => $queryfilter];

        try {
            $response = $client->post($url, json_encode($query));
            $responsecode = $response->getStatusCode();
            $responsebody = json_decode($response->getBody());

            if ($responsecode === 200) {
                $deletedcount = $responsebody->deleted ?? 0;
                debugging("Deleted {$deletedcount} document(s) for {$description}.", DEBUG_DEVELOPER);
                return true;
            }

            debugging("Delete failed: " . json_encode($responsebody), DEBUG_DEVELOPER);
            return false;
        } catch (Exception $e) {
            debugging("Failed to delete chunks for {$originaldocid}: " . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Given a document, get that documents associated files
     * and update them in the index.
     *
     * @param \core_search\document $document The document whose files to index/
     * @return array files to add to index and files to delete from index.
     */
    private function filter_indexed_files($document) {
        $rows = 500; // Maximum rows to process at a time.
        $files = $document->get_files(); // Get the attached files.
        // We do this progressively, so we can handle lots of files cleanly.
        [$numfound, $indexedfiles] = $this->get_indexed_files($document, 0, $rows);
        $count = 0;
        $idstodelete = [];
        $processedoriginals = []; // Track which original files we've already processed.

        do {
            // Go through each indexed file. We want to not index any stored and unchanged ones, delete any missing ones.
            foreach ($indexedfiles as $indexedfile) {
                $fileid = $indexedfile->_source->id;

                // Check if this is a chunk (has original_id field).
                $originalfileid = $indexedfile->_source->original_id ?? $fileid;
                $ischunk = isset($indexedfile->_source->original_id) && $indexedfile->_source->original_id !== $fileid;

                // Skip if we already processed this original file.
                if (isset($processedoriginals[$originalfileid])) {
                    continue;
                }

                // Check against the original file ID (not chunk ID).
                if (isset($files[$originalfileid])) {
                    // Check for changes that would mean we need to re-index the file. If so, just leave in $files.
                    // Filelib does not guarantee time modified is updated, so we will check important values.
                    $needsreindex = false;

                    if ($indexedfile->_source->modified != $files[$originalfileid]->get_timemodified()) {
                        $needsreindex = true;
                    }
                    if (strcmp($indexedfile->_source->title, $files[$originalfileid]->get_filename()) !== 0) {
                        $needsreindex = true;
                    }
                    if ($indexedfile->_source->filecontenthash != $files[$originalfileid]->get_contenthash()) {
                        $needsreindex = true;
                    }

                    // If the file is already indexed, we can just remove it from the files array and skip it.
                    if (!$needsreindex) {
                        unset($files[$fileid]);
                    } else {
                        // File changed so we need to delete the old version (including chunks) and re-index.
                        $idstodelete[$originalfileid] = [
                            'id' => $originalfileid,
                            'is_chunk' => $ischunk,
                            'type' => $indexedfile->_type,
                        ];
                    }

                    $processedoriginals[$originalfileid] = true;
                } else {
                    // This means we have found a file that is no longer attached, so we need to delete from the index.
                    // We do it later, since this is progressive, and it could reorder results.
                    $idstodelete[$originalfileid] = [
                        'id' => $originalfileid,
                        'is_chunk' => $ischunk,
                        'type' => $indexedfile->_type,
                    ];
                    $processedoriginals[$originalfileid] = true;
                }
            }
            $count += $rows;

            if ($count < $numfound) {
                // If we haven't hit the total count yet, fetch the next batch.
                [$numfound, $indexedfiles] = $this->get_indexed_files($document, $count, $rows);
            }
        } while ($count < $numfound);

        return [$files, $idstodelete];
    }

    /**
     * Given a document object, transform into formatted JSON ready to be
     * sent to Elasticsearch.
     *
     * @param object $docdata Object containing document information to index.
     * @param integer $luceneversion Apache Lucene version to get the mapping for.
     * @return string The JSON representation of doc data, ready to be indexed.
     */
    private function create_payload($docdata, $luceneversion = 0) {

        // We need to change some of the mappings if Apache Lucene version is less than 8.
        if (!$luceneversion) {
            $luceneversion = $this->get_es_lucene_version();
        }
        if ($luceneversion < 8) {
            $meta = ['index' => ['_index' => $this->config->index,
                                 '_type' => 'doc',
                                 '_id' => $docdata['id']]];
        } else {
            $meta = ['index' => ['_index' => $this->config->index,
                                 '_id' => $docdata['id']]];
        }

        $jsonmeta = json_encode($meta);
        $jsondoc = json_encode($docdata);
        $jsonpayload = $jsonmeta . "\n" . $jsondoc . "\n";

        // Return false if we can't JSON encode the document data.
        if ($jsonmeta == false || $jsondoc == false) {
            $jsonpayload = false;
        }

        return $jsonpayload;
    }

    /**
     * Add files to the index.
     *
     * @param document $document document
     */
    private function process_document_files($document) {
        // Handle already indexed Files.
        $files = [];
        if (!$document->get_is_new()) {
            // If this isn't a new document, we need to check the exiting indexed files.
            [$files, $idstodelete] = $this->filter_indexed_files($document);

            // Delete files that are no longer attached.
            $this->delete_indexed_files($idstodelete);
        } else {
            $files = $document->get_files();
        }

        // Check if chunking is enabled.
        $chunkingenabled = (bool)$this->config->enablechunking;

        $strategy = null;
        $options = [];
        if ($chunkingenabled) {
            // Get chunking strategy and options.
            $strategy = manager::get_configured_strategy();
            $options = $this->get_chunking_options();
        }

        foreach ($files as $fileid => $file) {
            $filedocdata = $document->export_file_for_engine($file);
            $filetext = $filedocdata['filetext'] ?? '';

            $needschunking = false;
            $contentchunks = [];
            if ($chunkingenabled) {
                // Check if chunking is needed for content.
                if (!empty($filetext)) {
                    $contentchunks = $strategy->chunk($filetext, $options);
                }

                // Determine if chunking occured.
                $needschunking = count($contentchunks) > 1;
            }

            if (!$needschunking) {
                $jsonpayload = $this->create_payload($filedocdata);
                if ($jsonpayload) {
                    $this->batch_add_documents($jsonpayload);
                }
            } else {
                $this->add_file_chunks($filedocdata, $contentchunks);
            }
        }

        $this->batch_add_documents(false, false, true);
    }


    /**
     * Get chunking options from plugin configuration.
     *
     * @return array Configured chunking options
     */
    private function get_chunking_options(): array {
        $options = [];
        $strategy = manager::get_configured_strategy();
        if ($strategy instanceof fixed_size) {
            $options = [
                'maxsize' => (int)$this->config->fs_chunkmaxsize,
                'overlap' => (int)$this->config->fs_chunkoverlapwords,
            ];
        }

        return $options;
    }

    /**
     * Add file document as multiple chunks to the batch payload.
     *
     * @param array $docdata The original file document data.
     * @param array $filechunks The file chunks from strategy.
     */
    private function add_file_chunks($docdata, $filechunks): void {
        $originaldocid = $docdata['id'];
        $totalchunks = count($filechunks);
        $successcount = 0;
        $failedchunks = [];

        for ($i = 0; $i < $totalchunks; $i++) {
            $chunknumber = $i + 1;

            try {
                // Create chunk data.
                $chunkdata = $this->create_chunk_data(
                    $docdata,
                    $chunknumber,
                    $totalchunks,
                    null,
                    $filechunks[$i] ?? null
                );

                // Add to batch payload.
                $jsonpayload = $this->create_payload($chunkdata);
                if ($jsonpayload) {
                    $this->batch_add_documents($jsonpayload);
                    $successcount++;
                } else {
                    $failedchunks[] = $chunknumber;
                }
            } catch (Exception $e) {
                $failedchunks[] = $chunknumber;
                debugging("Exception creating chunk {$chunknumber}: {$e->getMessage()}");
            }
        }

        $this->handle_chunk_results(
            $originaldocid,
            $docdata,
            $totalchunks,
            $successcount,
            $failedchunks
        );
    }

    /**
     * Get the highlighted result sections and use them to replace the
     * source sections.
     *
     * @param object $result the original search result.
     * @return object $highlightedsource the result object with highlighting.
     */
    public function highlight_result($result) {

        if (property_exists($result, 'highlight')) {
            $query = new \search_elastic\query();

            foreach ($result->highlight as $highlightfield => $value) {
                $result->_source->$highlightfield = $value[0]; // Replace _source element with highlight element.
            }
            $highlightedsource = $result;
        } else {
            $highlightedsource = $result;
        }

        return $highlightedsource;
    }

    /**
     * Loop through given iterator of search documents
     * and and have the search engine back end add them
     * to the index.
     *
     * @param iterator $iterator the iterator of documents to index
     * @param searcharea $searcharea the area for the documents to index
     * @param aray $options document indexing options
     * @return array Processed document counts
     */
    public function add_documents($iterator, $searcharea, $options) {
        $numrecords = 0;
        $numdocs = 0;
        $numdocsignored = 0;
        $lastindexeddoc = 0;
        $firstindexeddoc = 0;
        $partial = false;

        // Check if chunking is enabled.
        $chunkingenabled = (bool)$this->config->enablechunking;

        $strategy = null;
        $chunkingoptions = [];
        if ($chunkingenabled) {
            // Get chunking strategy and options.
            $strategy = manager::get_configured_strategy();
            $chunkingoptions = $this->get_chunking_options();
        }

        // First we'll process all the documents, then if we
        // are processing files we'll itterate through again and just add the files.
        foreach ($iterator as $document) {
            // Stop if we have exceeded the time limit (and there are still more items). Always
            // do at least one second's worth of documents otherwise it will never make progress.
            if (
                $lastindexeddoc !== $firstindexeddoc &&
                    !empty($options['stopat']) && microtime(true) >= $options['stopat']
            ) {
                        $partial = true;
                        break;
            }

            if (!$document instanceof \core_search\document) {
                continue;
            }
            if (isset($options['lastindexedtime']) && $options['lastindexedtime'] == 0) {
                // If we have never indexed this area before, it must be new.
                $document->set_is_new(true);
            }

            $lastindexeddoc = $document->get('modified');
            $docdata = $document->export_for_engine();

            $numrecords++;

            $needschunking = false;
            $contentchunks = [];
            if ($chunkingenabled) {
                // Check if chunking is needed.
                if (!empty($docdata['content'])) {
                    $contentchunks = $strategy->chunk($docdata['content'], $chunkingoptions);
                }
                $needschunking = count($contentchunks) > 1;
            }

            if (!$needschunking) {
                // Small document or chunking disabled - add to batch normally.
                $jsonpayload = $this->create_payload($docdata);
                if ($jsonpayload) {
                    $numdocsignored += $this->batch_add_documents($jsonpayload, true);
                } else {
                    $numdocsignored++;
                }
            } else {
                // Large document - create chunks and add to batch.
                $ignored = $this->batch_add_document_chunks($docdata, $contentchunks);
                $numdocsignored += $ignored;
            }

            if ($options['indexfiles']) {
                $searcharea->attach_files($document);
                $this->process_document_files($document);
            }
        }

        $numdocsignored += $this->batch_add_documents(false, true, true);
        $numdocs = $numrecords - $numdocsignored;

        if (method_exists($this, 'supports_add_document_batch')) {
            $numbatches = 0;  // TODO: fix https://github.com/catalyst/moodle-search_elastic/issues/67.
            return [$numrecords, $numdocs, $numdocsignored, $lastindexeddoc, $partial, $numbatches];
        }

        return [$numrecords, $numdocs, $numdocsignored, $lastindexeddoc, $partial];
    }

    /**
     * Add the payload object containing document information
     * in JSON format to the Elasticsearch index.
     *
     * @param string $jsonpayload
     * @param bool $isdoc
     * @param bool $sendnow
     * @return number Number of documents not indexed.
     */
    private function batch_add_documents($jsonpayload, $isdoc = false, $sendnow = false) {
        $numdocsignored = 0;
        if (!$sendnow) {
            $this->payload .= $jsonpayload;
            $this->payloadsize += strlen($jsonpayload);
        }

        // Track how many parent docs are in the request.
        if ($isdoc) {
            $this->count++;
        }

        // Some Elastic search providers such as AWS have a limit on how big the
        // HTTP payload can be. Therefore we limit it to a size in bytes.
        // If we don't have enough data to send yet return early.
        if ($this->payloadsize < $this->config->sendsize && !$sendnow) {
            return $numdocsignored;
        }

        // Make sure we have at least some data to send.
        if ($this->payloadsize <= 0) {
            return $numdocsignored;
        }

        // Send the bulk request.
        $url = $this->get_url();
        $client = new \search_elastic\esrequest();
        $docurl = $url . '/' . $this->config->index . '/_bulk';
        $response = $client->post($docurl, $this->payload);
        $responsebody = json_decode($response->getBody());
        $statuscode = $response->getStatusCode();

        // Handle different response scenarios.
        if ($statuscode == 413) {
            $numdocsignored = $this->handle_413_retry();
        } else if ($statuscode >= 300) {
            $message = get_string('addfail', 'search_elastic') . ' Error Code: ' . $statuscode;
            error_service::record_batch_error($message, $this->payload);
            $numdocsignored = $this->count;
        } else if (isset($responsebody->errors) && $responsebody->errors) {
            $numdocsignored = $this->log_bulk_response_item_errors($responsebody);
        }

        // Reset the counts.
        $this->payload = false;
        $this->payloadsize = 0;

        // Reset the parent doc count after attempting to add.
        if ($isdoc) {
            $this->count = 0;
        }

        return $numdocsignored;
    }

    /**
     * Add document chunks to the batch payload.
     *
     * @param array $docdata Original document data
     * @param array $contentchunks Content chunks from strategy
     * @return int 0 if document successfully indexed, 1 if document failed
     */
    private function batch_add_document_chunks($docdata, $contentchunks): bool {
        $originaldocid = $docdata['id'];
        $totalchunks = count($contentchunks);
        $successcount = 0;
        $failedchunks = [];

        for ($i = 0; $i < $totalchunks; $i++) {
            $chunknumber = $i + 1;

            try {
                // Create chunk data.
                $chunkdata = $this->create_chunk_data(
                    $docdata,
                    $chunknumber,
                    $totalchunks,
                    $contentchunks[$i] ?? null
                );

                // Create payload and add to batch.
                $jsonpayload = $this->create_payload($chunkdata);
                if ($jsonpayload) {
                    $this->batch_add_documents($jsonpayload, false);
                    $successcount++;
                } else {
                    $failedchunks[] = $chunknumber;
                }
            } catch (Exception $e) {
                $failedchunks[] = $chunknumber;
                debugging("Exception creating chunk {$chunknumber}: " . $e->getMessage());
            }
        }

        $documentsuccess = $this->handle_chunk_results(
            $originaldocid,
            $docdata,
            $totalchunks,
            $successcount,
            $failedchunks
        );

        // Return document level failure count (0 or 1).
        return $documentsuccess ? 0 : 1;
    }

    /**
     * Handle 413 payload too large error by retrying documents individually.
     *
     * @return int Number of documents ignored/failed.
     */
    private function handle_413_retry(): int {
        // Retry sending payload one record at a time.
        $payloaddocs = $this->parse_payload_documents();
        $retryignored = 0;
        $maxsize = (int)$this->config->sendsize;

        foreach ($payloaddocs as $doc) {
            if (is_null($doc)) {
                $retryignored++;
                continue;
            }

            // Check if individual document is too large.
            $docsize = strlen(json_encode($doc));
            if ($docsize > $maxsize) {
                // Document is too large - try chunk if enabled.
                $chunkingenabled = (bool)$this->config->enablechunking;
                if ($chunkingenabled) {
                    if ($this->retry_with_chunking($doc)) {
                        continue;
                    } else {
                        $retryignored++;
                        error_service::record_document_error(
                            get_string('handle413retryfailedchunking', 'search_elastic', [
                                'docsize' => $docsize,
                                'maxsize' => $maxsize,
                                'docid' => $doc['id'],
                            ]),
                            $doc
                        );
                        continue;
                    }
                } else {
                    $retryignored++;
                        error_service::record_document_error(
                            get_string('handle413retrychunkingdisabled', 'search_elastic', [
                                'docsize' => $docsize,
                                'maxsize' => $maxsize,
                                'docid' => $doc['id'],
                            ]),
                            $doc
                        );
                    continue;
                }
            }

            // Document is small enough - try to index normally.
            if (!$this->index_single_document($doc)) {
                $retryignored++;
                error_service::record_document_error(get_string('handle413retryfailed', 'search_elastic', $doc['id']), $doc);
            }
        }

        unset($payloaddocs);

        return $retryignored;
    }

    /**
     * Retry indexing a large document with chunking.
     *
     * @param  array  $docdata [description]
     * @return bool
     */
    private function retry_with_chunking(array $docdata): bool {
        try {
            // Get chunking strategy and options.
            $strategy = manager::get_configured_strategy();
            $options = $this->get_chunking_options();

            // Check if content needs chunking.
            $contentchunks = [];
            if (!empty($docdata['content'])) {
                $contentchunks = $strategy->chunk($docdata['content'], $options);
            }

            $filetextchunks = [];
            if (!empty($docdata['filetext'])) {
                $filetextchunks = $strategy->chunk($docdata['filetext'], $options);
            }

            $needschunking = count($contentchunks) > 1 || count($filetextchunks) > 1;
            if (!$needschunking) {
                // Chunking didn't help - document is still one piece and too large.
                debugging("Document is too large but doesn't chunk into multiple pieces.");
                return false;
            }

            // Create and index chunks.
            $originaldocid = $docdata['id'];
            $totalchunks = max(count($contentchunks), count($filetextchunks));
            $successcount = 0;
            $failedchunks = [];

            for ($i = 0; $i < $totalchunks; $i++) {
                $chunknumber = $i + 1;

                try {
                    // Create chunk data.
                    $chunkdata = $this->create_chunk_data(
                        $docdata,
                        $chunknumber,
                        $totalchunks,
                        $contentchunks[$i] ?? null,
                        $filetextchunks[$i] ?? null,
                    );

                    // Index this chunk using existing method.
                    if ($this->index_single_document($chunkdata)) {
                        $successcount++;
                    } else {
                        $failedchunks[] = $chunknumber;
                    }
                } catch (Exception $e) {
                    $failedchunks[] = $chunknumber;
                    debugging("Exception creating chunk {$chunknumber}: {$e->getMessage()}");
                }
            }

            return $this->handle_chunk_results(
                $originaldocid,
                $docdata,
                $totalchunks,
                $successcount,
                $failedchunks
            );
        } catch (Exception $e) {
            debugging("Exception while chunking document {$docdata['id']} during 413 retry: " .
                $e->getMessage(), DEBUG_DEVELOPER);
            error_service::record_document_error("Chunking failed during 413 retry: {$e->getMessage()}", $docdata);
            return false;
        }
    }

    /**
     * Matches failed items with original documents and records error details.
     *
     * @param stdClass $responsebody Decoded JSON response from bulk operation.
     * @return int Number of documents that failed.
     */
    private function log_bulk_response_item_errors(stdClass $responsebody): int {
        $payloaddocs = $this->parse_payload_documents();
        $numdocsignored = 0;

        if (!isset($responsebody->items) || !is_array($responsebody->items)) {
            unset($payloaddocs);
            return $numdocsignored;
        }

        foreach ($responsebody->items as $responseindex => $item) {
            if (!isset($item->index->status) || $item->index->status < 300) {
                continue;
            }

            $errortype = $item->index->error->type ?? 'unknown';
            $errorreason = $item->index->error->reason ?? 'unknown';

            $message = get_string('addfail', 'search_elastic') .
                ' Error Type: ' . $errortype .
                ' Error Reason: ' . $errorreason;

            // Get corresponding document data using the same index.
            $docdata = null;
            if (isset($payloaddocs[$responseindex]) && is_array($payloaddocs[$responseindex])) {
                $candidatedoc = $payloaddocs[$responseindex];

                // Verify document ID matches to ensure we have the right document.
                $expectedid = $item->index->_id ?? null;
                $parsedid = $candidatedoc['id'] ?? null;

                if ($expectedid && $parsedid && $expectedid === $parsedid) {
                    $docdata = $candidatedoc;
                } else {
                    // Log mismatch for debugging but continue with error recording.
                    debugging('Document ID mismatch at index ' . $responseindex . ': expected ' .
                        $expectedid . ', got ' . $parsedid, DEBUG_DEVELOPER);
                }
            }

            error_service::record_document_error($message, $docdata);
            $numdocsignored++;
        }

        unset($payloaddocs);

        return $numdocsignored;
    }

    /**
     * Parse the payload to extract document data with contextid.
     *
     * @return array Array of document data indexed by position.
     */
    private function parse_payload_documents(): array {
        $documents = [];

        if (empty($this->payload)) {
            return $documents;
        }

        $lines = explode("\n", trim($this->payload));

        // Process pair of lines (metadata and document data).
        $docindex = 0;
        for ($i = 0; $i < count($lines); $i += 2) {
            // Always increment docindex for each attempted document pair,
            // regardless of whether lines exist or parsing succeeds.
            $documents[$docindex] = null;

            if (isset($lines[$i]) && isset($lines[$i + 1])) {
                $metadata = json_decode($lines[$i], true);
                $docdata = json_decode($lines[$i + 1], true);
                if ($metadata && $docdata) {
                    $documents[$docindex] = [
                        'metadata' => $metadata,
                        ...$docdata,
                    ];

                    // Add defaults for missing keys.
                    $documents[$docindex] += [
                        'id' => 'unknown',
                        'contextid' => \context_system::instance(),
                        'areaid' => 'unknown',
                        'itemid' => 0,
                        'modified' => null,
                    ];
                }
            }

            $docindex++;
        }

        return $documents;
    }


    /**
     * Index a single document.
     *
     * @param array $docdata
     * @return bool
     */
    public function index_single_document(array $docdata): bool {
        try {
            $url = $this->get_url();
            $luceneversion = $this->get_es_lucene_version();

            $docprefix = '_';
            if ($luceneversion < 8) {
                $docprefix = '';
            }

            $docurl = $url . '/' . $this->config->index . '/' . $docprefix . 'doc/' . $docdata['id'];
            $jsondoc = json_encode($docdata);

            $client = new \search_elastic\esrequest();
            $response = $client->post($docurl, $jsondoc);
            $responsecode = $response->getStatusCode();

            if ($responsecode !== 201 && $responsecode !== 200) {
                debugging('Failed to index file document: ' . $response->getBody(), DEBUG_DEVELOPER);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            debugging('Exception indexing file document: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Add a document to the index
     *
     * @param document $document
     * @param bool $fileindexing are we indexing files
     * @param integer $luceneversion Apache Lucene version to get the mapping for.
     * @return bool
     */
    public function add_document($document, $fileindexing = false, $luceneversion = 0) {
        $docdata = $document->export_for_engine();

        if (!$luceneversion) {
            $luceneversion = $this->get_es_lucene_version();
        }

        // Check if chunking is enabled.
        $chunkingenabled = (bool)$this->config->enablechunking;

        $needschunking = false;
        if ($chunkingenabled) {
            // Get chunking strategy and options.
            $strategy = manager::get_configured_strategy();
            $options = $this->get_chunking_options();

            // Check if chunking is needed for content.
            $contentchunks = [];
            if (!empty($docdata['content'])) {
                $contentchunks = $strategy->chunk($docdata['content'], $options);
            }

            // Determine if chunking occured.
            $needschunking = (count($contentchunks)) > 1;
        }

        if (!$needschunking) {
            $url = $this->get_url();
            $docprefix = '_';
            if ($luceneversion < 8) {
                $docprefix = '';
            }
            $docurl = $url . '/' . $this->config->index . '/' . $docprefix . 'doc/' . $docdata['id'];
            $jsondoc = json_encode($docdata);

            $client = new \search_elastic\esrequest();
            $response = $client->post($docurl, $jsondoc);
            $responsecode = $response->getStatusCode();

            if ($responsecode !== 201 && $responsecode !== 200) {
                $responsebody = json_decode($response->getBody());
                error_service::record_document_error(get_string('addfail', 'search_elastic') .
                        ' Error Type: ' . $responsebody->error->type .
                        ' Error Reason: ' . $responsebody->error->reason, $docdata);
                return false;
            }

            if ($fileindexing) {
                // This will take care of updating all attached files in the index.
                $this->process_document_files($document);
            }

            return true;
        }

        // Large document and chunking enabled - index as chunks.
        return $this->index_document_chunks($document, $docdata, $contentchunks, $fileindexing, $luceneversion);
    }

    /**
     * Index document as multiple chunks.
     *
     * @param \core_search\document $document The original document object.
     * @param array $docdata The original document data.
     * @param array $contentchunks Content chunks from strategy.
     * @param bool $fileindexing Whether to process the files.
     * @param int $luceneversion Lucene version
     * @return bool
     */
    private function index_document_chunks($document, $docdata, $contentchunks, $fileindexing, $luceneversion): bool {
        $originaldocid = $docdata['id'];
        $totalchunks = count($contentchunks);
        $successcount = 0;
        $failedchunks = [];

        for ($i = 0; $i < $totalchunks; $i++) {
            $chunknumber = $i + 1;

            try {
                // Create chunk data.
                $chunkdata = $this->create_chunk_data(
                    $docdata,
                    $chunknumber,
                    $totalchunks,
                    $contentchunks[$i] ?? null
                );

                // Index this chunk using existing method.
                if ($this->index_single_document($chunkdata)) {
                    $successcount++;
                } else {
                    $failedchunks[] = $chunknumber;
                }
            } catch (Exception $e) {
                $failedchunks[] = $chunknumber;
                debugging("Failed to index chunk {$chunknumber} of {$totalchunks} for document {$originaldocid}", DEBUG_DEVELOPER);
            }
        }

        // Process attached files.
        if ($fileindexing) {
            $this->process_document_files($document);
        }

        return $this->handle_chunk_results(
            $originaldocid,
            $docdata,
            $totalchunks,
            $successcount,
            $failedchunks
        );
    }

    /**
     * Create chunk metadata for a document chunk.
     *
     * @param array $docdata Original document data
     * @param int $chunknumber Current chunk number (index starts at 1)
     * @param int $totalchunks Total number of chunks
     * @param array|null $contentchunk Content chunk data (optional)
     * @param array|null $filetextchunk Filetext chunk data (optional)
     * @return array
     */
    private function create_chunk_data(
        array $docdata,
        int $chunknumber,
        int $totalchunks,
        ?array $contentchunk = null,
        ?array $filetextchunk = null
    ): array {
        $originaldocid = $docdata['id'];

        // Clone docdata to preserve original metadata.
        $chunkdata = $docdata;

        // Update ID with chunk suffix.
        $chunkdata['id'] = "{$originaldocid}_c{$chunknumber}";

        // Add chunk-specific fields.
        $chunkdata['original_id'] = $originaldocid;
        $chunkdata['chunk_number'] = $chunknumber;
        $chunkdata['chunk_total'] = $totalchunks;

        // Set chunked content.
        if ($contentchunk !== null && isset($contentchunk['text'])) {
            $chunkdata['content'] = $contentchunk['text'];
        } else {
            // Default to empty string, since the 'content' key is required in the document.
            $chunkdata['content'] = '';
        }

        // Set chunked filetext.
        if ($filetextchunk !== null && isset($filetextchunk['text'])) {
            $chunkdata['filetext'] = $filetextchunk['text'];
        }

        return $chunkdata;
    }

    /**
     * Handle chunk indexing results with threshold-based error logging.
     *
     * @param string $originaldocid
     * @param array $docdata
     * @param int $totalchunks
     * @param int $successcount
     * @param array $failedchunks
     * @return bool True if success ration >=50%, false otherwise
     */
    private function handle_chunk_results(
        string $originaldocid,
        array $docdata,
        int $totalchunks,
        int $successcount,
        array $failedchunks
    ): bool {

        $threshold = (int)$this->config->chunksuccessthreshold;
        if ($threshold < 0 || $threshold > 100) {
            debugging("Invalid chunk success threshold ({$threshold}). Using default 50%.");
            $threshold = 50;
        }

        // Calculate success percentage.
        $successpercentage = $totalchunks > 0 ? ($successcount / $totalchunks) * 100 : 0;

        if ($successcount === 0) {
            // TOTAL FAILURE - All chunks failed.
            $message = get_string('chunkingfailed_total', 'search_elastic', [
                'docid' => $originaldocid,
                'total' => $totalchunks,
            ]);
            error_service::record_document_error($message, $docdata);
            return false;
        } else if ($successpercentage < $threshold) {
            // Critical partial failure - less than 50% succeeded.
            $message = get_string('chunkingfailed_critical', 'search_elastic', [
                'docid' => $originaldocid,
                'success' => $successcount,
                'total' => $totalchunks,
                'percentage' => round($successpercentage),
                'threshold' => $threshold,
                'failed' => implode(', ', $failedchunks),
            ]);
            error_service::record_document_error($message, $docdata);
            return false;
        } else if ($successcount < $totalchunks) {
            // Above threshold but not 100%.
            $message = get_string('chunkingfailed_partial', 'search_elastic', [
                'docid' => $originaldocid,
                'success' => $successcount,
                'total' => $totalchunks,
                'percentage' => round($successpercentage),
                'threshold' => $threshold,
                'failed' => implode(', ', $failedchunks),
            ]);
            // Log as warning, not error since document is partially searchable.
            debugging($message, DEBUG_NORMAL);
        }

        // Success - All chunks indexed.
        return true;
    }

    /**
     * Compile the search result documents.
     *
     * @param \stdClass $results The raw search result documents.
     * @param int $limit The number of results to return.
     * @param array $seenitems Tracking map for deduplication across pagination.
     * @param array $seencounts Counter of unique documents seen so far.
     * @param array $deletiondocs Tracking map for deleted documents.
     * @return array $docs The found result documents.
     */
    private function compile_results($results, $limit, &$seenitems, &$seencounts, &$deletiondocs) {
        $docs = [];
        $doccount = 0;

        foreach ($results->hits->hits as $result) {
            $searcharea = $this->get_search_area($result->_source->areaid);
            if (!$searcharea) {
                continue;
            }

            // Get document ID for deduplication.
            $originalid = $result->_source->original_id ?? $result->_id;
            $access = $searcharea->check_access($result->_source->itemid);

            if ($access == \core_search\manager::ACCESS_DELETED) {
                // Queue for async deletion.
                if (!isset($deletiondocs[$originalid])) {
                    $deletiondocs[$originalid] = [
                        'docid' => $originalid,
                        'is_chunk' => isset($result->_source->original_id) && $result->_source->original_id !== $result->_id,
                        'chunk_id' => $result->_id, // ID that matched which could be a chunk ID.
                    ];
                }
            } else if ($access == \core_search\manager::ACCESS_GRANTED) {
                $itemkey = $result->_source->areaid . ':' . $result->_source->itemid;
                if (!array_key_exists($itemkey, $seenitems) && $doccount < $limit) {
                    // Add hightlighting to document.
                    $highlightedresult = $this->highlight_result($result);
                    $docs[] = $this->to_document($searcharea, (array)$highlightedresult->_source);
                    $seenitems[$itemkey] = true;
                    $doccount++;
                }

                if (!array_key_exists($itemkey, $seencounts)) {
                    $this->totalresultdocs++;
                    $seencounts[$itemkey] = true;
                }
            }

            // The search backend might have returned up to 10 times the results we need.
            // Therefore break out once we have all the results we need.
            if ($this->totalresultdocs >= \core_search\manager::MAX_RESULTS) {
                break;
            }
        }

        return $docs;
    }

    /**
     * Queue document deletions as an ad-hoc task.
     *
     * Batches multiple deletions into a single task.
     *
     * @param array $deletiondocs
     */
    private function queue_document_deletions(array $deletiondocs) {
        if (empty($deletiondocs)) {
            return;
        }

        // Create ad-hoc task.
        $task = new \search_elastic\task\delete_document_task();

        // Set custom data.
        $task->set_custom_data(['deletiondocs' => array_values($deletiondocs)]);

        // Queue the task.
        \core\task\manager::queue_adhoc_task($task);
    }

    /**
     * Takes the user supplied query as well as data from Moodle global
     * search core to construct the search query and execute the query
     * against the search engine.
     * Returns an array of matching result documents.
     *
     * @param array $filters
     * @param mixed $accessinfo Information about the contexts the user can access
     * @param int $limit
     * @return array $docs
     */
    public function execute_query($filters, $accessinfo, $limit = 0) {
        $docs = [];
        $docoffest = 0;
        $url = $this->get_url() . '/' .  $this->config->index . '/_search';
        $client = new \search_elastic\esrequest();

        $returnlimit = \core_search\manager::MAX_RESULTS;

        if ($limit == 0) {
            $limit = $returnlimit;
        }

        $seenitems = [];
        $seencounts = [];
        $deletiondocs = [];

        // We need to make multiple calls to the search backend if:
        // The number of results in $docs is less than $returnlimit
        // and the number of docs in the search backend is greater than \search_elastic\query::MAX_RESULTS.
        // This is to allow for the fact that lots of docs may be filtered Moodle side before being
        // returned to end user.
        do {
            // Construct query.
            $query = new \search_elastic\query($docoffest);
            $esquery = $query->get_query($filters, $accessinfo);
            $jsonquery = json_encode($esquery);
            $this->log($jsonquery);

            // Send a query to the search engine backend.
            $response = $client->post($url, $jsonquery);
            $jsonresults = $response->getBody();
            $responsecode = $response->getStatusCode();
            $this->log($jsonresults);

            $results = json_decode($jsonresults);
            if ($responsecode != 200) {
                // Something has gone wrong with getting the results from the backend.
                // Grab the reason for the first fail, and show a notification to the user.
                // This typically happens from a bad query.

                // Add contextual help for simple or complex query structure.
                if (get_config('search_elastic', 'usesimplequery')) {
                    $url = get_string('simplehelpurl', 'search_elastic');
                    $helplink = \html_writer::link($url, $url);
                    $helptext = \html_writer::tag('p', get_string('simplehelptext', 'search_elastic', $helplink));
                } else {
                    $url = get_string('complexhelpurl', 'search_elastic');
                    $helplink = \html_writer::link($url, $url);
                    $helptext = \html_writer::tag('p', get_string('complexhelptext', 'search_elastic', $helplink));
                }

                $msg = get_string('queryerror', 'search_elastic', [
                    'reason' => $results->error->root_cause[0]->reason,
                    'help' => $helptext,
                ]);

                \core\notification::error($msg);
                $results = [];
            }

            $totalhits = 0;

            // Iterate through results.
            if (isset($results->hits)) {
                if (is_object($results->hits->total)) {
                    $totalhits = $results->hits->total->value;
                } else {
                    $totalhits = $results->hits->total;
                }
                $docs = array_merge($docs, $this->compile_results($results, $limit, $seenitems, $seencounts, $deletiondocs));
                $docoffest += count($results->hits->hits);
            }
        } while ((count($docs) < $limit) && ($totalhits > \search_elastic\query::MAX_RESULTS) && ($docoffest < $totalhits));

        // Queue deleted documents for async deletion.
        if (!empty($deletiondocs)) {
            $this->queue_document_deletions($deletiondocs);
        }

        // TODO: handle negative cases and errors.
        return $docs;
    }

    /**
     * Deletes the specified document.
     *
     * @param string $id The document id to delete
     * @param integer $luceneversion Apache Lucene version to get the mapping for.
     * @return void
     */
    public function delete_by_id($id, $luceneversion = 0) {
        if (!$luceneversion) {
            $luceneversion = $this->get_es_lucene_version();
        }
        $docprefix = '_';
        if ($luceneversion < 8) {
            $docprefix = '';
        }
        $url = $this->get_url();
        $deleteurl = $url . '/' . $this->config->index . '/' . $docprefix . 'doc/' . $id;
        $client = new \search_elastic\esrequest();

        $client->delete($deleteurl);
    }

    /**
     * Manage deletion of content out of Elasticsearch.
     * If an $areaid is not passed this will delete EVERYTHING!
     *
     * @param bool $areaid | string
     */
    public function delete($areaid = false) {
        $url = $this->get_url();
        $indexeurl = $url . '/' . $this->config->index;
        $client = new \search_elastic\esrequest();
        $returnval = false;

        if ($areaid === false) {
            // Delete all your search engine index contents.
            // Response will return acknowledged True if deletion worked,
            // or a status of not found if index doesn't exist.
            // We'll treat both cases as good.
            $response = json_decode($client->delete($indexeurl)->getBody());
            if (isset($response->acknowledged) && ($response->acknowledged == true)) {
                $this->create_index(); // Recreate the new index.
                $returnval = true;
            } else if (isset($response->status) && ($response->status == 404)) {
                $this->create_index();
                $returnval = true;
            }
        } else {
            // Delete all documents in a specific area.
            $this->delete_by_area($areaid);
            $returnval = true;
        }
        return $returnval;
    }

    /**
     * Returns status of if this backend supports indexing of files
     * and if that support is available and enabled.
     *
     * @return bool
     */
    public function file_indexing_enabled() {
        $returnval = (bool)$this->config->fileindexing;

        return $returnval;
    }

    /**
     * The force merge operation allows to reduce the number of segments by merging
     * them and optimizes the index for faster search operations.
     *
     * This call will block until the merge is complete.
     * If the http connection is lost, the request will continue in the background,
     * and any new requests will block until the previous force merge is complete.
     *
     */
    public function optimize() {
        $url = $this->get_url() . '/' . $this->config->index . '/_forcemerge';
        $client = new \search_elastic\esrequest();

        $client->post($url, '');
    }

    /**
     * Elastic includes group support in the execute_query function.
     *
     * @return bool True
     */
    public function supports_group_filtering() {
        return true;
    }

    /**
     * Requests the search engine to upgrade the schema. TJust return true for Elasticsearch.
     *
     * @param int $oldversion Old schema version
     * @param int $newversion New schema version
     * @return bool|string True if schema is updated successfully, a string if it needs updating manually
     */
    protected function update_schema($oldversion, $newversion) {
        return true;
    }

    /**
     * Elastic supports sort by location within course contexts or below.
     *
     * @param \context $context Context that the user requested search from
     * @return array Array from order name => display text
     */
    public function get_supported_orders(\context $context) {
        $orders = parent::get_supported_orders($context);
        $orders['desc'] = get_string('order_newest', 'search_elastic');
        $orders['asc'] = get_string('order_oldest', 'search_elastic');

        // If not within a course, no other kind of sorting supported.
        $coursecontext = $context->get_course_context(false);
        if ($coursecontext) {
            // Within a course or activity/block, support sort by location.
            $orders['location'] = get_string(
                'order_location',
                'search',
                $context->get_context_name()
            );
        }
        return $orders;
    }

    /**
     * Elastic supports search by user id.
     *
     * @return bool True
     */
    public function supports_users() {
        return true;
    }
}
