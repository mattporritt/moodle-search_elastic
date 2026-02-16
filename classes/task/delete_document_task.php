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

use core\task\adhoc_task;
use Exception;
use search_elastic\engine;

/**
 * Adhoc task to delete documents from Elasticsearch index.
 *
 * Used to asynchronously delete documents that are found to be deleted during search result
 * compilation, avoid performance impact on search.
 *
 * @package     search_elastic
 * @author      Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright   2026 Monash University (http://www.monash.edu)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_document_task extends adhoc_task {
    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        $data = $this->get_custom_data();
        if (empty($data->deletiondocs)) {
            mtrace('Delete document task: No documents provided.');
            return;
        }

        $deletiondocs = $data->deletiondocs;
        if (!is_array($deletiondocs)) {
            mtrace('Delete document task: Provided documents must be an array.');
            return;
        }

        mtrace('Delete document task: Deleting ' . count($deletiondocs) . ' document(s) from Elasticsearch.');

        try {
            $engine = new engine();

            // Group deletions by type.
            $chunkedorigins = [];
            $regulardocs = [];

            foreach ($deletiondocs as $doc) {
                // Convert to object if it's an array.
                if (is_array($doc)) {
                    $doc = (object)$doc;
                }

                if (empty($doc->docid)) {
                    mtrace('Delete document task: Missing docid in deletion.');
                    continue;
                }

                if (!empty($doc->is_chunk)) {
                    // This is a chunk - mark the original document for chunk deletion.
                    $chunkedorigins[$doc->docid] = true;
                } else {
                    // Regular document.
                    $regulardocs[] = $doc->docid;
                }
            }

            foreach (array_keys($chunkedorigins) as $originaldocid) {
                // Delete all chunks for this document.
                $engine->delete_by_original_id($originaldocid);

                // Also try to delete the original document ID if it exists.
                $engine->delete_by_id($originaldocid);
            }

            foreach ($regulardocs as $docid) {
                // Delete the document itself.
                $engine->delete_by_id($docid);
            }

            mtrace('Delete document task: Completed. Deleted ' . count($chunkedorigins) . ' chunked document(s) and ' .
                count($regulardocs) . ' regular document(s).');
        } catch (Exception $e) {
            debugging('Delete document task: Exception during deletion: ' . $e->getMessage(), DEBUG_DEVELOPER);

            // Re-throw so task will be retried.
            throw $e;
        }
    }
}
