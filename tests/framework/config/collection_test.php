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

require_once(dirname(dirname(__DIR__)).'/bootstrap.php');

/**
 * Test mr_config_collection
 *
 * @package mr
 * @author Mark Nielsen
 */
class mr_config_collection_test extends basic_testcase {

    public function test_count() {
        $collection = new mr_config_collection();
        $collection->add(new mr_config('foo', 'bar'));
        $collection->add(new mr_config('bat', 'baz'));

        $this->assertCount(2, $collection, 'The collection can be counted');
    }

    public function test_add() {
        $config = new mr_config('foo', 'bar');

        $storage = $this->createMock('mr_config_storage_interface');
        $storage->expects($this->once())
            ->method('read')
            ->with($this->equalTo($config));

        $collection = new mr_config_collection();
        $collection->set_storage($storage);
        $collection->add($config);
    }

    public function test_iterator() {
        $config1    = new mr_config('foo', 'bar');
        $config2    = new mr_config('bat', 'baz');
        $expected   = array(array('foo', $config1), array('bat', $config2));
        $collection = new mr_config_collection();
        $collection->add($config1);
        $collection->add($config2);

        foreach ($collection as $key => $config) {
            list($expectedkey, $expectedconfig) = array_shift($expected);

            $this->assertEquals($expectedkey, $key);
            $this->assertSame($expectedconfig, $config);
        }
        $this->assertEmpty($expected, 'Did not find all of our configs');
    }

    public function test_all() {
        $config1    = new mr_config('foo', 'bar');
        $config2    = new mr_config('bat', 'baz');
        $expected   = array('foo' => $config1, 'bat' => $config2);
        $collection = new mr_config_collection();
        $collection->add($config1);
        $collection->add($config2);

        $this->assertEquals($expected, $collection->all());
    }

    public function test_get() {
        $collection = new mr_config_collection();
        $collection->add(new mr_config('foo', 'bar'));
        $collection->add(new mr_config('bat', 'baz'));

        $this->assertEquals('bar', $collection->get('foo'), 'Can retrieve config from collection');
        $this->assertEquals('baz', $collection->get('bat'), 'Can retrieve config from collection');
    }

    /**
     * @expectedException coding_exception
     */
    public function test_bad_get() {
        $collection = new mr_config_collection();
        $collection->get('foo');
    }

    public function test_set() {
        $config  = new mr_config('foo', 'bar');
        $storage = $this->createMock('mr_config_storage_interface');
        $storage->expects($this->once())
            ->method('write')
            ->with($this->equalTo($config));

        $collection = new mr_config_collection();
        $collection->set_storage($storage);
        $collection->add($config);

        $this->assertEquals('bar', $config->get_value());
        $collection->set('foo', 'bat');
        $this->assertEquals('bat', $config->get_value(), 'The config has been updated');
        $this->assertEquals('bat', $collection->get('foo'), 'The the collection reflectes the updated value');
    }

    /**
     * @expectedException coding_exception
     */
    public function test_bad_set() {
        $collection = new mr_config_collection();
        $collection->set('foo', 'bat');
    }

    public function test_remove() {
        $config  = new mr_config('foo', 'bar');
        $storage = $this->createMock('mr_config_storage_interface');
        $storage->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($config));

        $collection = new mr_config_collection();
        $collection->set_storage($storage);
        $collection->add($config);
        $collection->add(new mr_config('bat', 'baz'));

        $this->assertTrue($collection->has('foo'), 'The collection should have this');
        $this->assertTrue($collection->has('bat'), 'The collection should have this');

        $collection->remove('foo');

        $this->assertFalse($collection->has('foo'), 'The collection should not have this');
        $this->assertTrue($collection->has('bat'), 'The collection should have this');
    }

    public function test_has() {
        $collection = new mr_config_collection();
        $collection->add(new mr_config('foo', 'bar'));
        $collection->add(new mr_config('bat', 'baz'));

        $this->assertTrue($collection->has('foo'), 'The collection should have this');
        $this->assertTrue($collection->has('bat'), 'The collection should have this');
        $this->assertFalse($collection->has('hat'), 'The collection should not have this');
    }

    /**
     * @expectedException coding_exception
     */
    public function test_add_duplicate() {
        $collection = new mr_config_collection();
        $collection->add(new mr_config('foo', 'bar'));
        $collection->add(new mr_config('foo', 'bat'));
    }
}