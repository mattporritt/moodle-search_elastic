[![Build Status](https://travis-ci.org/catalyst/moodle-search_elastic.svg?branch=master)](https://travis-ci.org/catalyst/moodle-search_elastic)

# Moodle Global Search - Elasticsearch Backend

This plugin allows Moodle to use Elasticsearch as the search engine for Moodle's Global Search.

The following features are provided by this plugin:

* Multiple versions of Elasticsearch
* File indexing
* Request signing, compatible with Amazon Web Services (AWS)
* Respects Moodle Proxy settings
* Image recognition and indexing

## Supported Moodle Versions
This plugin currently supports Moodle:

* 3.1
* 3.2
* 3.3

## Installation
**NOTE:** Complete all of these steps before trying to enable the Global Search functionality in Moodle.

1. Get the code and copy/ install it to: `<moodledir>/search/engine/elastic`
2. This plugin also depends on *local_aws* get the code from `https://github.com/catalyst/moodle-local_aws` and copy/ install it into `<moodledir>/local/aws`
3. Run the upgrade: `sudo -u www-data php admin/cli/upgrade` **Note:** the user may be different to www-data on your system.
4. Set up the plugin in *Site administration > Plugins > Search > Manage global search* by selecting *elastic* as the search engine.
5. Configure the Elasticsearch plugin at: *Site administration > Plugins > Search > Elastic*
6. Set *hostname* and *port* of your Elasticsearch server
7. Optionally, change the *Request size* variable. Generally this can be left as is. Some Elasticsearch providers such as AWS have a limit on how big the HTTP payload can be. Therefore we limit it to a size in bytes.
8. To create the index and populate Elasticsearch with your site's data, run this CLI script. `sudo -u www-data php search/cli/indexer.php --force`
9. Enable Global search in *Site administration > Advanced features*

## Elasticsearch Version Support
Currently this plugin is tested to work against the following versions of Elasticsearch:

* 2.3.4
* 2.4.4
* 5.1.2
* 5.5.0

## Elasticsearch Setup
The following is the bare minimum to get Elasticsearch working in a Debian/Ubuntu Operating System environment. Consult the [Elasticsearch Documention](https://www.elastic.co/downloads/elasticsearch) for in depth instructions, or for details on how to install on other operating systems.

NOTE: The instructions below should only be used for test and dev purposes. Don't do this in production.

Elasticsearch requires Java as a prerequisite, to install Java:
<pre><code>
sudo apt-get install default-jre default-jdk
</pre></code>

Once Java is installed, the following commands will install and start Elasticsearch.
<pre><code>
wget https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-5.1.2.deb
sudo dpkg -i elasticsearch-5.1.2.deb
sudo update-rc.d elasticsearch defaults
sudo service elasticsearch start
</pre></code>

A quick test can be performed by running the following from the command line.
<pre><code>
curl -X GET 'http://localhost:9200'
</pre></code>

The output should look something like:
<pre><code>
{
  "name" : "1QHLiux",
  "cluster_name" : "elasticsearch",
  "cluster_uuid" : "mLRqIsnVRrGdgg2OfHWNrg",
  "version" : {
    "number" : "5.1.2",
    "build_hash" : "c8c4c16",
    "build_date" : "2017-01-11T20:18:39.146Z",
    "build_snapshot" : false,
    "lucene_version" : "6.3.0"
  },
  "tagline" : "You Know, for Search"
}

</pre></code>

## File Indexing Support
This plugin uses [Apache Tika](https://tika.apache.org/) for file indexing support. Tika parses files, extracts the text, and return it via a REST API.

### Tika Setup
Seting up a Tika test service is straight forward. In most cases on a Linux environment, you can simply download the Java JAR then run the service.
<pre><code>
wget http://apache.mirror.amaze.com.au/tika/tika-server-1.16.jar
java -jar tika-server-1.16.jar
</code></pre>

This will start Tika on the host. By default the Tika service is available on: `http://localhost:9998`

### Enabling File indexing support in Moodle
Once a Tika service is available the Elasticsearch plugin in Moodle needs to be configured for file indexing support.<br/>
Assuming you have already followed the basic installation steps, to enable file indexing support:

1. Configure the Elasticsearch plugin at: *Site administration > Plugins > Search > Elastic*
2. Select the *Enable file indexing* checkbox.
3. Set *Tika hostname* and *Tika port* of your Tika service. If you followed the basic Tika setup instructions the defaults should not need changing.
4. Click the *Save Changes* button.

### What is Tika
From the [Apache Tika](https://tika.apache.org/) website:
<blockquote>
The Apache Tika™ toolkit detects and extracts metadata and text from over a thousand different file types (such as PPT, XLS, and PDF). All of these file types can be parsed through a single interface, making Tika useful for search engine indexing, content analysis, translation, and much more. You can find the latest release on the download page. Please see the Getting Started page for more information on how to start using Tika.
</blockquote>

### Why use Tika as a stand alone service?
It is common to see Elasticsearch implementations using an Elasticsearch file indexing plugin rather than a stand alone service. Current Elasticsearch plugins are a wrapper around Tika. (The Solr search engine also uses Tika).<br/>
Using Tika as a standalone service has the following advantages:

* Can support file indexing for Elasticsearch setups that don't support file indexing plugins such as AWS.
* No need to chagne setup or plugins based on Elasticsearch version.
* You can share one Tika service across multiple Elasticsearch clusters.
* Can run Tika on dedicated infrastructure that is not part of your search nodes.

## Image Recognition and Indexing
This plugin can use the Amazon Web Services (AWS) [Rekognition service(https://aws.amazon.com/rekognition/) to identify the contents of images. The identified content is then indexed by Elasticsearch and can be searched for in Moodle (cool huh?).

**NOTE:** Indexing of files by Moodle's core Global Search is currently limited to only indexing files from a couple of places. Tracker issue [MDL-59459](https://tracker.moodle.org/browse/MDL-59459) has been raised to increase the coverage of the files indexed by Global Search.

Currently the best resource to use to test image search functionality it so add an image via the Moodle course file resource.

### Enabling image recognition and indexing support in Moodle
Once you have setup Elasticsearch in AWS Moodle needs to be configured for Image Recognition.<br/>
Assuming you have already followed the basic installation steps and the file indexing steps, to enable Image Recognition:

1. Configure the Elasticsearch plugin at: *Site administration > Plugins > Search > Elastic*
2. Select the *Enable image signing* checkbox.
3. Set *Key ID*, *Secret Key* and *Region* of your AWS credentials and Rekognition region.
4. Click the *Save Changes* button.

**NOTE:** You will need a set of AWS API keys for an AWS IAM user with full Rekognition permissions. Setting this up is beyond the scope of this README. for further information see the [AWS Documentation](https://aws.amazon.com/rekognition/getting-started/).

## Request Signing
Amazon Web Services (AWS) provide Elasticsearch as a managed service. This makes it easy to provision and manage and Elasticsearch cluster.<br/>
One of the ways you can secure access to your data in Elasticsearch when using AWS is to use request signing. [Request signing](http://docs.aws.amazon.com/general/latest/gr/signing_aws_api_requests.html) allows only valid signed requests to be accepted by the Elasticsearch endpoint. Requests that are unsigned are not authorised to access the endpoint.

### Enabling Request Signing support in Moodle
Once you have setup Elasticsearch in AWS Moodle needs to be configured for Request Signing.<br/>
Assuming you have already followed the basic installation steps, to enable Request Signing:

1. Configure the Elasticsearch plugin at: *Site administration > Plugins > Search > Elastic*
2. Select the *Enable request signing* checkbox.
3. Set *Key ID*, *Secret Key* and *Region* of your AWS credentials and Elasticsearch region.
4. Click the *Save Changes* button.

## Webservices
This plugin exposes two AJAX enabled webservices, to allow you to integrate Moodle's Global search with other systems and services. The two available webservices are:

* *search_elastic_search* - Returns search results based on provided search query.
* *search_elastic_search_areas* - Returns the search area IDs for each available search area in Moodle.

Setup and documentation of these services is connsistent with other Moodle core web services.

This plugin sets up a pre-configured *External service* called *Search service* when the plugin is installed. This service adds and enables the two webservice methods provided by this plugin.

**NOTE:** You will need to have Global search and this plugin enabled and configured correctly before you can use the provided web services.

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
<pre><code>
export TEST_SEARCH_ELASTIC_HOSTNAME=http://127.0.0.1; export TEST_SEARCH_ELASTIC_PORT=9200; export TEST_SEARCH_ELASTIC_INDEX=moodle_test
</pre></code>

### Running the tests
First initialise the test environment, from the Moodle code home directory: `php admin/tool/phpunit/cli/init.php`
To run only this plugins tests: `vendor/bin/phpunit search_elastic_engine_testcase search/engine/elastic/tests/engine_test.php`

# Crafted by Catalyst IT


This plugin was developed by Catalyst IT Australia:

https://www.catalyst-au.net/

![Catalyst IT](/pix/catalyst-logo.png?raw=true)


# Contributing and Support

Issues, and pull requests using github are welcome and encouraged! 

https://github.com/catalyst/moodle-search_elastic/issues

If you would like commercial support or would like to sponsor additional improvements
to this plugin please contact us:

https://www.catalyst-au.net/contact-us
