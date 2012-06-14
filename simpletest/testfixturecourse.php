<?php
/**
 * Moodlerooms Framework
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @copyright Copyright (c) 2009 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @package mr
 * @author Mark Nielsen
 */

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * @see mr_fixture_course
 */
require_once($CFG->dirroot.'/local/mr/framework/fixture/course.php');

class mr_fixture_course_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/fixture/course.php');

    public function tearDown() {
        global $DB;

        if ($course = $DB->get_record('course', array('shortname' => 'simpletest'))) {
            delete_course($course, false);
        }
    }

    public function test_build() {
        global $DB;

        $this->assertFalse($DB->record_exists('course', array('shortname' => 'simpletest')));

        $course = new mr_fixture_course(array(
            'shortname' => 'simpletest',
            'idnumber'  => 'simpletest',
        ));
        $course->build();

        $this->assertTrue($DB->record_exists('course', array('shortname' => 'simpletest')));
    }

    public function test_destroy() {
        global $DB;

        $this->assertFalse($DB->record_exists('course', array('shortname' => 'simpletest')));

        $course = new mr_fixture_course(array(
            'shortname' => 'simpletest',
            'idnumber'  => 'simpletest',
        ));
        $course->build();

        $this->assertTrue($DB->record_exists('course', array('shortname' => 'simpletest')));

        $course->destroy();

        $this->assertFalse($DB->record_exists('course', array('shortname' => 'simpletest')));
    }

    public function test_exists() {
        $course = new mr_fixture_course(array(
            'shortname' => 'simpletest',
            'idnumber'  => 'simpletest',
        ));

        $this->assertFalse($course->exists());

        $course->build();

        $this->assertTrue($course->exists());

        $course->destroy();

        $this->assertFalse($course->exists());
    }

    public function test_get_results() {
        global $DB;

        $course = new mr_fixture_course(array(
            'shortname' => 'simpletest',
            'idnumber'  => 'simpletest',
        ));

        $this->assertEqual($course->get_results(), new stdClass);

        $course->build();

        $record = $DB->get_record('course', array('shortname' => 'simpletest'), '*', MUST_EXIST);
        $this->assertEqual($course->get_results(), $record);

        $course->destroy();

        $this->assertEqual($course->get_results(), new stdClass);
    }
}