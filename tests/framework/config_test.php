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

require_once(dirname(__DIR__).'/bootstrap.php');

/**
 * Test mr_config
 *
 * @package mr
 * @author Mark Nielsen
 */
class mr_config_test extends basic_testcase {
    public function test_construct() {
        $config = new mr_config('foo', 'bar');

        $this->assertEquals('bar', $config->get_value(), 'Construct sets the value to the passed default');
        $this->assertEquals('bar', $config->get_default(), 'Construct sets the default');
        $this->assertEquals('foo', $config->get_name(), 'Construct sets the name');
    }
}