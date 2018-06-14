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
 * Custom Guzzle exception class.
 *
 * Guzzle returns a standard response object for regular (successful) responses
 * as well as for errors raised by \GuzzleHttp\Exception\BadResponseException.
 * However it does not raise a standard response object for \GuzzleHttp\Exception\GuzzleException.
 *
 * This class provides a simple interface response object interface for errors raised with
 * \GuzzleHttp\Exception\GuzzleException.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic;

defined('MOODLE_INTERNAL') || die();

/**
 * Custom Guzzle exception class.
 *
 * Guzzle returns a standard response object for regular (successful) responses
 * as well as for errors raised by \GuzzleHttp\Exception\BadResponseException.
 * However it does not raise a standard response object for \GuzzleHttp\Exception\GuzzleException.
 *
 * This class provides a simple interface response object interface for errors raised with
 * \GuzzleHttp\Exception\GuzzleException.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class guzzle_exception {

    // @codingStandardsIgnoreStart
    /**
     * Return error code for a URL that does not resolve.
     *
     * @return int The return code for a failure.
     */
    public function getStatusCode() {
        return 410;
    }
    // @codingStandardsIgnoreEnd

}