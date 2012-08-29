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
 * @see mr_lock
 */
require_once($CFG->dirroot.'/local/mr/framework/lock.php');

class mr_lock_test extends UnitTestCase {

    public static $includecoverage = array(
        'local/mr/framework/bootstrap.php',
        'local/mr/framework/lock.php',
        'local/mr/framework/lock/abstract.php',
        'local/mr/framework/lock/redis.php',
    );

    protected $cfgserver;
    protected $cfgttl = null;

    function setUp() {
        global $CFG;

        $this->cfgserver = $CFG->local_mr_redis_server;

        if (!empty($CFG->local_mr_lock_default_timetolive)) {
            $this->cfgttl = $CFG->local_mr_lock_default_timetolive;
        }
    }

    function tearDown() {
        global $CFG;

        $CFG->local_mr_redis_server = $this->cfgserver;
        $CFG->local_mr_lock_default_timetolive = $this->cfgttl;
    }


    public function test_lock_and_release() {
        $lock = new mr_lock('mr_lock_simpletest');

        $this->assertTrue($lock->get());
        $this->assertTrue($lock->release());
    }

    public function test_bad_uniquekey() {
        $this->expectException('coding_exception');
        $lock = new mr_lock('&*^@(!');
    }
/*

 The shutdown function doesn't allow this to work anymore

    public function test_destruct() {
        $lock = new mr_lock('mr_lock_simpletest');

        $this->assertTrue($lock->get());

        unset($lock);

        // Make sure we can re-acquire
        $lock = new mr_lock('mr_lock_simpletest');

        $this->assertTrue($lock->get());
        $this->assertTrue($lock->release());
    }
*/
    public function test_multiple_lock() {
        $lock  = new mr_lock('mr_lock_simpletest');
        $lock2 = new mr_lock('mr_lock_simpletest');
        $lock3 = new mr_lock('mr_lock_simpletest');

        $this->assertTrue($lock->get());

        // Trying to mimic another script trying to get a lock and then going away
        $this->assertFalse($lock2->get());
        unset($lock2);

        // Try again, make sure $lock2 didn't destroy anything
        $this->assertFalse($lock3->get());
        unset($lock3);

        $this->assertTrue($lock->release());
    }

    public function test_timetolive() {
        global $CFG;

        $CFG->local_mr_lock_default_timetolive = 10;

        $lock  = new mr_lock('mr_lock_simpletest', 3);
        $lock2 = new mr_lock('mr_lock_simpletest');

        $this->assertTrue($lock->get());

        sleep(1);

        $this->assertFalse($lock2->get());

        sleep(4);

        $this->assertTrue($lock2->get());

        // We shouldn't be able to release/get because lock2 now has the lock
        $this->assertTrue($lock->release());
        $this->assertFalse($lock->get());

        $this->assertTrue($lock2->release());
    }

    public function test_misconfigured_site() {
        global $CFG;

        $CFG->local_mr_redis_server = '';

        $lock = new mr_lock('mr_lock_simpletest');
        $this->assertFalse($lock->get());
    }

    public function test_get_twice() {
        $lock = new mr_lock('mr_lock_simpletest');

        $this->assertTrue($lock->get());
        $this->assertTrue($lock->get());
        $this->assertTrue($lock->release());
    }
}