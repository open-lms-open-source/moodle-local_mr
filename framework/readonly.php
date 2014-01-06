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
 * MR Read Only
 *
 * If a class extends this class, and has a data member
 * named $foo, then one can get $foo with get_foo()
 *
 * It is handy to extend this class for classes
 * that implement renderable so that the rendering
 * method can access variables without being able
 * to manipulate them.
 *
 * @package mr
 * @author Mark Nielsen
 */
abstract class mr_readonly {
    /**
     * Provides dynamic get_{dataMember}() method access
     *
     * @param string $name Method name which should be get_{dataMember}()
     * @param array $arguments Should be empty, no args needed
     * @return mixed
     * @throws coding_exception
     */
    public function __call($name, $arguments) {
        $parts = explode('_', $name, 2);

        if (!empty($parts[0]) and $parts[0] == 'get' and !empty($parts[1])) {
            $member = $parts[1];
            if (property_exists($this, $member)) {
                return $this->$member;
            }
        }
        throw new coding_exception("Method $name does not exist in class ".get_class($this));
    }
}