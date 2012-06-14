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
 * @see mr_fixture_enrollment
 */
require_once($CFG->dirroot.'/local/mr/framework/fixture/enrollment.php');

class mr_fixture_enrollment_test extends UnitTestCase {

    public static $includecoverage = array(
        'local/mr/framework/fixture/enrollment.php',
        'local/mr/framework/fixture/role.php'
    );

    public function setUp() {
        mr_fixture_manager::instance()
            ->set('user', new mr_fixture_user(array('username' => 'simpletest')))
            ->set('course', new mr_fixture_course(array('shortname' => 'simpletest')))
            ->set('role', new mr_fixture_role());
    }

    public function tearDown() {
        mr_fixture_manager::tearDown();
    }

    public function test_build() {
        $fm      = mr_fixture_manager::instance();
        $userid = $fm->get('user')->get('id');
        $roleid = $fm->get('role')->get('id');
        $context = context_course::instance($fm->get('course')->get('id'));

        $this->assertFalse(user_has_role_assignment($userid, $roleid, $context->id));
        $this->assertFalse(is_enrolled($context, $fm->get('user')->get_results()));

        $enrollment = new mr_fixture_enrollment($fm->get('course'), $fm->get('user'), enrol_get_plugin('manual'), $fm->get('role'));
        $enrollment->build();

        $this->assertTrue(user_has_role_assignment($userid, $roleid, $context->id));
        $this->assertTrue(is_enrolled($context, $fm->get('user')->get_results()));
    }

    public function test_destroy() {
        $fm      = mr_fixture_manager::instance();
        $context = context_course::instance($fm->get('course')->get('id'));

        $this->assertFalse(is_enrolled($context, $fm->get('user')->get_results()));

        $enrollment = new mr_fixture_enrollment($fm->get('course'), $fm->get('user'), enrol_get_plugin('manual'));
        $enrollment->build();

        $this->assertTrue(is_enrolled($context, $fm->get('user')->get_results()));

        $enrollment->destroy();

        $this->assertFalse(is_enrolled($context, $fm->get('user')->get_results()));
    }

    public function test_exists() {
        $fm         = mr_fixture_manager::instance();
        $enrollment = new mr_fixture_enrollment($fm->get('course'), $fm->get('user'), enrol_get_plugin('manual'));

        $this->assertFalse($enrollment->exists());

        $enrollment->build();

        $this->assertTrue($enrollment->exists());

        $enrollment->destroy();

        $this->assertFalse($enrollment->exists());
    }

    public function test_get_results() {
        global $DB;

        $fm         = mr_fixture_manager::instance();
        $enrollment = new mr_fixture_enrollment($fm->get('course'), $fm->get('user'), enrol_get_plugin('manual'));

        $this->assertEqual($enrollment->get_results(), new stdClass);

        $enrollment->build();

        $record = $DB->get_record_sql("
            SELECT ue.*
              FROM {user_enrolments} ue
              JOIN {enrol} e ON e.id = ue.enrolid AND e.courseid = ?
             WHERE ue.userid = ?
               AND e.enrol = ?
        ", array($fm->get('course')->get('id'), $fm->get('user')->get('id'), 'manual'), MUST_EXIST);

        $this->assertEqual($enrollment->get_results(), $record);

        $enrollment->destroy();

        $this->assertEqual($enrollment->get_results(), new stdClass);
    }

    public function test_create_instance() {
        global $DB;

        $fm      = mr_fixture_manager::instance();
        $context = context_course::instance($fm->get('course')->get('id'));

        $enroll = enrol_get_plugin('manual');
        if ($instance = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $fm->get('course')->get('id')))) {
            $enroll->delete_instance($instance);
        }
        $this->assertFalse(is_enrolled($context, $fm->get('user')->get_results()));

        $enrollment = new mr_fixture_enrollment($fm->get('course'), $fm->get('user'), $enroll);
        $enrollment->build();

        $this->assertTrue(is_enrolled($context, $fm->get('user')->get_results()));
    }
}