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

namespace search_elastic;

use advanced_testcase;
use core\check\result;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use search_elastic\check\server_ready_check;

/**
 * Elasticsearch plugin server ready check tests
 *
 * @package     search_elastic
 * @copyright   Matthew Hilton <matthew.hilton@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \search_elastic\check\server_ready_check
 */
final class server_ready_check_test extends advanced_testcase {
    /** @var string Valid hostname for testing */
    private const VALID_HOSTNAME = 'valid.com';

    /** @var string Invalid hostname for testing */
    private const INVALID_HOSTNAME = 'invalid.com';

    /** @var string Empty hostname for testing */
    private const EMPTY_HOSTNAME = '';

    /**
     * Provides server ready test configurations.
     * @return array
     */
    public function server_ready_provider(): array {
        return [
            'not set' => [
                'hostname' => self::EMPTY_HOSTNAME,
                'status' => result::NA,
            ],
            'invalid hostname' => [
                'hostname' => self::INVALID_HOSTNAME,
                'status' => result::ERROR,
            ],
            'valid hostname' => [
                'hostname' => self::VALID_HOSTNAME,
                'status' => result::OK,
            ],
        ];
    }

    /**
     * Tests check result.
     *
     * @param string $hostname
     * @param string $expectedstatus
     * @dataProvider server_ready_provider
     */
    public function test_check(string $hostname, string $expectedstatus): void {
        $this->resetAfterTest();
        set_config('hostname', $hostname, 'search_elastic');

        $testhostnamestatus = [
            self::VALID_HOSTNAME => 200,
            self::INVALID_HOSTNAME => 404,
            self::EMPTY_HOSTNAME => 400,
        ];

        $mock = new MockHandler([
            new Response($testhostnamestatus[$hostname], ['Content-Type' => 'application/json']),
        ]);
        $stack = HandlerStack::create($mock);

        $check = new server_ready_check($stack);
        $result = $check->get_result();
        $this->assertEquals($expectedstatus, $result->get_status());
    }

    /**
     * Test check result when hostname won't resolve.
     */
    public function test_check_unreachable_host(): void {
        $this->resetAfterTest();
        set_config('hostname', 'unreachable', 'search_elastic');

        $check = new server_ready_check();
        $result = $check->get_result();

        $this->assertEquals(result::ERROR, $result->get_status());
    }
}
