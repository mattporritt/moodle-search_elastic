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

namespace search_elastic\task;

use core\lock\lock_config;
use core\task\adhoc_task;
use Exception;
use search_elastic\engine;
use search_elastic\error_action_handler;
use search_elastic\local\model\error;
use search_elastic\local\service\error_service;

/**
 * Adhoc task for retrying Elasticsearch errors.
 *
 * @package    search_elastic
 * @author     Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright  2025 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class retry_errors_task extends adhoc_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('retryerrorstask', 'search_elastic');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        $data = $this->get_custom_data();
        $operation = $data->operation ?? error_action_handler::ACTION_RETRY_ALL_FAILED;
        $errorid = $data->errorid ?? null;

        $results = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'operation' => $operation,
        ];

        switch ($operation) {
            case error_action_handler::ACTION_RETRY_SINGLE:
                $results = $this->retry_single($errorid);
                break;
            case error_action_handler::ACTION_RETRY_ALL_FAILED:
            default:
                $lockfactory = lock_config::get_lock_factory('search_elastic');
                $lock = $lockfactory->get_lock('retry_all_failed', 10);
                if (!$lock) {
                    mtrace('Unable to obtain lock for retry_all_failed - another task is running.');
                    return;
                }
                $results = $this->retry_all_failed(true);
                $lock->release();
        }

        mtrace('Retry task completed: ' . $operation . ' - ' .
                'processed: ' . $results['processed'] . ', ' .
                'success: ' . $results['success'] . ', ' .
                'failed: ' . $results['failed']);
    }

    /**
     * Retry a single error.
     *
     * @param int $errorid
     * @return array
     */
    private function retry_single($errorid): array {
        $successcount = 0;
        $errorcount = 0;

        try {
            $error = new error($errorid);
            mtrace("Processing document ID: {$error->get('docid')} with areaid: {$error->get('areaid')}");
            $result = error_service::retry_error($errorid);
            if ($result['success']) {
                mtrace("Successfully indexed document ID: {$error->get('docid')}");
                $successcount++;
            } else {
                $errorcount++;
                mtrace("Failed to retry error ID {$errorid}: {$result['message']}");
            }

        } catch (Exception $e) {
            $errorcount++;
            mtrace("Exception retrying error ID {$errorid}: " . $e->getMessage());
        }

        return  [
            'processed' => 1,
            'success' => $successcount,
            'failed' => $errorcount,
        ];
    }

    /**
     * Retry all failed.
     *
     * @return array
     */
    private function retry_all_failed(): array {
        $errors = error::get_records(['status' => error::STATUS_FAILED]);
        if (empty($errors)) {
            mtrace(get_string('noerrors', 'search_elastic'));
            return ['processed' => 0, 'success' => 0, 'failed' => 0];
        }

        $successcount = 0;
        $errorcount = 0;
        $processed = 0;

        $engine = new engine();
        $batchsize = $engine->get_batch_max_documents();

        // Process in batches.
        foreach (array_chunk($errors, $batchsize) as $batch) {
            foreach ($batch as $error) {
                try {
                    mtrace("Processing document ID: {$error->get('docid')} with areaid: {$error->get('areaid')}");
                    $result = error_service::retry_error($error->get('id'));
                    if ($result['success']) {
                        mtrace("Successfully indexed document ID: {$error->get('docid')}");
                        $successcount++;
                    } else {
                        $errorcount++;
                        mtrace("Failed to retry error ID {$error->get('id')}: {$result['message']}");
                    }
                } catch (Exception $e) {
                    $errorcount++;
                    mtrace("Exception retrying error ID {$error->get('id')}: " . $e->getMessage());
                }

                $processed++;
            }
        }

        return [
            'processed' => $processed,
            'success' => $successcount,
            'failed' => $errorcount,
        ];
    }
}
