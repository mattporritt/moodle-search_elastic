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

$string['pluginname'] = 'Elastic';
$string['pluginname_desc'] = 'Search backend for the Elasticsearch search engine';

$string['addfail'] = 'Failed to add document to index';
$string['fileindexing'] = 'Enable file indexing';
$string['fileindexing_help'] = 'Enables file indexing for this plugin. With this option checked you will need to enter details of the Tika service in the "File indexing settings" below.';
$string['fileindexsettings'] = 'File indexing settings';
$string['fileindexsettings_desc'] = 'Enter the details for the Tika service. These are required if file indexing is enabled above.';
$string['hostname'] = 'Hostname';
$string['hostname_desc'] = 'The FQDN of the Elasticsearch engine endpoint';
$string['index'] = 'Index';
$string['index_desc'] = 'Namespace index to store search data in backend';
$string['imageindex'] = 'Enable image indexing';
$string['imageindex_desc'] = 'When enabled Moodle will use AWS Rekognition to index images. If enabled File indexing must also be enabled and configured above.';
$string['indexfail'] = 'Failed to create index';
$string['maxlabels'] = 'Maxiumum Labels';
$string['maxlabels_desc'] = 'The maximum number of result labels returned by Rekognition.';
$string['minconfidence'] = 'Minimum confidence';
$string['minconfidence_desc'] = 'Reckognition will only return labels with a confidence above this';
$string['noconfig'] = 'Elasticsearch configuration missing';
$string['noserver'] = 'Elasticsearch endpoint unreachable';
$string['port'] = 'Port';
$string['port_desc'] = 'The Port of the Elasticsearch engine endpoint';
$string['region'] = 'Region';
$string['region_desc'] = 'The AWS region the Elasticsearch instance is in, e.g. ap-southeast-2';
$string['rekregion'] = 'Region';
$string['rekregion_desc'] = 'The AWS region the Rekognition service is in, e.g. us-west-2';
$string['reksigningkeyid'] = 'Key ID';
$string['reksigningkeyid_desc'] = 'The ID of the key to use to access Rekcognition.';
$string['reksigningsecretkey'] = 'Secret Key';
$string['reksigningsecretkey_desc'] = 'The secret key to use to access Rekcognition.';
$string['rekognitionsettings'] = 'AWS Rekognition settings';
$string['rekognitionsettings_desc'] = 'Settings to configure image recognition and indexing using the AWS Rekognition service.';
$string['searchinfo'] = 'Search queries';
$string['searchinfo_help'] = 'The field to be searched may be specified by prefixing the search query with \'title:\', \'content:\', \'name:\', or \'intro:\'. For example, searching for \'title:news\' would return results with the word \'news\' in the title.

Boolean operators (\'AND\', \'OR\') may be used to combine or exclude keywords.

Wildcard characters (\'*\' or \'?\' ) may be used to represent characters in the search query.';
$string['sendsize'] = 'Request size';
$string['sendsize_desc'] = 'Some Elasticsearch providers such as AWS have a limit on how big the HTTP payload can be. Therefore we limit it to a size in bytes.';
$string['signing'] = 'Enable request signing';
$string['signing_desc'] = 'When enabled Moodle will sign each request to Elasticsearch with the credentials below';
$string['signingkeyid'] = 'Key ID';
$string['signingkeyid_desc'] = 'The ID of the key to use for signing requests.';
$string['signingsecretkey'] = 'Secret Key';
$string['signingsecretkey_desc'] = 'The secret key to use to sign requests.';
$string['signingsettings'] = 'Request signing settings';
$string['signingsettings_desc'] = 'If your Elasticsearch setup uses Request Signing enable and configure it below.

This generally only applies if you are using Amazon Web Service (AWS) to provide your Elasticsearch Endpoint';
$string['tikahostname'] = 'Tika Hostname';
$string['tikahostname_desc'] = 'The FQDN of the Apache Tika endpoint';
$string['tikaport'] = 'Tika Port';
$string['tikaport_desc'] = 'The Port of the Apache Tika endpoint';
$string['tikasendsize'] = 'Maximum file size';
$string['tikasendsize_desc'] = 'Sending large files to Tika can cause out of memory issues. Therefore we limit it to a size in bytes.';
