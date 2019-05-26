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

/**
 * Mock AWS Rekognition responsefor use in testing.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MockRekognition {
    /**
     * A mock for detect labels method on
     * AWS Rekognition client.
     *
     * @param array $params params normally passed to client.
     * @return array $results The mock results.
     */
    // @codingStandardsIgnoreStart
    public function detectLabels($params) {
        // @codingStandardsIgnoreEnd
        $results = array (
                'Labels' => array (
                        array ( 'Name' => 'black',
                                'Confidence' => 91.745529174805
                        )
                )
        );
        return $results;
    }

    /**
     * A mock for detect text method on
     * AWS Rekognition client.
     *
     * @param array $params params normally passed to client.
     * @return array $results The mock results.
     */
    // @codingStandardsIgnoreStart
    public function detectText($params) {
        // @codingStandardsIgnoreEnd
        $results = array (
            'TextDetections' => array (
                array ( 'DetectedText' => 'thecolor',
                )
            )
        );
        return $results;
    }
}
