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
 * MR Plugin
 *
 * Very basic plugin base class.  Primary
 * purpose is to provide a human readable name
 * and coding plugin name.
 *
 * Plugin instances can be managed with mr_helper_load::plugin()
 *
 * @author Mark Nielsen
 * @package mr
 * @example plugin/base/class.php Base plugin class
 * @example plugin/one/class.php Plugin class, extending base plugin class
 * @see mr_helper_load::plugin()
 * @todo Make $this->helper more available?
 */
abstract class mr_plugin {
    /**
     * Passed to get_string calls.
     *
     * @return string
     */
    abstract public function get_component();

    /**
     * Return a human readable name of the plugin
     *
     * @return string
     */
    public function name() {
        return get_string('plugin-'.$this->type(), $this->get_component());
    }

    /**
     * Returns the plugin's name based on class name
     *
     * @return string
     */
    public function type() {
        $classparts = explode('_', get_class($this));

        // Burn the word class
        array_pop($classparts);

        return array_pop($classparts);
    }
}