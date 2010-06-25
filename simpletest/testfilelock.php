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
 * @see mr_file_lock
 */
require_once($CFG->dirroot.'/local/mr/framework/file/lock.php');

class mr_file_lock_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/file/lock.php');

    public function test_lock_and_release() {
        global $CFG;

        $lock = new mr_file_lock('mr_file_lock_test');

        $this->assertTrue($lock->get());
        $this->assertTrue(file_exists("$CFG->dataroot/mr_file_lock_test_lock.txt"));

        $lock->release();
        $this->assertFalse(file_exists("$CFG->dataroot/mr_file_lock_test_lock.txt"));
    }

    public function test_double_lock() {
        global $CFG;

        $lock  = new mr_file_lock('mr_file_lock_test');
        $lock2 = new mr_file_lock('mr_file_lock_test');

        $this->assertTrue($lock->get());
        $this->assertTrue(file_exists("$CFG->dataroot/mr_file_lock_test_lock.txt"));

        $this->assertFalse($lock2->get());

        $lock->release();
        $this->assertFalse(file_exists("$CFG->dataroot/mr_file_lock_test_lock.txt"));
    }

    public function test_destruct() {
        global $CFG;

        $lock = new mr_file_lock('mr_file_lock_test');

        $this->assertTrue($lock->get());
        $this->assertTrue(file_exists("$CFG->dataroot/mr_file_lock_test_lock.txt"));

        unset($lock);

        $this->assertFalse(file_exists("$CFG->dataroot/mr_file_lock_test_lock.txt"));

        // Make sure we can re-aquire
        $lock = new mr_file_lock('mr_file_lock_test');

        $this->assertTrue($lock->get());
        $lock->release();
        $this->assertFalse(file_exists("$CFG->dataroot/mr_file_lock_test_lock.txt"));
    }

    public function test_bad_uniquekey() {
        $this->expectException('coding_exception');
        $lock = new mr_file_lock('&*^@(!');
    }
}