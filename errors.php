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
 * Elasticsearch indexing errors management page.
 *
 * @package    search_elastic
 * @author     Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright  2025 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use core\notification;
use search_elastic\error_action_handler;
use search_elastic\local\model\error;
use search_elastic\local\service\error_service;
use core_reportbuilder\system_report_factory;
use search_elastic\reportbuilder\local\systemreports\errors;

defined('MOODLE_INTERNAL') || die();

admin_externalpage_setup('search_elastic_errors');

// Get parameters.
$action = optional_param('action', '', PARAM_ALPHAEXT);
$errorid = optional_param('id', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$context = context_system::instance();

$PAGE->set_url(new moodle_url('/search/engine/elastic/errors.php'));

if ($action && confirm_sesskey()) {
    error_action_handler::handle_action($action, $errorid, $PAGE->url, $confirm);
}

// Build the page output.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('indexingerrors', 'search_elastic'));

$failedcount = error_service::get_error_count_by_status(error::STATUS_FAILED);
$obsoletecount = error_service::get_error_count_by_status(error::STATUS_OBSOLETE);
$buttons = [];
if ($failedcount > 0) {
    $retryallfailed = new moodle_url($PAGE->url, [
        'action' => error_action_handler::ACTION_RETRY_ALL_FAILED,
        'sesskey' => sesskey(),
        'confirm' => 1,
    ]);

    $buttons[] = $OUTPUT->single_button($retryallfailed, get_string('retryallfailed', 'search_elastic'), 'post', [
        'data-confirmation' => 'modal',
        'data-confirmation-title-str' => json_encode(['retryallfailed', 'search_elastic']),
        'data-confirmation-content-str' => json_encode(['confirm:retryallfailed', 'search_elastic']),
        'data-confirmation-yes-button-str' => json_encode(['confirm', 'core']),
        'title' => get_string('retryallfailed_desc', 'search_elastic'),
    ]);
}

if ($obsoletecount > 0) {
    $removeallobsolete = new moodle_url($PAGE->url, [
        'action' => error_action_handler::ACTION_DELETE_ALL_OBSOLETE,
        'sesskey' => sesskey(),
        'confirm' => 1,
    ]);

    $buttons[] = $OUTPUT->single_button($removeallobsolete, get_string('removeallobsolete', 'search_elastic'), 'post', [
        'data-confirmation' => 'modal',
        'data-confirmation-title-str' => json_encode(['removeallobsolete', 'search_elastic']),
        'data-confirmation-content-str' => json_encode(['confirm:removeallobsolete', 'search_elastic']),
        'data-confirmation-yes-button-str' => json_encode(['confirm', 'core']),
        'title' => get_string('removeallobsolete_desc', 'search_elastic'),
    ]);
}

if (!empty($buttons)) {
    echo html_writer::div(implode('', $buttons), 'mb-3');
}

// Create and display the report.
$report = system_report_factory::create(errors::class, $context);
echo $report->output();

echo $OUTPUT->footer();
