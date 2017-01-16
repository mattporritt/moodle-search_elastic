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
 * Elasticsearch engine.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace search_elastic;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/lib/filelib.php');

/**
 * Extending the core Moodle Curl lib
 * to override methods required for search engine communication.
 *
 */
class elastic_curl extends \curl{
    /**
     * HTTP PUT method
     * Moodle's core PUT method in unsuitable here
     * as it always expects a file as part of PUT.
     *
     * @param string $url
     * @param array $params
     * @param array $options
     * @return bool
     */
    public function put($url, $params = array(), $options = array()) {
        $options['CURLOPT_PUT']        = 1;
        if (!isset($this->options['CURLOPT_USERPWD'])) {
            $this->setopt(array('CURLOPT_USERPWD' => 'anonymous: noreply@moodle.org'));
        }
        $ret = $this->request($url, $options);
        return $ret;
    }
}
