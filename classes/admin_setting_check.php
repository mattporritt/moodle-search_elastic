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

use admin_setting;
use core\check\check;
use core\check\result;
use core\output\notification;

/**
 * Admin setting for check api.
 *
 * @package     search_elastic
 * @copyright   Matthew Hilton <matthew.hilton@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_check extends admin_setting {
    /** @var check $check The check to display */
    private $check;

    /**
     * Creates check setting.
     *
     * @param string $name name of setting
     * @param string $heading title of setting
     * @param check $check check to display
     */
    public function __construct(string $name, string $heading, check $check) {
        $this->nosave = true;
        $this->check = $check;
        parent::__construct($name, $heading, '', '');
    }

    /**
     * Returns setting (unused)
     *
     * @return true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Writes the setting (unused)
     *
     * @param mixed $data
     */
    public function write_setting($data) {
        return '';
    }

    /**
     * Ouputs the admin setting HTML to be rendered.
     *
     * @param mixed $data
     * @param string $query
     * @return string html
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;

        // Run the check and get the result.
        $checkresult = $this->check->get_result();

        $resulthtml = $OUTPUT->check_result($checkresult);
        $resultinfo = $checkresult->get_summary();
        $out = $resulthtml . ' ' . $resultinfo;

        switch ($checkresult->get_status()) {
            case result::CRITICAL:
            case result::ERROR:
                $out = $OUTPUT->notification($out, notification::NOTIFY_ERROR, false);
                break;

            case result::OK:
                $out = $OUTPUT->notification($out, notification::NOTIFY_SUCCESS, false);
                break;
        }
        return format_admin_setting($this, $this->visiblename, '', $out);
    }
}
