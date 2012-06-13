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
 * @see mr_fixture_user
 */
require_once($CFG->dirroot.'/local/mr/framework/fixture/user.php');

class mr_fixture_user_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/fixture/user.php');

    public function tearDown() {
        global $DB;

        if ($user = $DB->get_record('user', array('username' => 'simpletest'))) {
            delete_user($user);
            $DB->delete_records('user', array('id' => $user->id));
        }
    }

    public function test_build() {
        global $DB;

        $this->assertFalse($DB->record_exists('user', array('username' => 'simpletest')));

        $user = new mr_fixture_user(array(
            'username' => 'simpletest',
            'idnumber'  => 'simpletest',
        ));
        $user->build();

        $this->assertTrue($DB->record_exists('user', array('username' => 'simpletest')));
    }

    public function test_destroy() {
        global $DB;

        $this->assertFalse($DB->record_exists('user', array('username' => 'simpletest')));

        $user = new mr_fixture_user(array(
            'username' => 'simpletest',
            'idnumber'  => 'simpletest',
        ));
        $user->build();

        $this->assertTrue($DB->record_exists('user', array('username' => 'simpletest')));

        $user->destroy();

        $this->assertFalse($DB->record_exists('user', array('username' => 'simpletest')));
    }

    public function test_exists() {
        $user = new mr_fixture_user(array(
            'username' => 'simpletest',
            'idnumber'  => 'simpletest',
        ));

        $this->assertFalse($user->exists());

        $user->build();

        $this->assertTrue($user->exists());

        $user->destroy();

        $this->assertFalse($user->exists());
    }

    public function test_get_results() {
        global $DB;

        $user = new mr_fixture_user(array(
            'username' => 'simpletest',
            'idnumber'  => 'simpletest',
        ));

        $this->assertEqual($user->get_results(), new stdClass);

        $user->build();

        $record = $DB->get_record('user', array('username' => 'simpletest'), '*', MUST_EXIST);
        $this->assertEqual($user->get_results(), $record);

        $user->destroy();

        $this->assertEqual($user->get_results(), new stdClass);
    }
}