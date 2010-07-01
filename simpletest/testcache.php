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
 * @see mr_var
 */
require_once($CFG->dirroot.'/local/mr/framework/var.php');

/**
 * @see mr_cache
 */
require_once($CFG->dirroot.'/local/mr/framework/cache.php');

class mr_cache_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/cache.php');

    public function setUp() {
        $this->cache = new mr_cache('test/mr/cache');
    }

    public function tearDown() {
        $this->cache = NULL;
    }

    public function test_cache() {
        if (!defined('MR_CACHE_TEST')) {
            return;
        }
        $cache = new mr_cache('test/mr/cache');

        $this->assertFalse($cache->test('key'));
        $this->assertTrue($cache->save('value', 'key'));
        $this->assertTrue($cache->test('key'));
        $this->assertEqual($cache->load('key'), 'value');
        $this->assertTrue($cache->touch('key', 60));
        $this->assertTrue($cache->remove('key'));
        $this->assertFalse($cache->test('key'));
    }

    public function test_clean() {
        if (!defined('MR_CACHE_TEST')) {
            return;
        }
        $cache = new mr_cache('test/mr/cache');

        $this->assertTrue($cache->save('value', 'key1'));
        $this->assertTrue($cache->save('value', 'key2'));
        $this->assertTrue($cache->save('value', 'key3'));
        $this->assertTrue($cache->clean());
        $this->assertFalse($cache->test('key1'));
        $this->assertFalse($cache->test('key2'));
        $this->assertFalse($cache->test('key3'));
    }

    public function test_cache_object() {
        if (!defined('MR_CACHE_TEST')) {
            return;
        }
        $cache = new mr_cache('test/mr/cache');

        $object = new stdClass;
        $object->foo = 'bar';
        $object->bat = 'baz';

        $this->assertTrue($cache->save($object, 'objectkey'));
        $this->assertIdentical($cache->load('objectkey', true), $object);
        $this->assertTrue($cache->clean());
    }

    public function test_cache_broken() {
        if (!defined('MR_CACHE_TEST')) {
            return;
        }
        $old = mr_var::instance()->get('mrconfig')->cache_lifetime;
        mr_var::instance()->get('mrconfig')->cache_lifetime = 0;

        $cache = new mr_cache('test/mr/cache');

        $this->assertFalse($cache->test('key'));
        $this->assertTrue($cache->save('value', 'key'));
        $this->assertFalse($cache->test('key'));
        $this->assertFalse($cache->load('key'));
        $this->assertTrue($cache->touch('key', 60));
        $this->assertTrue($cache->remove('key'));

        mr_var::instance()->get('mrconfig')->cache_lifetime = $old;
    }
}