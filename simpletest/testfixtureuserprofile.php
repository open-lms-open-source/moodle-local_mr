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
 * @see mr_fixture_user_profile
 */
require_once($CFG->dirroot.'/local/mr/framework/fixture/user/profile.php');

/**
 * @see mr_fixture_user_profile_category
 */
require_once($CFG->dirroot.'/local/mr/framework/fixture/user/profile/category.php');

class mr_fixture_user_profile_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/fixture/user/profile.php');

    public function setUp() {
        mr_fixture_manager::instance()->set('cat', new mr_fixture_user_profile_category());
    }

    public function tearDown() {
        mr_fixture_manager::tearDown();
    }

    public function test_build() {
        global $DB;

        $this->assertFalse($DB->record_exists('user_info_field', array('shortname' => 'simpletest')));

        $field = new mr_fixture_user_profile(mr_fixture_manager::instance()->get('cat'), array('shortname'  => 'simpletest'));
        $field->build();

        $this->assertTrue($DB->record_exists('user_info_field', array('shortname' => 'simpletest')));
    }

    public function test_destroy() {
        global $DB;

        $this->assertFalse($DB->record_exists('user_info_field', array('shortname' => 'simpletest')));

        $field = new mr_fixture_user_profile(mr_fixture_manager::instance()->get('cat'), array('shortname'  => 'simpletest'));
        $field->build();

        $field->destroy();

        $this->assertFalse($DB->record_exists('user_info_field', array('shortname' => 'simpletest')));
    }

    public function test_exists() {
        $field = new mr_fixture_user_profile(mr_fixture_manager::instance()->get('cat'), array('shortname'  => 'simpletest'));

        $this->assertFalse($field->exists());

        $field->build();

        $this->assertTrue($field->exists());

        $field->destroy();

        $this->assertFalse($field->exists());
    }

    public function test_get_results() {
        global $DB;

        $field = new mr_fixture_user_profile(mr_fixture_manager::instance()->get('cat'), array('shortname'  => 'simpletest'));

        $this->assertEqual($field->get_results(), new stdClass);

        $field->build();

        $record = $DB->get_record('user_info_field', array('shortname' => 'simpletest'), '*', MUST_EXIST);
        $this->assertEqual($field->get_results(), $record);

        $field->destroy();

        $this->assertEqual($field->get_results(), new stdClass);
    }
}