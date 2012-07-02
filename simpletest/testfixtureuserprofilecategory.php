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
 * @see mr_fixture_user_profile_category
 */
require_once($CFG->dirroot.'/local/mr/framework/fixture/user/profile/category.php');

class mr_fixture_user_profile_category_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/fixture/user/profile/category.php');

    public function tearDown() {
        global $DB;

        $DB->delete_records('user_info_category', array('name' => 'simpletest'));
    }

    public function test_build() {
        global $DB;

        $this->assertFalse($DB->record_exists('user_info_category', array('name' => 'simpletest')));

        $cat = new mr_fixture_user_profile_category();
        $cat->build();

        $this->assertTrue($DB->record_exists('user_info_category', array('name' => 'simpletest')));
    }

    public function test_destroy() {
        global $DB;

        $this->assertFalse($DB->record_exists('user_info_category', array('name' => 'simpletest')));

        $cat = new mr_fixture_user_profile_category();
        $cat->build();

        $this->assertTrue($DB->record_exists('user_info_category', array('name' => 'simpletest')));

        $cat->destroy();

        $this->assertFalse($DB->record_exists('user_info_category', array('name' => 'simpletest')));
    }

    public function test_exists() {
        $cat = new mr_fixture_user_profile_category();

        $this->assertFalse($cat->exists());

        $cat->build();

        $this->assertTrue($cat->exists());

        $cat->destroy();

        $this->assertFalse($cat->exists());
    }

    public function test_get_results() {
        global $DB;

        $cat = new mr_fixture_user_profile_category();

        $this->assertEqual($cat->get_results(), new stdClass);

        $cat->build();

        $record = $DB->get_record('user_info_category', array('name' => 'simpletest'), '*', MUST_EXIST);
        $this->assertEqual($cat->get_results(), $record);

        $cat->destroy();

        $this->assertEqual($cat->get_results(), new stdClass);
    }
}