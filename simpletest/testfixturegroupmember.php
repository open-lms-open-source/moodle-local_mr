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
 * @see mr_fixture_group_member
 */
require_once($CFG->dirroot.'/local/mr/framework/fixture/group/member.php');

class mr_fixture_group_member_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/fixture/group/member.php');

    public function setUp() {
        $fm = mr_fixture_manager::instance();
        $fm->set('course', new mr_fixture_course(array('shortname' => 'simpletest')))
           ->set('user', new mr_fixture_user(array('username' => 'simpletest')))
           ->set('enroll', new mr_fixture_enrollment($fm->get('course'), $fm->get('user')))
           ->set('group', new mr_fixture_group($fm->get('course'), array('name' => 'simpletest')));
    }

    public function tearDown() {
        mr_fixture_manager::tearDown();
    }

    public function test_build() {
        $fm       = mr_fixture_manager::instance();
        $userid   = $fm->get('user')->get('id');
        $groupid  = $fm->get('group')->get('id');

        $this->assertFalse(groups_is_member($groupid, $userid));

        $gm = new mr_fixture_group_member($fm->get('group'), $fm->get('enroll'));
        $gm->build();

        $this->assertTrue(groups_is_member($groupid, $userid));
    }

    public function test_destroy() {
        $fm      = mr_fixture_manager::instance();
        $userid  = $fm->get('user')->get('id');
        $groupid = $fm->get('group')->get('id');

        $this->assertFalse(groups_is_member($groupid, $userid));

        $gm = new mr_fixture_group_member($fm->get('group'), $fm->get('enroll'));
        $gm->build();

        $this->assertTrue(groups_is_member($groupid, $userid));

        $gm->destroy();

        $this->assertFalse(groups_is_member($groupid, $userid));
    }

    public function test_exists() {
        $fm = mr_fixture_manager::instance();
        $gm = new mr_fixture_group_member($fm->get('group'), $fm->get('enroll'));

        $this->assertFalse($gm->exists());

        $gm->build();

        $this->assertTrue($gm->exists());

        $gm->destroy();

        $this->assertFalse($gm->exists());
    }

    public function test_get_results() {
        global $DB;

        $fm      = mr_fixture_manager::instance();
        $userid  = $fm->get('user')->get('id');
        $groupid = $fm->get('group')->get('id');
        $gm      = new mr_fixture_group_member($fm->get('group'), $fm->get('enroll'));

        $this->assertEqual($gm->get_results(), new stdClass);

        $gm->build();

        $record = $DB->get_record('groups_members', array('groupid' => $groupid, 'userid' => $userid), '*', MUST_EXIST);
        $this->assertEqual($gm->get_results(), $record);

        $gm->destroy();

        $this->assertEqual($gm->get_results(), new stdClass);
    }
}