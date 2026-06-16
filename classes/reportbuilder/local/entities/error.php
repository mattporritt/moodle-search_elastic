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
use html_writer;
use lang_string;
use moodle_url;
use stdClass;
use search_elastic\local\model\error as error_model;

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
                return isset($searchareas[$value]) ? $searchareas[$value]->get_visible_name() : s($value);
            })
            ->set_is_sortable(true);

        $columns[] = (new column(
            'links',
            new lang_string('links', 'search_elastic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_join("LEFT JOIN {context} rawctx ON rawctx.id = {$alias}.contextid")
            ->add_join("LEFT JOIN {course_modules} rawcm ON rawcm.id = rawctx.instanceid AND rawctx.contextlevel = " . CONTEXT_MODULE)
            ->add_join("LEFT JOIN {modules} rawmod ON rawmod.id = rawcm.module")
            ->add_join("LEFT JOIN {files} rawfile ON rawfile.id = {$alias}.fileid")
            ->add_field("rawctx.contextlevel", 'rawcontextlevel')
            ->add_field("rawctx.instanceid", 'rawinstanceid')
            ->add_field("rawcm.course", 'rawcourseid')
            ->add_field("rawcm.id", 'rawcmid')
            ->add_field("rawmod.name", 'rawmodulename')
            ->add_field("rawfile.contextid", 'rawfilecontextid')
            ->add_field("rawfile.component", 'rawfilecomponent')
            ->add_field("rawfile.filearea", 'rawfilefilearea')
            ->add_field("rawfile.itemid", 'rawfileitemid')
            ->add_field("rawfile.filepath", 'rawfilefilepath')
            ->add_field("rawfile.filename", 'rawfilefilename')
            ->add_callback(static function ($value, stdClass $row): string {
                return self::render_links($row);
            });

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
                return $typeoptions[$row->errortype] ?? s($row->errortype);
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
                $statustext = $statusoptions[$row->status] ?? s($row->status);

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

    /**
     * Render course, activity and file links for a row.
     *
     * @param stdClass $row
     * @return string
     */
    private static function render_links(stdClass $row): string {
        $links = [];

        if ($courseurl = self::get_course_url($row)) {
            $links[] = html_writer::link($courseurl, get_string('course'));
        }

        if ($activityurl = self::get_activity_url($row)) {
            $links[] = html_writer::link($activityurl, get_string('activity'));
        }

        if ($fileurl = self::get_file_url($row)) {
            $links[] = html_writer::link($fileurl, get_string('file'));
        }

        return $links ? implode(' | ', $links) : '-';
    }

    /**
     * Return the course URL for the row, if it can be resolved.
     *
     * @param stdClass $row
     * @return moodle_url|null
     */
    private static function get_course_url(stdClass $row): ?moodle_url {
        $contextlevel = isset($row->rawcontextlevel) ? (int)$row->rawcontextlevel : null;

        if ($contextlevel === CONTEXT_COURSE && !empty($row->rawinstanceid)) {
            return new moodle_url('/course/view.php', ['id' => (int)$row->rawinstanceid]);
        }

        if ($contextlevel === CONTEXT_MODULE && !empty($row->rawcourseid)) {
            return new moodle_url('/course/view.php', ['id' => (int)$row->rawcourseid]);
        }

        return null;
    }

    /**
     * Return the activity URL for the row, if it can be resolved.
     *
     * @param stdClass $row
     * @return moodle_url|null
     */
    private static function get_activity_url(stdClass $row): ?moodle_url {
        if (empty($row->rawcmid) || empty($row->rawmodulename)) {
            return null;
        }

        return new moodle_url('/mod/' . $row->rawmodulename . '/view.php', ['id' => (int)$row->rawcmid]);
    }

    /**
     * Return a direct file URL for the row, if it represents a file document.
     *
     * @param stdClass $row
     * @return moodle_url|null
     */
    private static function get_file_url(stdClass $row): ?moodle_url {
        if (empty($row->rawfilecontextid) || empty($row->rawfilefilename)) {
            return null;
        }

        return moodle_url::make_pluginfile_url(
            (int)$row->rawfilecontextid,
            $row->rawfilecomponent,
            $row->rawfilefilearea,
            (int)$row->rawfileitemid,
            $row->rawfilefilepath,
            $row->rawfilefilename
        );
    }
}
