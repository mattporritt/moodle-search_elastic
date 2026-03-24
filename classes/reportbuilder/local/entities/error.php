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

namespace search_elastic\reportbuilder\local\entities;

use core_search\manager;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\{date, select, text, number};
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\{column, filter};
use search_elastic\local\model\error as error_model;
use lang_string;
use stdClass;
use html_writer;

/**
 * Report builder entity for Elasticsearch indexing errors.
 *
 * @package     search_elastic
 * @copyright   2025 Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class error extends base {
    /**
     * Returns the default table aliases.
     *
     * @return array
     */
    protected function get_default_tables(): array {
        return ['search_elastic_errors'];
    }

    /**
     * Returns the default title for this entity.
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('indexingerrors', 'search_elastic');
    }

    /**
     * Initialises the entity.
     *
     * @return base
     */
    public function initialise(): base {
        foreach ($this->get_all_columns() as $column) {
            $this->add_column($column);
        }

        foreach ($this->get_all_filters() as $filter) {
            $this->add_filter($filter);
        }

        return $this;
    }

    /**
     * Returns list of available columns.
     *
     * @return array
     */
    protected function get_all_columns(): array {
        $alias = $this->get_table_alias('search_elastic_errors');

        $columns[] = (new column(
            'docid',
            new lang_string('documentid', 'search_elastic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.docid")
            ->add_callback(static function ($value, stdClass $row): string {
                return html_writer::tag('code', s($value));
            })
            ->set_is_sortable(true);

        $columns[] = (new column(
            'areaid',
            new lang_string('areaid', 'search_elastic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.areaid")
            ->add_callback(static function ($value, stdClass $row): string {
                // Get search area display name.
                $searchareas = manager::get_search_areas_list(true);
                return isset($searchareas[$value]) ? $searchareas[$value]->get_visible_name() : $value;
            })
            ->set_is_sortable(true);

        $typeoptions = $this->get_error_type_options();
        $columns[] = (new column(
            'errortype',
            new lang_string('errortype', 'search_elastic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.errortype")
            ->add_callback(static function ($value, stdClass $row) use ($typeoptions): string {
                return $typeoptions[$row->errortype] ?? $row->errortype;
            })
            ->set_is_sortable(true);

        $columns[] = (new column(
            'errormessage',
            new lang_string('errormessage', 'search_elastic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.errormessage")
            ->add_callback(static function ($value, stdClass $row): string {
                return s($value);
            });

        $statusoptions = $this->get_status_options();
        $columns[] = (new column(
            'status',
            new lang_string('status', 'search_elastic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.status")
            ->add_callback(static function ($value, stdClass $row) use ($statusoptions): string {
                $statustext = $statusoptions[$row->status] ?? $row->status;

                $statusclasses = [
                    error_model::STATUS_RETRYING => 'badge-info',
                    error_model::STATUS_FAILED => 'badge-danger',
                    error_model::STATUS_OBSOLETE => 'badge-secondary',
                ];

                $class = 'badge ' . ($statusclasses[$row->status] ?? 'badge-secondary');
                return html_writer::tag('span', $statustext, ['class' => $class]);
            })
            ->set_is_sortable(true);

        $columns[] = (new column(
            'retrycount',
            new lang_string('retrycount', 'search_elastic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.retrycount")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'timemodified',
            new lang_string('timemodified', 'search_elastic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$alias}.timemodified")
            ->add_callback([format::class, 'userdate'])
            ->set_is_sortable(true);

        $columns[] = (new column(
            'contentmodified',
            new lang_string('contentmodified', 'search_elastic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$alias}.contentmodified")
            ->add_callback(static function ($value, stdClass $row): string {
                return $value ? userdate($value) : '-';
            })
            ->set_is_sortable(true);

        $columns[] = (new column(
            'parentid',
            new lang_string('parentid', 'search_elastic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.parentid")
            ->add_callback(static function ($value, stdClass $row): string {
                return $value ? html_writer::tag('code', s($row->parentid)) : '-';
            })
            ->set_is_sortable(true);

        return $columns;
    }

    /**
     * Return list of all available filters.
     *
     * @return []
     */
    protected function get_all_filters(): array {
        $alias = $this->get_table_alias('search_elastic_errors');

        // Document ID filter.
        $filters[] = (new filter(
            text::class,
            'docid',
            new lang_string('documentid', 'search_elastic'),
            $this->get_entity_name(),
            "{$alias}.docid"
        ))
            ->add_joins($this->get_joins());

        // Area ID filter.
        $areaoptions = [];
        $searchareas = manager::get_search_areas_list(true);
        foreach ($searchareas as $areaid => $searcharea) {
            $areaoptions[$areaid] = $searcharea->get_visible_name();
        }

        $filters[] = (new filter(
            select::class,
            'areaid',
            new lang_string('areaid', 'search_elastic'),
            $this->get_entity_name(),
            "{$alias}.areaid"
        ))
            ->add_joins($this->get_joins())
            ->set_options($areaoptions);

        // Error type filter.
        $typeoptions = $this->get_error_type_options();
        $filters[] = (new filter(
            select::class,
            'errortype',
            new lang_string('errortype', 'search_elastic'),
            $this->get_entity_name(),
            "{$alias}.errortype"
        ))
            ->add_joins($this->get_joins())
            ->set_options($typeoptions);

        // Status filter.
        $statusoptions = $this->get_status_options();
        $filters[] = (new filter(
            select::class,
            'status',
            new lang_string('status', 'search_elastic'),
            $this->get_entity_name(),
            "{$alias}.status"
        ))
            ->add_joins($this->get_joins())
            ->set_options($statusoptions);

        // Retry count filter.
        $filters[] = (new filter(
            number::class,
            'retrycount',
            new lang_string('retrycount', 'search_elastic'),
            $this->get_entity_name(),
            "{$alias}.retrycount"
        ))
            ->add_joins($this->get_joins());

        // Error message filter.
        $filters[] = (new filter(
            text::class,
            'errormessage',
            new lang_string('errormessage', 'search_elastic'),
            $this->get_entity_name(),
            "{$alias}.errormessage"
        ))
            ->add_joins($this->get_joins());

        // Timemodified filter.
        $filters[] = (new filter(
            date::class,
            'timemodified',
            new lang_string('timemodified', 'search_elastic'),
            $this->get_entity_name(),
            "{$alias}.timemodified"
        ))
            ->add_joins($this->get_joins());

        // Content modified filter.
        $filters[] = (new filter(
            date::class,
            'contentmodified',
            new lang_string('contentmodified', 'search_elastic'),
            $this->get_entity_name(),
            "{$alias}.contentmodified"
        ))
            ->add_joins($this->get_joins());

        // Parent ID filter.
        $filters[] = (new filter(
            text::class,
            'parentid',
            new lang_string('parentid', 'search_elastic'),
            $this->get_entity_name(),
            "{$alias}.parentid"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }

    /**
     * Returns an array of error type options.
     *
     * @return array
     */
    private function get_error_type_options(): array {
        return [
            error_model::TYPE_CHUNKING => get_string('type_chunking', 'search_elastic'),
            error_model::TYPE_INDEXING => get_string('type_indexing', 'search_elastic'),
            error_model::TYPE_TIKA => get_string('type_tika', 'search_elastic'),
        ];
    }

    /**
     * Returns an array of status options.
     *
     * @return array
     */
    private function get_status_options(): array {
        return [
            error_model::STATUS_RETRYING => get_string('status_retrying', 'search_elastic'),
            error_model::STATUS_FAILED => get_string('status_failed', 'search_elastic'),
            error_model::STATUS_OBSOLETE => get_string('status_obsolete', 'search_elastic'),
        ];
    }
}
