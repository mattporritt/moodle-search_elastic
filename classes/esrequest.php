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
 * Provides request signing
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic;

/**
 * Class creates the API calls to Elasticsearch.
 *
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class esrequest {
    /**
     * @var bool True if we should sign requests, false if not.
     */
    private $signing = false;

    /**
     * @var elasticsearch plugin config.
     */
    private $config = null;

    /** @var \GuzzleHttp\Client $client A guzzle client. */
    private $client;

    /**
     * Initialises the search engine configuration.
     *
     * Search engine availability should be checked separately.
     *
     * @param \GuzzleHttp\HandlerStack $handler Optional custom Guzzle handler stack
     * @return void
     */
    public function __construct($handler = false) {
        $this->config = get_config('search_elastic');
        $this->signing = (isset($this->config->signing) ? (bool)$this->config->signing : false);

        $config = [
            'connect_timeout' => intval($this->config->connecttimeout)
        ];

        // Allow the caller to instantiate the Guzzle client with a custom handler.
        if ($handler) {
            $config['handler'] = $handler;
        }
        $this->client = guzzle_helper::configure_client_proxy(new \GuzzleHttp\Client($config));
    }

    /**
     * Signs a request with the supplied credentials.
     * This is used for access control to the Elasticsearch endpoint.
     *
     * @param \GuzzleHttp\Psr7\Request $request
     * @throws \moodle_exception
     * @return \GuzzleHttp\Psr7\Request
     */
    private function signrequest($request) {
        // Check we are all configured for request signing.
        if (empty($this->config->signingkeyid) ||
                empty($this->config->signingsecretkey) ||
                empty($this->config->region)) {
            throw new \moodle_exception('noconfig', 'search_elastic', '');
        }

        // Pull credentials from the default provider chain.
        $credentials = new \Aws\Credentials\Credentials(
                $this->config->signingkeyid,
                $this->config->signingsecretkey
                );
        // Create a signer with the service's signing name and region.
        $signer = new \Aws\Signature\SignatureV4('es', $this->config->region);

        // Sign your request.
        $signedrequest = $signer->signRequest($request, $credentials);

        return $signedrequest;
    }

    /**
     * Execute the HTTP action and return the response.
     * Requests that receive a 4xx or 5xx response will throw a
     * Guzzle\Http\Exception\BadResponseException.
     * Requests to a URL that does not resolve will raise a \GuzzleHttp\Exception\GuzzleException.
     * We want to handle this in a sane way and provide the caller with
     * a useful response. So we catch the error and return the
     * response.
     *
     * @param \GuzzleHttp\Psr7\Request $psr7request
     * @return \GuzzleHttp\Psr7\Response
     */
    private function http_action($psr7request) {
        try {
            $response = $this->client->send($psr7request);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $response = $e->getResponse();
        }

        return $response;
    }

    /**
     * Process GET requests to Elasticsearch.
     *
     * @param string $url
     * @return \GuzzleHttp\Psr7\Response
     */
    public function get($url) {
        $headers = $this->get_authorization_header();

        $psr7request = new \GuzzleHttp\Psr7\Request('GET', $url, $headers);

        if ($this->signing) {
            $psr7request = $this->signrequest($psr7request);
        }

        $response = $this->http_action($psr7request);

        return $response;

    }

    /**
     * Process PUT requests to Elasticsearch.
     *
     * @param string $url
     * @param array $params
     * @return \GuzzleHttp\Psr7\Response
     */
    public function put($url, $params=null) {
        $headers = $this->get_authorization_header();
        $headers['content-type'] = 'application/json';

        $psr7request = new \GuzzleHttp\Psr7\Request('PUT', $url, $headers, $params);

        if ($this->signing) {
            $psr7request = $this->signrequest($psr7request);
        }

        $response = $this->http_action($psr7request);

        return $response;

    }

    /**
     * Creates post API requests.
     * @param string $url
     * @param unknown $params
     * @return \Psr\Http\Message\ResponseInterface|NULL
     */
    public function post($url, $params) {
        $headers = $this->get_authorization_header();
        $headers['content-type'] = 'application/json';

        $psr7request = new \GuzzleHttp\Psr7\Request('POST', $url, $headers, $params);

        if ($this->signing) {
            $psr7request = $this->signrequest($psr7request);
        }

        $response = $this->http_action($psr7request);

        return $response;

    }

    /**
     * Posts a Moodle file object to provided URL.
     *
     * @param string $url URL to post file to.
     * @param file $file Moodle file object to post
     * @return \Psr\Http\Message\ResponseInterface|NULL
     */
    public function postfile($url, $file) {
        $headers = $this->get_authorization_header();

        $contents = $file->get_content_file_handle();
        $multipart = new \GuzzleHttp\Psr7\MultipartStream([
                [
                        'name' => 'upload_file',
                        'contents' => $contents
                ],
        ]);

        $psr7request = new \GuzzleHttp\Psr7\Request('POST', $url, $headers, $multipart);

        $response = $this->http_action($psr7request);

        return $response;

    }

    /**
     * Creates delete API requests.
     *
     * @param unknown $url
     * @return \Psr\Http\Message\ResponseInterface|NULL
     */
    public function delete($url) {
        $headers = $this->get_authorization_header();

        $psr7request = new \GuzzleHttp\Psr7\Request('DELETE', $url, $headers);

        if ($this->signing) {
            $psr7request = $this->signrequest($psr7request);
        }

        $response = $this->http_action($psr7request);

        return $response;

    }

    /**
     * Return authorization header.
     *
     * @return array|string[]
     */
    private function get_authorization_header() {
        if (!empty($this->config->apikey)) {
            return [
                'Authorization' => 'ApiKey ' . $this->config->apikey,
            ];
        }
        return [];
    }
}
