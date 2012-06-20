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
 * @see mr_fixture_role_assignment
 */
require_once($CFG->dirroot.'/local/mr/framework/fixture/role/assignment.php');

class mr_fixture_role_assignment_test extends UnitTestCase {

    public static $includecoverage = array(
        'local/mr/framework/fixture/role/assignment.php',
        'local/mr/framework/fixture/role.php'
    );

    public function setUp() {
        $fm = mr_fixture_manager::instance();
        $fm->set('course', new mr_fixture_course(array('shortname' => 'simpletest')))
            ->set('user', new mr_fixture_user(array('username' => 'simpletest')))
            ->set('role', new mr_fixture_role('student'));
    }

    public function tearDown() {
        mr_fixture_manager::tearDown();
    }

    public function test_build() {
        $fm        = mr_fixture_manager::instance();
        $userid    = $fm->get('user')->get('id');
        $roleid    = $fm->get('role')->get('id');
        $contextid = context_course::instance($fm->get('course')->get('id'))->id;

        $this->assertFalse(user_has_role_assignment($userid, $roleid, $contextid));

        $ra = new mr_fixture_role_assignment($fm->get('role'), $fm->get('user'), $fm->get('course'));
        $ra->build();

        $this->assertTrue(user_has_role_assignment($userid, $roleid, $contextid));
    }

    public function test_destroy() {
        $fm        = mr_fixture_manager::instance();
        $userid    = $fm->get('user')->get('id');
        $roleid    = $fm->get('role')->get('id');
        $contextid = context_course::instance($fm->get('course')->get('id'))->id;

        $this->assertFalse(user_has_role_assignment($userid, $roleid, $contextid));

        $ra = new mr_fixture_role_assignment($fm->get('role'), $fm->get('user'), $fm->get('course'));
        $ra->build();

        $this->assertTrue(user_has_role_assignment($userid, $roleid, $contextid));

        $ra->destroy();

        $this->assertFalse(user_has_role_assignment($userid, $roleid, $contextid));
    }

    public function test_exists() {
        $fm = mr_fixture_manager::instance();
        $ra = new mr_fixture_role_assignment($fm->get('role'), $fm->get('user'), $fm->get('course'));

        $this->assertFalse($ra->exists());

        $ra->build();

        $this->assertTrue($ra->exists());

        $ra->destroy();

        $this->assertFalse($ra->exists());
    }

    public function test_get_results() {
        global $DB;

        $fm        = mr_fixture_manager::instance();
        $userid    = $fm->get('user')->get('id');
        $roleid    = $fm->get('role')->get('id');
        $contextid = context_course::instance($fm->get('course')->get('id'))->id;
        $ra        = new mr_fixture_role_assignment($fm->get('role'), $fm->get('user'), $fm->get('course'));

        $this->assertEqual($ra->get_results(), new stdClass);

        $ra->build();

        $record = $DB->get_record('role_assignments', array('roleid' => $roleid, 'contextid' => $contextid, 'userid' => $userid), '*', MUST_EXIST);
        $this->assertEqual($ra->get_results(), $record);

        $ra->destroy();

        $this->assertEqual($ra->get_results(), new stdClass);
    }
}