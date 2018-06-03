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
 * Elastic search engine enrichment image rekognition unit tests.
 *
 * @package    search_elastic
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Elastic search engine enrichment image rekognition unit tests.
 *
 * @package     search_elastic
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_elastic_image_recognition_testcase extends advanced_testcase {

    /**
     * Test image with AWS rekgonition
     */
    public function test_export_image_file_for_engine() {
        global $CFG;
        set_config('imageindex', 1, 'search_elastic');

        // Create file to analyze.
        $fs = get_file_storage();
        $filerecord = array(
            'contextid' => 1,
            'component' => 'mod_test',
            'filearea' => 'search',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'testfile.png');
        $fileurl = $CFG->dirroot . '/search/engine/elastic/tests/pix/black.png';
        $file = $fs->create_file_from_pathname($filerecord, $fileurl);

        // Construct the search object.
        $rec = new \stdClass();
        $rec->content = "elastic";
        $area = new core_mocksearch\search\mock_search_area();
        $record = $this->generator->create_record($rec);
        $info = unserialize($record->info);

        // Mock out thw AWS Rekognition client and response.
        // Add missing data to stub record object.

        $builder = $this->getMockBuilder('\search_elastic\document');
        $builder->setMethods(array('get_rekognition_client'));
        $builder->setConstructorArgs(array('1', 'core_mocksearch', 'mock_search_area'));
        $stub = $builder->getMock();

        $stub->set('title', $info->title);
        $stub->set('content', $info->content);
        $stub->set('description1', $info->description1);
        $stub->set('description1', $info->description2);
        $stub->set('contextid', $info->contextid);
        $stub->set('courseid', $info->courseid);
        $stub->set('userid', $info->userid);
        $stub->set('owneruserid', $info->owneruserid);
        $stub->set('modified', $record->timemodified);

        $rekognition = new MockRekognition;
        $stub->method('get_rekognition_client')->willReturn($rekognition);

        $filearray = $stub->export_file_for_engine($file);
        $this->assertEquals('black', $filearray['filetext']);
    }

}
