[![Build Status](https://travis-ci.org/catalyst/moodle-search_elastic.svg?branch=master)](https://travis-ci.org/catalyst/moodle-search_elastic)

# Moodle Global Search - Elasticsearch Backend

This plugin allows Moodle to use Elasticsearch as the search engine for Moodle's Global Search.

## Installation
**NOTE:** Complete all of these steps before trying to enable the Global Search functionality in Moodle.

1. Get the code and copy/ install it to: `<moodledir>/search/engine/elastic`
2. Run the upgrade: `sudo -u www-run php admin/cli/upgrade`
3. Set up the plugin in *Site administration > Plugins > Search > Manage global search* by selecting *elastic* as the search engine.
4. Configure the Elasticsearch plugin at: *Site administration > Plugins > Search > Elastic*
    4.1 Set *hostname* and *port* of your Elasticsearch server
5. To create the index and populate Elasticsearch with your site's data, run this CLI script. `sudo -u www-run php search/cli/indexer.php --force`
6. Enable Global search in *Site administration > Advanced features*

## Elasticsearch Version Support
Currently this plugin is tested to work against the following versions of Elasticsearch:

* 2.3.4
* 2.4.4
* 5.1.2

## File Indexing Support
File indexing is currently not supported by this plugin.

## Test Setup
In order to run the PHP Unit tests for this plugin you need to setup and configure an Elasticsearch instance as will as supply the instance details to Moodle.
You need to define:

* Hostname: the name URL of the host of your Elasticsearch Instance
* Port: The TCP port the host is listening on
* Index: The name of the index to use during tests. **NOTE:** Make sure this is different from your production index!

### Setup via config.php
To define the required variables in via your Moodle configuration file, add the following to `config.php`:
<pre><code>
define('TEST_SEARCH_ELASTIC_HOSTNAME', 'http://127.0.0.1');
define('TEST_SEARCH_ELASTIC_PORT', 9200);
define('TEST_SEARCH_ELASTIC_INDEX', 'moodle_test_2');
</pre></code>

### Setup via Environment variables
The required Elasticserach instance configuration variables can also be provided as environment variables. To do this at the Linux command line:
`export TEST_SEARCH_ELASTIC_HOSTNAME=http://127.0.0.1; export TEST_SEARCH_ELASTIC_PORT=9200; export TEST_SEARCH_ELASTIC_INDEX=moodle_test`
