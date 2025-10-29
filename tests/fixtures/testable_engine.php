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

use stdClass;

/**
 * Elasticsearch engine.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class testable_engine extends \search_elastic\engine {
    /** @var array */
    private array $mockindexresults = [];

    /** @var int */
    private int $mockindexcallcount = 0;

    /**
     *      * Function that lets us update the internally cached config object of the engine.
     * @param string $name
     * @param mixed $value
     */
    public function test_set_config($name, $value) {
        $this->config->$name = $value;
    }

    /**
     * Helper method to set the payload for testing.
     * @param string $payload
     */
    public function set_test_payload(string $payload): void {
        $this->payload = $payload;
        $this->payloadsize = strlen($payload);
    }

    /**
     * Helper method to set mock index results.
     * @param array $results
     */
    public function set_mock_index_results(array $results): void {
        $this->mockindexresults = $results;
        $this->mockindexcallcount = 0;
    }

    /**
     * Wrapper for testing handle_413_retry.
     * @return int
     */
    public function test_handle_413_retry(): int {
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('handle_413_retry');
        $method->setAccessible(true);
        return $method->invoke($this);
    }

    /**
     * Wrapper for testing log_bulk_response_item_errors.
     * @param stdClass $responsebody
     * @return int
     */
    public function test_log_bulk_response_item_errors(stdClass $responsebody): int {
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('log_bulk_response_item_errors');
        $method->setAccessible(true);
        return $method->invoke($this, $responsebody);
    }

    /**
     * Wrapper for setting mock index results for index_single_document.
     * @param array $docdata
     * @return bool
     */
    public function index_single_document(array $docdata): bool {
        if (!empty($this->mockindexresults)) {
            $result = $this->mockindexresults[$this->mockindexcallcount] ?? true;
            $this->mockindexcallcount++;
            return $result;
        }

        return parent::index_single_document($docdata);
    }
}
