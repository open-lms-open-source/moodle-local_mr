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
 * @see mr_bootstrap
 */
require_once($CFG->dirroot.'/local/mr/framework/bootstrap.php');

/**
 * @see mr_autoload
 */
require_once($CFG->dirroot.'/local/mr/framework/autoload.php');

class mr_bootstrap_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/bootstrap.php');

    public function test_bootstrap_startup() {
        // Have to shutdown first (I believe that simpletest kill autoload)
        mr_bootstrap::shutdown();
        mr_bootstrap::startup();

        $autoloads = spl_autoload_functions();
        $this->assertIsA($autoloads, 'array');

        $found = false;
        foreach ($autoloads as $autoload) {
            if (is_array($autoload) and $autoload[0] instanceof mr_autoload) {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    public function test_bootstrap_shutdown() {
        mr_bootstrap::startup();
        mr_bootstrap::shutdown();

        $autoloads = spl_autoload_functions();
        $this->assertIsA($autoloads, 'array');

        $found = false;
        foreach ($autoloads as $autoload) {
            if (is_array($autoload) and $autoload[0] instanceof mr_autoload) {
                $found = true;
            }
        }
        $this->assertFalse($found);
    }

    public function test_redis_connect_ping_close() {
        $redis = mr_bootstrap::redis();
        $this->assertIsA($redis, 'Redis');
        $this->assertEqual($redis->ping(), '+PONG');
        $this->assertTrue($redis->close());
    }

    public function test_redis_set_get_delete() {
        $redis = mr_bootstrap::redis();
        $this->assertTrue($redis->set('simpletest', 'foo'));
        $this->assertEqual($redis->get('simpletest'), 'foo');
        $this->assertEqual($redis->delete('simpletest'), 1);
        $this->assertTrue($redis->close());
    }

    public function test_zend_include_path() {
        global $CFG;

        mr_bootstrap::zend();

        $files = array(
            $CFG->dirroot.'/lib/zend/Zend/Validate/Barcode/Ean13.php',
        );
        if (is_dir($CFG->dirroot.'/local/mr/vendor/zend')) {
            $files[] = $CFG->dirroot.'/local/mr/vendor/zend/Zend/Controller/Request/Http.php';
        }
        if (is_dir($CFG->dirroot.'/search')) {
            $files[] = $CFG->dirroot.'/search/Zend/Exception.php';
        }
        $included = get_included_files();
        foreach ($files as $file) {
            $this->assertFalse(in_array($file, $included));
        }

        include_once 'Zend/Exception.php';
        include_once 'Zend/Controller/Request/Http.php';
        include_once 'Zend/Validate/Barcode/Ean13.php';

        $included = get_included_files();
        foreach ($files as $file) {
            $this->assertTrue(in_array($file, $included));
        }
    }
}