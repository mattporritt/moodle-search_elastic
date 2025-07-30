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

namespace search_elastic\reportbuilder\local\systemreports;

use context;
use context_system;
use core_reportbuilder\local\report\action;
use core_reportbuilder\system_report;
use search_elastic\reportbuilder\local\entities\error;
use search_elastic\error_action_handler;
use search_elastic\local\model\error as error_model;
use moodle_url;
use lang_string;
use pix_icon;
use stdClass;

/**
 * Search elastic errors system report.
 *
 * @package     search_elastic
 * @copyright   2025 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class errors extends system_report {

    /**
     * Initialise the report.
     *
     * @return void
     */
    protected function initialise(): void {
        $errorentity = new error();
        $erroralias = $errorentity->get_table_alias('search_elastic_errors');

        $this->set_main_table('search_elastic_errors', $erroralias);
        $this->add_entity($errorentity);

        // Any columns required by actions should be defined here to ensure they're always available.
        $this->add_base_fields("{$erroralias}.id, {$erroralias}.status");

        // Add default columns.
        $this->add_columns_from_entity($errorentity->get_entity_name(), [
            'docid',
            'areaid',
            'errortype',
            'errormessage',
            'status',
            'retrycount',
            'timemodified',
            'contentmodified',
            'parentid',
        ]);

        // Add default filters.
        $this->add_filters_from_entity($errorentity->get_entity_name(), [
            'docid',
            'areaid',
            'errortype',
            'errormessage',
            'status',
            'retrycount',
            'timemodified',
            'contentmodified',
            'parentid',
        ]);

        $this->add_actions();

        // Default sorting.
        $this->set_initial_sort_column($errorentity->get_entity_name() . ':timemodified', SORT_DESC);

        $this->set_downloadable(true);
    }

    /**
     * Add the system report actions. An extra column will be appended to each row, containing all actions added here.
     *
     * Note the use of ":id" placeholder which will be substituted according to actual values in the row.
     */
    protected function add_actions(): void {
        $baseurl = new moodle_url('/search/engine/elastic/errors.php');

        $this->add_action((new action(
            new moodle_url($baseurl, [
                'action' => error_action_handler::ACTION_RETRY_SINGLE,
                'id' => ':id',
                'sesskey' => sesskey(),
                'confirm' => 1,
            ]),
            new pix_icon('t/play', '', 'core'),
            [
                'data-confirmation' => 'modal',
                'data-confirmation-title-str' => json_encode(['retry', 'search_elastic']),
                'data-confirmation-content-str' => json_encode(['confirm:retrysingle', 'search_elastic']),
                'data-confirmation-yes-button-str' => json_encode(['retry', 'search_elastic']),
            ],
            false,
            new lang_string('retry', 'core')
        ))
            ->add_callback(function(stdClass $row): bool {
                // Only show retry button for retrying or failed errors.
                return in_array($row->status, [error_model::STATUS_RETRYING, error_model::STATUS_FAILED]);
            }));

        $this->add_action((new action(
            new moodle_url($baseurl, [
                'action' => error_action_handler::ACTION_DELETE_SINGLE,
                'id' => ':id',
                'sesskey' => sesskey(),
                'confirm' => 1,
            ]),
            new pix_icon('t/delete', '', 'core'),
            [
                'data-confirmation' => 'modal',
                'data-confirmation-title-str' => json_encode(['delete', 'core']),
                'data-confirmation-content-str' => json_encode(['confirm:delete', 'search_elastic']),
                'data-confirmation-yes-button-str' => json_encode(['delete', 'core']),
            ],
            false,
            new lang_string('delete', 'core')
        )));
    }

    /**
     * Returns report context.
     *
     * @return \context
     */
    public function get_context(): context {
        return context_system::instance();
    }

    /**
     * Check if can view this system report.
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('moodle/site:config', $this->get_context());
    }


    /**
     * Return the columns that will be added to the report once it's created.
     *
     * @return array
     */
    public function get_default_columns(): array {
        return [
            'error:docid',
            'error:areaid',
            'error:errortype',
            'error:errormessage',
            'error:status',
            'error:retrycount',
            'error:timemodified',
            'error:contentmodified',
            'error:parentid',
        ];
    }

    /**
     * Return the filters that will be added to the report once it's created.
     *
     * @return array
     */
    public function get_default_filters(): array {
        return [
            'error:docid',
            'error:areaid',
            'error:errortype',
            'error:errormessage',
            'error:status',
            'error:retrycount',
            'error:timemodified',
            'error:contentmodified',
            'error:parentid',
        ];
    }
}
