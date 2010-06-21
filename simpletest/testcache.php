<?php


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot.'/local/mr/framework/cache.php');

class mr_cache_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/cache.php');

    /*
    public function setUp() {
        $this->cache = new mr_cache('test/mr/cache');
    }

    public function tearDown() {
        $this->cache = NULL;
    }

    public function test_cache() {
        $cache = new mr_cache('test/mr/cache');

        $this->assertFalse($cache->test('key'));
        $this->assertTrue($cache->save('value', 'key'));
        $this->assertTrue($cache->test('key'));
        $this->assertTrue($cache->save('value', 'key'));
        $this->assertEqual($cache->load('key'), 'value');
        $this->assertTrue($cache->touch('key', 60));
        $this->assertTrue($cache->remove('key'));
        $this->assertFalse($cache->test('key'));
    }

    public function test_clean() {
        $cache = new mr_cache('test/mr/cache');

        $this->assertTrue($cache->save('value', 'key1'));
        $this->assertTrue($cache->save('value', 'key2'));
        $this->assertTrue($cache->save('value', 'key3'));
        $this->assertTrue($cache->clean());
        $this->assertFalse($cache->test('key1'));
        $this->assertFalse($cache->test('key2'));
        $this->assertFalse($cache->test('key3'));
    }
    */
}