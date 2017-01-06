# Moodle Global Search - Elasticsearch Backend

This plugin allows Moodle to use Elasticsearch as the search enging for Moodle's Global Search.

## Installation
**NOTE:** Complete all of these steps before trying to enable the Global Search functionality in Moodle.

1. Get the code and copy/ install it to: `<moodledir>/search/engine/elastic`
2. Run the upgrade: `sudo -u www-run php admin/cli/upgrade`
3. Set up the plugin in *Site administration > Plugins > Search > Manage global search* by selecting *elastic* as the search engine.
4. Configure the Elasticsearch plugin at: *Site administration > Plugins > Search > Elastic*
    4.1 Set *hostname* and *port* of your Elasticsearch server
5. To create the index and populate Elasticsearch with your site's data, run this CLI script. `sudo -u www-run php search/cli/indexer.php --force`
6. Enable Global search in *Site administration > Advanced features*