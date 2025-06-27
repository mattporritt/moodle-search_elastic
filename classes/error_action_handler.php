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

use core\notification;
use core\task\manager;
use dml_missing_record_exception;
use Exception;
use moodle_url;
use search_elastic\local\model\error;
use search_elastic\local\service\error_service;
use search_elastic\task\retry_errors_task;

/**
 * Handles error-related actions from the admin interface.
 *
 * @package     search_elastic
 * @copyright   2025 Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class error_action_handler {

    /** Delete a single error record from database. */
    const ACTION_DELETE_SINGLE = 'delete_single';

    /** Delete all error records that have obsolete status. */
    const ACTION_DELETE_ALL_OBSOLETE = 'delete_all_obsolete';

    /** Retry indexing for a single error record. */
    const ACTION_RETRY_SINGLE = 'retry_single';

    /** Retry all error records that have failed status. */
    const ACTION_RETRY_ALL_FAILED = 'retry_all_failed';

    /** File size threshold for spawning adhoc tasks (1 MB). */
    const ADHOC_TASK_FILE_THRESHOLD = 1000000;

    /**
     * Handle error-related actions.
     *
     * @param string $action The action to perform
     * @param int $errorid The ID of the error (for single retry operations)
     * @param moodle_url $pageurl The page URL to redirect to
     * @param bool $confirm Whether the action was confirmed
     */
    public static function handle_action(string $action, int $errorid, moodle_url $pageurl, bool $confirm = false): void {
        // Redirect if no confirmation.
        if (!$confirm) {
            redirect($pageurl);
        }

        switch ($action) {
            case self::ACTION_DELETE_SINGLE:
                self::handle_delete_single($errorid, $pageurl);
                break;
            case self::ACTION_DELETE_ALL_OBSOLETE:
                self::handle_delete_all_obsolete($pageurl, $confirm);
                break;
            case self::ACTION_RETRY_SINGLE:
                self::handle_retry_single($errorid, $pageurl);
                break;
            case self::ACTION_RETRY_ALL_FAILED:
            default:
                self::handle_retry_all_failed($pageurl, $confirm);
        }
    }

    /**
     * This will delete a given error record from database.
     *
     * @param int $errorid
     * @param moodle_url $pageurl The page URL to redirect to
     */
    private static function handle_delete_single(int $errorid, moodle_url $pageurl): void {
        if (!$errorid) {
            notification::error(get_string('invaliderrorid', 'search_elastic'));
            redirect($pageurl);
        }

        try {
            $error = new error($errorid);
            $error->delete();
            notification::success(get_string('deletedsuccessfully', 'search_elastic', $errorid));
            redirect($pageurl);
        } catch (dml_missing_record_exception $e) {
            notification::warning(get_string('erroridnotfound', 'search_elastic', $errorid));
            redirect($pageurl);
        }
    }

    /**
     * This will delete all error records that have obsolete status.
     *
     * @param moodle_url $pageurl The page URL to redirect to
     */
    private static function handle_delete_all_obsolete(moodle_url $pageurl): void {
        global $DB;

        try {
            $obsoletecount = error_service::get_error_count_by_status(error::STATUS_OBSOLETE);
            $deleted = $DB->delete_records('search_elastic_errors', ['status' => error::STATUS_OBSOLETE]);
            if ($deleted) {
                notification::success(get_string('deletedobsoleteerrors', 'search_elastic', $obsoletecount));
            }
        } catch (Exception $e) {
            notification::error(get_string('deleteobsoleteexception', 'search_elastic', $e->getMessage()));
        }

        redirect($pageurl);
    }

    /**
     * This will retry all error records that have failed status.
     *
     * @param moodle_url $pageurl The page URL to redirect to
     */
    private static function handle_retry_all_failed(moodle_url $pageurl): void {
        global $USER;

        $task = new retry_errors_task();
        $task->set_custom_data([
            'operation' => self::ACTION_RETRY_ALL_FAILED,
            'userid' => $USER->id,
        ]);

        $task->set_component('search_elastic');
        manager::queue_adhoc_task($task);
        notification::info(get_string('retryallqueued', 'search_elastic'));

        redirect($pageurl);
    }

    /**
     * This will retry indexing for a given error record.
     *
     * @param int $errorid
     * @param moodle_url $pageurl The page URL to redirect to
     */
    private static function handle_retry_single(int $errorid, moodle_url $pageurl): void {
        global $USER;

        if (!$errorid) {
            notification::error(get_string('invaliderrorid', 'search_elastic'));
            redirect($pageurl);
        }

        try {
            $error = new error($errorid);
            if (!$error->can_retry()) {
                notification::error(get_string('exceededmaxretries', 'search_elastic'));
                redirect($pageurl);
            }

            if (self::should_spawn_adhoc_task($error)) {
                $task = new retry_errors_task();
                $task->set_custom_data([
                    'operation' => self::ACTION_RETRY_SINGLE,
                    'errorid' => $errorid,
                    'userid' => $USER->id,
                ]);

                $task->set_component('search_elastic');
                manager::queue_adhoc_task($task);
                notification::info(get_string('retrysingleadhoc', 'search_elastic'));
                redirect($pageurl);
            }

            $result = error_service::retry_error($errorid);
            $result['success'] ? notification::success($result['message']) : notification::error($result['message']);
        } catch (dml_missing_record_exception $e) {
            notification::warning(get_string('erroridnotfound', 'search_elastic', $errorid));
        }

        redirect($pageurl);
    }

    /**
     * Determine if an adhoc task should be spawned for retrying an error.
     *
     * For Tika errors, files with size greater than 1 MB are processed via adhoc task.
     * For Indexing errors, documents with files are processed via adhoc task.
     *
     * @param error $error
     * @return bool
     */
    private static function should_spawn_adhoc_task(error $error): bool {
        global $DB;

        if ($error->get('errortype') == error::TYPE_TIKA) {
            $fileid = (int)($error->get('docid'));
            if (!$fileid) {
                return false;
            }

            $filesize = $DB->get_field('files', 'filesize', ['id' => $fileid]);
            if ($filesize && (int)$filesize > self::ADHOC_TASK_FILE_THRESHOLD) {
                return true;
            }
        }

        return error_service::error_has_indexable_files($error);
    }
}
