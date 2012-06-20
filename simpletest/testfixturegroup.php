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
 * @see mr_fixture_group
 */
require_once($CFG->dirroot.'/local/mr/framework/fixture/group.php');

class mr_fixture_group_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/fixture/group.php');

    public function setUp() {
        mr_fixture_manager::instance()
            ->set('course', new mr_fixture_course(array('shortname' => 'simpletest')));
    }

    public function tearDown() {
        mr_fixture_manager::tearDown();
    }

    public function test_build() {
        global $DB;

        $fm       = mr_fixture_manager::instance();
        $courseid = $fm->get('course')->get('id');
        $params   = array('name' => 'simpletest', 'courseid' => $courseid);

        $this->assertFalse($DB->record_exists('groups', $params));

        $group = new mr_fixture_group($fm->get('course'), $params);
        $group->build();

        $this->assertTrue($DB->record_exists('groups', $params));
    }

    public function test_destroy() {
        global $DB;

        $fm       = mr_fixture_manager::instance();
        $courseid = $fm->get('course')->get('id');
        $params   = array('name' => 'simpletest', 'courseid' => $courseid);

        $this->assertFalse($DB->record_exists('groups', $params));

        $group = new mr_fixture_group($fm->get('course'), $params);
        $group->build();

        $this->assertTrue($DB->record_exists('groups', $params));

        $group->destroy();

        $this->assertFalse($DB->record_exists('groups', $params));
    }

    public function test_exists() {
        $fm       = mr_fixture_manager::instance();
        $params   = array('name' => 'simpletest', 'courseid' => $fm->get('course')->get('id'));
        $group    = new mr_fixture_group($fm->get('course'), $params);

        $this->assertFalse($group->exists());

        $group->build();

        $this->assertTrue($group->exists());

        $group->destroy();

        $this->assertFalse($group->exists());
    }

    public function test_get_results() {
        global $DB;

        $fm     = mr_fixture_manager::instance();
        $params = array('name' => 'simpletest', 'courseid' => $fm->get('course')->get('id'));
        $group  = new mr_fixture_group($fm->get('course'), $params);

        $this->assertEqual($group->get_results(), new stdClass);

        $group->build();

        $record = $DB->get_record('groups', $params, '*', MUST_EXIST);
        $this->assertEqual($group->get_results(), $record);

        $group->destroy();

        $this->assertEqual($group->get_results(), new stdClass);
    }
}