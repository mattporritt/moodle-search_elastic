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

namespace search_elastic\local\model;

use core\persistent;

/**
 * Error persistent model for Elasticsearch indexing errors.
 *
 * @package    search_elastic
 * @author     Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright  2025 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class error extends persistent {
    /** @var string The table name. */
    const TABLE = 'search_elastic_errors';

    /** Error type for indexing. */
    const TYPE_INDEXING = 'indexing';

    /** Error type for Tika-related errors. */
    const TYPE_TIKA = 'tika';

    /** For errors currently being processed. */
    const STATUS_RETRYING = 'retrying';

    /** Exceeded the max retry limit. */
    const STATUS_FAILED = 'failed';

    /** Content no longer exists. */
    const STATUS_OBSOLETE = 'obsolete';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'docid' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'itemid' => [
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'contextid' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'areaid' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'errortype' => [
                'type' => PARAM_ALPHA,
                'null' => NULL_NOT_ALLOWED,
                'choices' => [
                    self::TYPE_INDEXING,
                    self::TYPE_TIKA,
                ],
            ],
            'errormessage' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'retrycount' => [
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
                'default' => 0,
            ],
            'status' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
                'default' => self::STATUS_FAILED,
                'choices' => [
                    self::STATUS_RETRYING,
                    self::STATUS_FAILED,
                    self::STATUS_OBSOLETE,
                ],
            ],
            'timecreated' => [
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'timemodified' => [
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'contentmodified' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'parentid' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
        ];
    }

    /**
     * Mark this error as failed.
     */
    public function mark_failed() {
        $this->set('status', self::STATUS_FAILED);
        $this->save();
    }

    /**
     * Mark this error as retrying.
     */
    public function mark_retrying() {
        $this->set('status', self::STATUS_RETRYING);
        $this->save();
    }

    /**
     * Mark error as obsolete (content no longer exists).
     */
    public function mark_obsolete() {
        $this->set('status', self::STATUS_OBSOLETE);
        $this->save();
    }

    /**
     * Increment the retry count.
     */
    public function increment_retry() {
        $newcount = $this->get('retrycount') + 1;
        $this->set('retrycount', $newcount);
        $this->save();
    }

    /**
     * Check if this error can be retried.
     *
     * @return bool
     */
    public function can_retry(): bool {
        return $this->get('status') !== self::STATUS_OBSOLETE;
    }
}
