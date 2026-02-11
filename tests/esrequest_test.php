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

namespace search_elastic;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');
require_once($CFG->dirroot . '/search/tests/fixtures/mock_search_area.php');
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/testable_engine.php');

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

/**
 * Tests for esrequest class
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \search_elastic\esrequest
 */
final class esrequest_test extends \advanced_testcase {
    /**
     * @var \stdClass $cfg Backup of global config.
     */
    protected $cfg;

    /**
     * Polyfill to support the new regexp assertion in place of the old, deprecated one.
     *
     * This can be removed once we no longer need to support Moodle <3.11/PHPUnit 8.5.
     *
     * @param string $method
     * @param array $args
     * @return mixed|void
     */
    public function __call(string $method, array $args) {
        if ($method === 'assertMatchesRegularExpression' && method_exists($this, 'assertRegExp')) {
            return call_user_func_array([$this, 'assertRegExp'], $args);
        }
    }

    /**
     * Test setup.
     */
    public function setUp(): void {
        global $CFG;
        parent::setUp();
        $this->cfg = clone $CFG;
        $this->resetAfterTest(true);
        new \search_elastic\engine();
    }

    /**
     * Reset global $CFG object.
     *
     * @return void
     */
    public function tearDown(): void {
        global $CFG;
        $CFG = clone $this->cfg;
        unset($this->cfg);
        parent::tearDown();
    }

    /**
     * Test that unreachable host must return 503
     * The GuzzleHttp exception is no longer be throwed
     */
    public function test_get_unreachable_host(): void {
        $this->expectException(\GuzzleHttp\Exception\ConnectException::class);
        $url = 'http://unreachable:9020';
        $client = new \search_elastic\esrequest();
        $response = $client->get($url);
    }

    /**
     * Test unsigned esrequest get functionality
     */
    public function test_get(): void {
        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain']),
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->get($url);
        $request = $container[0]['request'];

        // Check the results.
        $this->assertEquals('http', $request->getUri()->getScheme());
        $this->assertEquals('localhost', $request->getUri()->getHost());
        $this->assertEquals('8080', $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=blerg', $request->getUri()->getQuery());
    }

    /**
     * Test signed esrequest get functionality
     */
    public function test_signed_get(): void {
        $this->resetAfterTest(true);
        set_config('signing', 1, 'search_elastic');
        set_config('signingkeyid', 'key_id', 'search_elastic');
        set_config('signingsecretkey', 'secret_key', 'search_elastic');
        set_config('region', 'region', 'search_elastic');

        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain']),
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->get($url);
        $request = $container[0]['request'];
        $authheader = $request->getHeader('Authorization');

        // Check the results.
        $this->assertEquals('http', $request->getUri()->getScheme());
        $this->assertEquals('localhost', $request->getUri()->getHost());
        $this->assertEquals('8080', $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=blerg', $request->getUri()->getQuery());
        $this->assertTrue($request->hasHeader('X-Amz-Date'));
        $this->assertTrue($request->hasHeader('Authorization'));
        $this->assertMatchesRegularExpression('/key_id.{10}region/', $authheader[0]);
    }

    /**
     * Test unsigned esrequest put functionality
     */
    public function test_put(): void {
        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain']),
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $params = '{"properties":"value"}';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->put($url, $params);
        $request = $container[0]['request'];
        $contentheader = $request->getHeader('content-type');

        // Check the results.
        $this->assertEquals('http', $request->getUri()->getScheme());
        $this->assertEquals('localhost', $request->getUri()->getHost());
        $this->assertEquals('8080', $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=blerg', $request->getUri()->getQuery());
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertEquals(['application/json'], $contentheader);
    }

    /**
     * Test signed esrequest put functionality
     */
    public function test_signed_put(): void {
        $this->resetAfterTest(true);
        set_config('signing', 1, 'search_elastic');
        set_config('signingkeyid', 'key_id', 'search_elastic');
        set_config('signingsecretkey', 'secret_key', 'search_elastic');
        set_config('region', 'region', 'search_elastic');

        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain']),
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $params = '{"properties":"value"}';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->put($url, $params);
        $request = $container[0]['request'];
        $authheader = $request->getHeader('Authorization');
        $contentheader = $request->getHeader('content-type');

        // Check the results.
        $this->assertEquals('http', $request->getUri()->getScheme());
        $this->assertEquals('localhost', $request->getUri()->getHost());
        $this->assertEquals('8080', $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=blerg', $request->getUri()->getQuery());
        $this->assertTrue($request->hasHeader('X-Amz-Date'));
        $this->assertTrue($request->hasHeader('Authorization'));
        $this->assertMatchesRegularExpression('/key_id.{10}region/', $authheader[0]);
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertEquals(['application/json'], $contentheader);
    }

    /**
     * Test unsigned esrequest post functionality
     */
    public function test_post(): void {
        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain']),
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $params = '{"properties":"value"}';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->post($url, $params);
        $request = $container[0]['request'];
        $contentheader = $request->getHeader('content-type');

        // Check the results.
        $this->assertEquals('http', $request->getUri()->getScheme());
        $this->assertEquals('localhost', $request->getUri()->getHost());
        $this->assertEquals('8080', $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=blerg', $request->getUri()->getQuery());
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertEquals(['application/json'], $contentheader);
    }

    /**
     * Test signed esrequest post functionality
     */
    public function test_signed_post(): void {
        $this->resetAfterTest(true);
        set_config('signing', 1, 'search_elastic');
        set_config('signingkeyid', 'key_id', 'search_elastic');
        set_config('signingsecretkey', 'secret_key', 'search_elastic');
        set_config('region', 'region', 'search_elastic');

        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain']),
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $params = '{"properties":"value"}';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->post($url, $params);
        $request = $container[0]['request'];
        $authheader = $request->getHeader('Authorization');
        $contentheader = $request->getHeader('content-type');

        // Check the results.
        $this->assertEquals('http', $request->getUri()->getScheme());
        $this->assertEquals('localhost', $request->getUri()->getHost());
        $this->assertEquals('8080', $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=blerg', $request->getUri()->getQuery());
        $this->assertTrue($request->hasHeader('X-Amz-Date'));
        $this->assertTrue($request->hasHeader('Authorization'));
        $this->assertMatchesRegularExpression('/key_id.{10}region/', $authheader[0]);
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertEquals(['application/json'], $contentheader);
    }

    /**
     * Test unsigned esrequest delete functionality
     */
    public function test_delete(): void {
        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain']),
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->delete($url);
        $request = $container[0]['request'];

        // Check the results.
        $this->assertEquals('http', $request->getUri()->getScheme());
        $this->assertEquals('localhost', $request->getUri()->getHost());
        $this->assertEquals('8080', $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=blerg', $request->getUri()->getQuery());
    }

    /**
     * Test signed esrequest delete functionality
     */
    public function test_signed_delete(): void {
        $this->resetAfterTest(true);
        set_config('signing', 1, 'search_elastic');
        set_config('signingkeyid', 'key_id', 'search_elastic');
        set_config('signingsecretkey', 'secret_key', 'search_elastic');
        set_config('region', 'region', 'search_elastic');

        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain']),
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://localhost:8080/foo?bar=blerg';
        $client = new \search_elastic\esrequest($stack);
        $response = $client->delete($url);
        $request = $container[0]['request'];
        $authheader = $request->getHeader('Authorization');

        // Check the results.
        $this->assertEquals('http', $request->getUri()->getScheme());
        $this->assertEquals('localhost', $request->getUri()->getHost());
        $this->assertEquals('8080', $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=blerg', $request->getUri()->getQuery());
        $this->assertTrue($request->hasHeader('X-Amz-Date'));
        $this->assertTrue($request->hasHeader('Authorization'));
        $this->assertMatchesRegularExpression('/key_id.{10}region/', $authheader[0]);
    }

    /**
     * Test esrequest get with proxy functionality
     */
    public function test_proxy_get(): void {
        global $CFG;
        $CFG->proxyhost = 'proxy.com';
        $CFG->proxyport = 3128;
        $CFG->proxybypass = 'localhost, 127.0.0.1';

        $container = [];
        $history = Middleware::history($container);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'text/plain']),
        ]);

        $stack = HandlerStack::create($mock);
        // Add the history middleware to the handler stack.
        $stack->push($history);

        $url = 'http://example.com:8080/foo?bar=blerg';
        $client = new \search_elastic\esrequest($stack);
        $client->get($url);
        $request = $container[0]['request'];

        $lastrequestoptions = $mock->getLastOptions();
        $this->assertArrayHasKey('proxy', $lastrequestoptions);
        $expected = 'proxy.com:3128';

        // Check the results.
        $this->assertEquals('http', $request->getUri()->getScheme());
        $this->assertEquals('example.com', $request->getUri()->getHost());
        $this->assertEquals('8080', $request->getUri()->getPort());
        $this->assertEquals('/foo', $request->getUri()->getPath());
        $this->assertEquals('bar=blerg', $request->getUri()->getQuery());
        $this->assertEquals($expected, $lastrequestoptions['proxy']);
    }

    /**
     * Test constructor applies configured timeout.
     */
    public function test_constructor_configured_timeout(): void {
        $this->resetAfterTest();

        // Mock config with timeout setting.
        set_config('timeout', 60, 'search_elastic');
        set_config('connecttimeout', 5, 'search_elastic');

        $esrequest = new esrequest();

        // Use reflection to access private client.
        $reflection = new \ReflectionClass($esrequest);
        $clientproperty = $reflection->getProperty('client');
        $clientproperty->setAccessible(true);
        $client = $clientproperty->getValue($esrequest);

        // Assert configured timeout is applied.
        $clientconfig = $client->getConfig();
        $this->assertEquals(60, $clientconfig['timeout']);
        $this->assertEquals(5, $clientconfig['connect_timeout']);
    }
}
