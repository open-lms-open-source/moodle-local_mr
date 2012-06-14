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
 * @see mr_fixture_manager
 */
require_once($CFG->dirroot.'/local/mr/framework/fixture/manager.php');

class mr_fixture_manager_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/fixture/manager.php');

    public function tearDown() {
        global $DB;

        if ($course = $DB->get_record('course', array('shortname' => 'simpletest'))) {
            delete_course($course, false);
        }
        if ($course = $DB->get_record('course', array('shortname' => 'simpletest2'))) {
            delete_course($course, false);
        }
        if ($course = $DB->get_record('course', array('shortname' => 'simpletest3'))) {
            delete_course($course, false);
        }
    }

    public function test_exists_set_get() {
        $course = new mr_fixture_course(array(
            'shortname' => 'simpletest',
            'idnumber'  => 'simpletest',
        ));

        $manager = new mr_fixture_manager();

        $this->assertFalse($manager->has('course'));

        $manager->set('course', $course);

        $this->assertTrue($manager->has('course'));

        // Manager creates them...
        $this->assertTrue($course->exists());

        $this->assertIdentical($manager->get('course'), $course);
    }

    public function test_set_twice() {
        $course = new mr_fixture_course(array(
            'shortname' => 'simpletest',
            'idnumber'  => 'simpletest',
        ));

        $manager = new mr_fixture_manager();
        $manager->set('course', $course);

        $this->expectException('coding_exception');
        $manager->set('course', $course);
    }

    public function test_invalid_get() {
        $manager = new mr_fixture_manager();
        $this->expectException('coding_exception');
        $manager->get('course');
    }

    public function test_destroy() {
        $course = new mr_fixture_course(array(
            'shortname' => 'simpletest',
            'idnumber'  => 'simpletest',
        ));

        $manager = new mr_fixture_manager();
        $manager->set('course', $course);

        $this->assertTrue($course->exists());

        $manager->destroy();

        $this->assertFalse($course->exists());
    }

    public function test_tearDown() {
        $course = new mr_fixture_course(array(
            'shortname' => 'simpletest',
            'idnumber'  => 'simpletest',
        ));

        $manager = new mr_fixture_manager();
        $manager->set('course', $course);

        $course2 = new mr_fixture_course(array(
            'shortname' => 'simpletest2',
            'idnumber'  => 'simpletest2',
        ));

        $manager2 = new mr_fixture_manager();
        $manager2->set('course', $course2);

        $course3 = new mr_fixture_course(array(
            'shortname' => 'simpletest3',
            'idnumber'  => 'simpletest3',
        ));

        mr_fixture_manager::instance()->set('course', $course3);

        mr_fixture_manager::tearDown();

        $this->assertFalse($course->exists());
        $this->assertFalse($course2->exists());
        $this->assertFalse($course3->exists());
    }
}