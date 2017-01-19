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

$string['fileindexing'] = 'Enable file indexing';
$string['fileindexing_help'] = 'If your Elasticsearch install supports it, this feature allows Moodle to send files to be indexed.';
$string['fileindexsettings'] = 'File indexing settings';
$string['fileindexsettings_desc'] = 'Configure file indexing for the Elasticsearch search engine';
$string['hostname'] = 'Hostname';
$string['hostname_desc'] = 'The FQDN of the Elasticsearch engine endpoint';
$string['index'] = 'Index';
$string['index_desc'] = 'Namespace index to store search data in backend';
$string['indexfail'] = 'Failed to create index';
$string['noconfig'] = 'Elasticsearch configuration missing';
$string['noserver'] = 'Elasticsearch endpoint unreachable';
$string['port'] = 'Port';
$string['port_desc'] = 'The Port of the Elasticsearch engine endpoint';
$string['searchinfo'] = 'Search queries';
$string['searchinfo_help'] = 'The field to be searched may be specified by prefixing the search query with \'title:\', \'content:\', \'name:\', or \'intro:\'. For example, searching for \'title:news\' would return results with the word \'news\' in the title.

Boolean operators (\'AND\', \'OR\') may be used to combine or exclude keywords.

Wildcard characters (\'*\' or \'?\' ) may be used to represent characters in the search query.';
$string['tikahostname'] = 'Tika Hostname';
$string['tikahostname_desc'] = 'The FQDN of the Apache Tika endpoint';
$string['tikaport'] = 'Tika Port';
$string['tikaport_desc'] = 'The Port of the Apache Tika endpoint';
