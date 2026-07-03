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
 * Strings for component 'search_elastic'.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['actions'] = 'Actions';
$string['addfail'] = 'Failed to add document to index.';
$string['adminsettings'] = 'Plugin settings';
$string['advsettings'] = 'Advanced settings';
$string['all'] = 'All';
$string['apikey'] = 'API key';
$string['apikey_help'] = 'Some Elasticsearch providers such as  Elastic Cloud require API key to authorize HTTP requests.';
$string['areaid'] = 'Area ID';
$string['aws'] = 'AWS';
$string['basicsettings'] = 'Basic settings';
$string['boostdescription'] = 'These settings control the boosting settings for the search areas. Areas with higher values will be boosted more and will appear higher in the search results. A Boost value of 20 will make the area weighted 2x as high as an area with value 10.';
$string['boostsettings'] = 'Boosting settings';
$string['boostvalue'] = '';
$string['boostvalue_help'] = 'Set the value you want this search area to be boosted by in the search results. Higher boost values give more priority.';
$string['checkchunking_status'] = 'Chunking status';
$string['checkserver_ready_check'] = 'Elastic server connection';
$string['chunking:na'] = 'Document chunking is not enabled';
$string['chunkingfailed_critical'] = 'Critical failure indexing document {$a->docid}: Only {$a->success} of {$a->total} chunks indexed successfully ({$a->percentage}% success, threshold: {$a->threshold}%). Failed chunk number(s): {$a->failed}. Document is not reliably searchable.';
$string['chunkingfailed_partial'] = 'Partial failure indexing document {$a->docid}: {$a->success} of {$a->total} chunks indexed successfully ({$a->percentage}% success, threshold: {$a->threshold}%). Failed chunk number(s): {$a->failed}. Document is partially searchable but some content may be missing from search results';
$string['chunkingfailed_total'] = 'Failed to index document {$a->docid}: All {$a->total} chunks failed to index.';
$string['chunkingrelatederrorsfound'] = 'Found {$a} chunking related errors.';
$string['chunkingsettings'] = 'Document chunking settings';
$string['chunkingstrategy'] = 'Chunking Strategy';
$string['chunkingstrategy_desc'] = 'Method used to split large documents into chunks.';
$string['chunkonverlaptoolarge'] = 'Chunk overlap ({$a->overlap} words) is too large for the configured chunk size ({$a->maxsize} bytes, ~{$a->maxreasonable} words max). Overlap should be less than 50% of chunk size. Please reduce overlap to {$a->maxreasonable} words or less.';
$string['chunksuccessthreshold'] = 'Chunk success threshold percentage';
$string['chunksuccessthreshold_desc'] = 'Minimum percentage of chunks that must be successfully indexed for a document to be considered successfully indexed.

Example with 50% threshold and 10 chunks:
<br>
✓ Success: 7 chunks indexed (any 7 out of 10) = 70%<br>
✓ Success: 5 chunks indexed (any 5 out of 10) = 50%<br>
✗ Failure: 4 chunks indexed (any 4 out of 10) = 40%<br>

The chunk order does not matter. The threshold is based on total count, not which specific chunks succeed.';
$string['clearsearch'] = 'Clear Search';
$string['clearsearchdesc'] = 'Remove all search filters';
$string['complexhelptext'] = 'The field to be searched may be specified by prefixing the search query with \'title:\', \'content:\', \'name:\', or \'intro:\'. For example, searching for \'title:news\' would return results with the word \'news\' in the title.
<br>
Boolean operators (\'AND\', \'OR\') may be used to combine or exclude keywords.
<br>
Wildcard characters (\'*\' or \'?\' ) may be used to represent characters in the search query.
<br>
For more information, follow this link: {$a}';
$string['complexhelpurl'] = 'https://lucene.apache.org/core/2_9_4/queryparsersyntax.html';
$string['confirm:delete'] = 'Are you sure you want to delete this record?';
$string['confirm:removeallobsolete'] = 'Are you sure you want to delete all obsolete errors? This action cannot be undone.';
$string['confirm:retryallfailed'] = 'Are you sure you want to retry all errors? This will queue a background task to reprocess the failed documents.';
$string['confirm:retrysingle'] = 'Are you sure you want to retry indexing this record?';
$string['connection:na'] = 'Server not configured';
$string['connection:status'] = 'The configured elastic server at {$a->url} returned the status code {$a->status}';
$string['connectiontest'] = 'Server connection test';
$string['connecttimeout'] = 'Connect timeout (seconds)';
$string['connecttimeout_help'] = 'Guzzle connection timeout. Use 0 to disable. See https://docs.guzzlephp.org/en/stable/request-options.html#connect-timeout';
$string['contentmodified'] = 'Content modified';
$string['delete'] = 'Delete';
$string['deletedobsoleteerrors'] = 'Successfully deleted {$a} obsolete errors';
$string['deletedsuccessfully'] = 'Error ID {$a} has been deleted successfully.';
$string['deleteobsoleteexception'] = 'Exception occured while deleting obsolete errors.';
$string['documentid'] = 'Document ID';
$string['enablechunking'] = 'Enable document chunking';
$string['enablechunking_desc'] = 'Automatically split large documents into smaller chunks to avoid Elasticsearch payload size limits.

Platform limits:
<ul>
<li>AWS OpenSearch: 10-100 MB depending on the instance type (see https://docs.aws.amazon.com/opensearch-service/latest/developerguide/limits.html#network-limits)</li>
<li>Self-hosted Elasticsearch: 100 MB by default (configurable via http.max_content_length)</li>
</ul>

Without chunking:
<ul>
<li>Documents exceeding your platform limit fail with "413 Request Entity Too Large"</li>
<li>Documents that failed to index are not searchable</li>
<li>Errors logged but indexing continues for other documents</li>
</ul>

Enable this if you have large files (PDFs, presentations, data files) that need to be searchable.';
$string['enrichdesc'] = 'Global Search can enrich the indexed data used in search by extracting text and other data from files.
The data extracted from files in Moodle is controlled by the following groups of settings.';
$string['enrichsettings'] = 'Data enrichment settings';
$string['erroridnotfound'] = 'Unable to find error ID: {$a}';
$string['errormessage'] = 'Error message';
$string['errortype'] = 'Error type';
$string['exceededmaxretries'] = 'Maximum retries exceeded';
$string['fileindexing'] = 'Enable file indexing';
$string['fileindexing_help'] = 'Enables file indexing for this plugin.';
$string['fileindexselect'] = 'File processor';
$string['fileindexselect_help'] = 'Select the file processor or service that will extract text from files. The form will update with the settings for the chosen service.';
$string['fileindexsettings'] = 'File indexing';
$string['fileindexsettings_help'] = 'Enter the details for the Tika service. These are required if file indexing is enabled above.';
$string['fileindexsettingsdesc'] = 'Before files can be processed and their content and information extracted file indexing for Global Search needs to be enabled.';
$string['fixedsizestrategy'] = 'Fixed size';
$string['fs_chunkmaxsize'] = 'Maximum chunk size (bytes)';
$string['fs_chunkmaxsize_desc'] = 'Maximum size of each chunk in bytes.';
$string['fs_chunkoverlapwords'] = 'Chunk overlap (words)';
$string['fs_chunkoverlapwords_desc'] = 'Number of words to overlap between chunks to preserve context across boundaries. The last N words of each chunk are repeated at the start of the next chunk.<br><br>
Example with 10 words overlap:
<br><br>
Chunk 1: <em>"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, <br> when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, <br> remaining essentially unchanged. It was popularised in the 1960s with"</em>
<br><br>
Chunk 2 starts: <em>"remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, <br> and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."</em>';
$string['fs_maxchunk'] = 'Max chunk';
$string['fs_maxchunk_desc'] = 'Max chunk desc';
$string['handle413retrychunkingdisabled'] = 'Failed to add document to index. Document too large ({$a->docsize} bytes exceeds {$a->maxsize} bytes limit). Enable chunking in plugin settings to index large documents. Doc ID: {$a->docid}';
$string['handle413retryfailed'] = 'Failed to add document to index. Failed on individual retry after 413. Doc ID: ({$a})';
$string['handle413retryfailedchunking'] = 'Failed to add document to index. Document too large ({$a->docsize} bytes exceeds {$a->maxsize} bytes limit) and chunking failed. Doc ID: {$a->docid}';
$string['hostname'] = 'Hostname';
$string['hostname_help'] = 'The FQDN of the Elasticsearch engine endpoint';
$string['imageindexselect'] = 'Image processor';
$string['imageindexselect_help'] = 'Select the image processor or service that will extract information out of your images. The form will update with the settings for the chosen service.';
$string['imagerecognitionsettings'] = 'Image recognition';
$string['imagerecognitionsettingsdesc'] = 'Image recognition extracts details about the content of an image and adds these to the search index.

These settings control what process or service is used to extract data out of an image and how the image data is added to the search engine.';
$string['index'] = 'Index';
$string['index_help'] = 'Namespace index to store search data in backend';
$string['indexfail'] = 'Failed to create index';
$string['indexingerrors'] = 'Indexing errors';
$string['invaliderrorid'] = 'Invalid error ID';
$string['logging'] = 'Query logging';
$string['logging_help'] = 'If enabled all search queries and the raw results from Elasticsearch will be added to the error log';
$string['maxlabels'] = 'Maxiumum Labels';
$string['maxlabels_help'] = 'The maximum number of result labels returned by Rekognition.';
$string['minconfidence'] = 'Minimum confidence';
$string['minconfidence_help'] = 'Reckognition will only return labels with a confidence above this';
$string['noconfig'] = 'Elasticsearch configuration missing';
$string['noerrors'] = 'No errors found.';
$string['none'] = 'None';
$string['norecentchunkingerrors'] = 'No recent chunking errors';
$string['noserver'] = 'Elasticsearch endpoint unreachable';
$string['nostrategies'] = 'No available chunking strategy found.';
$string['order_newest'] = 'Newest first';
$string['order_oldest'] = 'Oldest first';
$string['parentid'] = 'Parent ID';
$string['pluginname'] = 'Elastic';
$string['pluginname_help'] = 'Search backend for the Elasticsearch search engine';

$string['pluginsettings'] = 'Plugin Settings';
$string['port'] = 'Port';
$string['port_help'] = 'The Port of the Elasticsearch engine endpoint';
$string['privacy:metadata'] = 'This plugin sends data externally to a linked Elasticsearch search engine. It does not store data locally.';
$string['privacy:metadata:data'] = 'Personal data passed through from the search subsystem.';
$string['queryerror'] = 'Error executing query in search engine: {$a->reason}

{$a->help}';
$string['reachable'] = 'Elasticsearch endpoint reachable';
$string['region'] = 'Region';
$string['region_help'] = 'The AWS region the Elasticsearch instance is in, e.g. ap-southeast-2';
$string['rekkeyid'] = 'Key ID';
$string['rekkeyid_help'] = 'The ID of the key to use to access Rekcognition.';
$string['rekognitionsettings'] = 'AWS Rekognition settings';
$string['rekognitionsettings_help'] = 'Settings to configure image recognition and indexing using the AWS Rekognition service.';
$string['rekregion'] = 'Region';
$string['rekregion_help'] = 'The AWS region the Rekognition service is in, e.g. us-west-2';
$string['reksecretkey'] = 'Secret Key';
$string['reksecretkey_help'] = 'The secret key to use to access Rekcognition.';
$string['removeallobsolete'] = 'Remove all obsolete';
$string['removeallobsolete_desc'] = 'Delete all error records that have obsolete status from database.';
$string['retry'] = 'Retry';
$string['retryallfailed'] = 'Retry all failed';
$string['retryallfailed_desc'] = 'Queue background task to reattempt indexing for failed documents.';
$string['retryallqueued'] = 'Background task queued to retry all errors.';
$string['retrycount'] = 'Retry count';
$string['retryerrorstask'] = 'Retry Elasticsearch errors';
$string['retrysingleadhoc'] = 'Background task queued to retry the error.';
$string['searcharea'] = 'Search area';
$string['searchareanotfound'] = 'Search area not found';
$string['searchdocumentid'] = 'Document ID';
$string['searchdocumentidplaceholder'] = 'Enter document ID to search...';
$string['searcherrors'] = 'Search errors';
$string['searchinfo'] = 'Search queries';
$string['searchinfo_help'] = 'The field to be searched may be specified by prefixing the search query with \'title:\', \'content:\', \'name:\', or \'intro:\'. For example, searching for \'title:news\' would return results with the word \'news\' in the title.

Boolean operators (\'AND\', \'OR\') may be used to combine or exclude keywords.

Wildcard characters (\'*\' or \'?\' ) may be used to represent characters in the search query.';
$string['searchmessage'] = 'Error message';
$string['searchmessageplaceholder'] = 'Search in error messages...';
$string['searchresultsinfo'] = 'Showing {$a->filtered} of {$a->total} total errors';
$string['searchsettings'] = 'Search settings';
$string['sendsize'] = 'Request size';
$string['sendsize_help'] = 'Some Elasticsearch providers such as AWS have a limit on how big the HTTP payload can be. Therefore we limit it to a size in bytes.';
$string['signing'] = 'Enable request signing';
$string['signing_help'] = 'When enabled Moodle will sign each request to Elasticsearch with the credentials below';
$string['signingkeyid'] = 'Key ID';
$string['signingkeyid_help'] = 'The ID of the key to use for signing requests.';
$string['signingsecretkey'] = 'Secret Key';
$string['signingsecretkey_help'] = 'The secret key to use to sign requests.';
$string['signingsettings'] = 'Request signing settings (compatible with AWS)';
$string['signingsettings_help'] = 'If your Elasticsearch setup uses Request Signing enable and configure it below.

This generally only applies if you are using Amazon Web Service (AWS) to provide your Elasticsearch Endpoint';
$string['simplehelptext'] = 'Boolean operators (\'+\' for And, \'|\' for Or) may be used to combine or exclude keywords.
<br>
Wildcard characters (\'*\') may be used to represent characters in the search query.
<br>
Quotes (" ") may be used to specify a phrase.
<br>
Minus (\'-\') may be used to negate search terms.
<br>
For more information, follow this link: {$a}';
$string['simplehelpurl'] = 'https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-simple-query-string-query.html';
$string['status'] = 'Status';
$string['status_failed'] = 'Failed';
$string['status_obsolete'] = 'Obsolete';
$string['status_retrying'] = 'Retrying';
$string['textextractionsettings'] = 'Text extraction';
$string['textextractionsettingsdesc'] = 'Text extraction takes the actual text contained in a file and adds it as searchable content to the search index.';
$string['tika'] = 'Apache Tika';
$string['tikahostname'] = 'Tika Hostname';
$string['tikahostname_help'] = 'The FQDN of the Apache Tika endpoint';
$string['tikaport'] = 'Tika Port';
$string['tikaport_help'] = 'The Port of the Apache Tika endpoint';
$string['tikasendsize'] = 'Maximum file size';
$string['tikasendsize_help'] = 'Sending large files to Tika can cause out of memory issues. Therefore we limit it to a size in bytes.';
$string['timecreated'] = 'Created';
$string['timemodified'] = 'Timemodified';
$string['timeout'] = 'Request timeout';
$string['timeout_desc'] = 'Maximum time in seconds to wait for HTTP requests to complete. Defaults to 0 to wait indefinitely (the default behavior).';
$string['type_chunking'] = 'Chunking';
$string['type_indexing'] = 'Indexing';
$string['type_tika'] = 'Tika';
$string['usesimplequery'] = 'Use simple query';
$string['usesimplequery_help'] = 'Simple queries reduce the amount of operators usable, but allow for partial returns on malformed queries.

Standard syntax information: {$a->complex}

Simple syntax information: {$a->simple}';
$string['wildcardend'] = 'Wildcard at the end';
$string['wildcardend_help'] = 'When enabled Moodle will add implicit wildcards at the end of search terms. This can improve behaviour of searches.
For example: searching for "math" will become "math*" prior to be sent to the search engine. This means the search will now match "math", "maths" and "mathematics".';
$string['wildcardstart'] = 'Wildcard at the start';
$string['wildcardstart_help'] = 'When enabled Moodle will add implicit wildcards at the start of search terms. This can improve behaviour of searches.
For example: searching for "scrip" will become "*scrip" prior to be sent to the search engine. This means the search will now match "script" and "description".';
