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

use admin_setting_configtext;

/**
 * Admin setting for chunking overlap.
 *
 * @package     search_elastic
 * @author      Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright   2026 Monash University (http://www.monash.edu)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_chunking_overlap extends admin_setting_configtext {
    /**
     * Validate data before storage.
     * @param int $data
     * @return mixed true if ok string if error found
     */
    public function validate($data) {
        $result = parent::validate($data);
        if ($result !== true) {
            return $result;
        }

        $overlapwords = $data;

        // Get current chunking strategy.
        $strategy = get_config('search_elastic', 'chunkingstrategy') ?: 'fixed_size';
        if ($strategy === 'fixed_size') {
            $maxsize = get_config('search_elastic', 'fs_chunkmaxsize') ?: 8000000;

            // Estimate max words that fit in chunk (assuming 6 bytes per word average).
            $estimatedwords = floor($maxsize / 6);

            // Overlap should be less than 50% of chunk capacity.
            $maxreasonableoverlap = floor($estimatedwords * 0.5);
            if ($overlapwords > $maxreasonableoverlap) {
                return get_string('chunkonverlaptoolarge', 'search_elastic', [
                    'overlap' => $overlapwords,
                    'maxsize' => $maxsize,
                    'maxreasonable' => $maxreasonableoverlap,
                ]);
            }
        }

        return true;
    }
}
