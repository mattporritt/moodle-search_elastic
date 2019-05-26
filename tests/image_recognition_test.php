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
require_once($CFG->dirroot . '/search/engine/elastic/tests/fixtures/aws_rekognition.php');

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
        $this->resetAfterTest();
        global $CFG;
        $config = new \stdClass();
        $config->rekregion = 'us-west-2';
        $config->rekkeyid = '12345';
        $config->reksecretkey = 'dfadfdf';
        $config->maxlabels = '1';
        $config->minconfidence = '90';

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

        // Mock out thw AWS Rekognition client and response.
        // Add missing data to stub record object.
        $builder = $this->getMockBuilder('\search_elastic\enrich\image\rekognition');
        $builder->setMethods(array('get_rekognition_client'));
        $builder->setConstructorArgs(array($config));
        $stub = $builder->getMock();

        $rekognition = new MockRekognition;
        $stub->method('get_rekognition_client')->willReturn($rekognition);

        $filetext = $stub->analyze_file($file);
        $this->assertEquals('blackthecolor', $filetext);
    }

}
