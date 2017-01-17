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

require_once(dirname(dirname(dirname(__DIR__))).'/bootstrap.php');

/**
 * Test mr_config_storage_default
 *
 * @package mr
 * @author Mark Nielsen
 */
class mr_config_storage_default_test extends advanced_testcase {

    protected $component = 'phpunit_phpunit';

    protected function setUp() {
        $this->resetAfterTest();
    }

    public function test_read() {
        set_config('phpunit', 'value', $this->component);

        $config = $this->createMock('mr_config_interface');
        $config->expects($this->exactly(2))
            ->method('get_name')
            ->will($this->returnValue('phpunit'));

        $config->expects($this->once())
            ->method('get_default')
            ->will($this->returnValue(''));

        $config->expects($this->once())
            ->method('set_value')
            ->with($this->equalTo('value'));

        $storage = new mr_config_storage_default($this->component);
        $storage->read($config);
    }

    public function test_read_unserialize() {
        $value = array('bat' => 'baz');

        set_config('phpunit', serialize($value), $this->component);

        $config = $this->createMock('mr_config_interface');
        $config->expects($this->exactly(2))
            ->method('get_name')
            ->will($this->returnValue('phpunit'));

        $config->expects($this->once())
            ->method('get_default')
            ->will($this->returnValue(array()));

        $config->expects($this->once())
            ->method('set_value')
            ->with($this->equalTo($value));

        $storage = new mr_config_storage_default($this->component);
        $storage->read($config);
    }

    public function test_write() {
        $config = $this->createMock('mr_config_interface');
        $config->expects($this->once())
            ->method('get_name')
            ->will($this->returnValue('phpunit'));

        $config->expects($this->once())
            ->method('get_default')
            ->will($this->returnValue(''));

        $config->expects($this->once())
            ->method('get_value')
            ->will($this->returnValue('value'));

        $storage = new mr_config_storage_default($this->component);
        $storage->write($config);

        $this->assertNull($storage->get_cache());
        $this->assertEquals('value', get_config($this->component, 'phpunit'));
    }

    public function test_write_serialize() {
        $value = array('bat' => 'baz');

        $config = $this->createMock('mr_config_interface');
        $config->expects($this->once())
            ->method('get_name')
            ->will($this->returnValue('phpunit'));

        $config->expects($this->once())
            ->method('get_default')
            ->will($this->returnValue(array()));

        $config->expects($this->once())
            ->method('get_value')
            ->will($this->returnValue($value));

        $storage = new mr_config_storage_default($this->component);
        $storage->write($config);

        $this->assertNull($storage->get_cache());
        $this->assertEquals(serialize($value), get_config($this->component, 'phpunit'));
    }

    public function test_remove() {
        set_config('phpunit', 'value', $this->component);

        $config = $this->createMock('mr_config_interface');
        $config->expects($this->once())
            ->method('get_name')
            ->will($this->returnValue('phpunit'));

        $storage = new mr_config_storage_default($this->component);
        $storage->load_cache();
        $storage->remove($config);

        $this->assertFalse(get_config($this->component, 'phpunit'));
        $this->assertFalse(property_exists($storage->get_cache(), 'phpunit'), 'The config is no longer in the cache');
    }
}