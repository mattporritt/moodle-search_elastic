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
 * Elastic search engine unit tests.
 *
 * @package    search_elastic
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');
require_once($CFG->dirroot . '/search/tests/fixtures/mock_search_area.php');
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/testable_engine.php');

use \GuzzleHttp\Handler\MockHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Middleware;
use \GuzzleHttp\Psr7\Response;
use \GuzzleHttp\Psr7\Request;

/**
 * Tests for esrequest class
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_elastic_esrequest_testcase extends advanced_testcase {

    /**
     * Test unsigned esrequest get functionality
     */
    public function test_get() {
        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain'])
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->get($url);
        $request = $container[0]['request'];

        // Check the results.
        $this->assertEquals($request->getUri()->getScheme(), 'http');
        $this->assertEquals($request->getUri()->getHost(),  'localhost');
        $this->assertEquals($request->getUri()->getPort(),  '8080');
        $this->assertEquals($request->getUri()->getPath(), '/foo');
        $this->assertEquals($request->getUri()->getQuery(), 'bar=blerg');

    }

    /**
     * Test signed esrequest get functionality
     */
    public function test_signed_get() {
        $this->resetAfterTest(true);
        set_config('signing', 1, 'search_elastic');
        set_config('keyid', 'key_id', 'search_elastic');
        set_config('secretkey', 'secret_key', 'search_elastic');
        set_config('region', 'region', 'search_elastic');

        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain'])
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->get($url);
        $request = $container[0]['request'];
        $auth_header = $request->getHeader('Authorization');

        // Check the results.
        $this->assertEquals($request->getUri()->getScheme(), 'http');
        $this->assertEquals($request->getUri()->getHost(),  'localhost');
        $this->assertEquals($request->getUri()->getPort(),  '8080');
        $this->assertEquals($request->getUri()->getPath(), '/foo');
        $this->assertEquals($request->getUri()->getQuery(), 'bar=blerg');
        $this->assertTrue($request->hasHeader('X-Amz-Date'));
        $this->assertTrue($request->hasHeader('Authorization'));
        $this->assertRegexp('/key_id.{10}region/', $auth_header[0]);
    }

    /**
     * Test unsigned esrequest put functionality
     */
    public function test_put() {
        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain'])
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $params = '{"properties":"value"}';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->put($url, $params);
        $request = $container[0]['request'];
        $content_header = $request->getHeader('content-type');

        // Check the results.
        $this->assertEquals($request->getUri()->getScheme(), 'http');
        $this->assertEquals($request->getUri()->getHost(),  'localhost');
        $this->assertEquals($request->getUri()->getPort(),  '8080');
        $this->assertEquals($request->getUri()->getPath(), '/foo');
        $this->assertEquals($request->getUri()->getQuery(), 'bar=blerg');
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertEquals($content_header, array('application/x-www-form-urlencoded'));

    }

    /**
     * Test signed esrequest put functionality
     */
    public function test_signed_put() {
        $this->resetAfterTest(true);
        set_config('signing', 1, 'search_elastic');
        set_config('keyid', 'key_id', 'search_elastic');
        set_config('secretkey', 'secret_key', 'search_elastic');
        set_config('region', 'region', 'search_elastic');

        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain'])
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $params = '{"properties":"value"}';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->put($url, $params);
        $request = $container[0]['request'];
        $auth_header = $request->getHeader('Authorization');
        $content_header = $request->getHeader('content-type');

        // Check the results.
        $this->assertEquals($request->getUri()->getScheme(), 'http');
        $this->assertEquals($request->getUri()->getHost(),  'localhost');
        $this->assertEquals($request->getUri()->getPort(),  '8080');
        $this->assertEquals($request->getUri()->getPath(), '/foo');
        $this->assertEquals($request->getUri()->getQuery(), 'bar=blerg');
        $this->assertTrue($request->hasHeader('X-Amz-Date'));
        $this->assertTrue($request->hasHeader('Authorization'));
        $this->assertRegexp('/key_id.{10}region/', $auth_header[0]);
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertEquals($content_header, array('application/x-www-form-urlencoded'));
    }

    /**
     * Test unsigned esrequest post functionality
     */
    public function test_post() {
        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain'])
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $params = '{"properties":"value"}';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->post($url, $params);
        $request = $container[0]['request'];
        $content_header = $request->getHeader('content-type');

        // Check the results.
        $this->assertEquals($request->getUri()->getScheme(), 'http');
        $this->assertEquals($request->getUri()->getHost(),  'localhost');
        $this->assertEquals($request->getUri()->getPort(),  '8080');
        $this->assertEquals($request->getUri()->getPath(), '/foo');
        $this->assertEquals($request->getUri()->getQuery(), 'bar=blerg');
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertEquals($content_header, array('application/x-www-form-urlencoded'));

    }

    /**
     * Test signed esrequest post functionality
     */
    public function test_signed_post() {
        $this->resetAfterTest(true);
        set_config('signing', 1, 'search_elastic');
        set_config('keyid', 'key_id', 'search_elastic');
        set_config('secretkey', 'secret_key', 'search_elastic');
        set_config('region', 'region', 'search_elastic');

        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain'])
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $params = '{"properties":"value"}';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->post($url, $params);
        $request = $container[0]['request'];
        $auth_header = $request->getHeader('Authorization');
        $content_header = $request->getHeader('content-type');

        // Check the results.
        $this->assertEquals($request->getUri()->getScheme(), 'http');
        $this->assertEquals($request->getUri()->getHost(),  'localhost');
        $this->assertEquals($request->getUri()->getPort(),  '8080');
        $this->assertEquals($request->getUri()->getPath(), '/foo');
        $this->assertEquals($request->getUri()->getQuery(), 'bar=blerg');
        $this->assertTrue($request->hasHeader('X-Amz-Date'));
        $this->assertTrue($request->hasHeader('Authorization'));
        $this->assertRegexp('/key_id.{10}region/', $auth_header[0]);
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertEquals($content_header, array('application/x-www-form-urlencoded'));
    }
}
