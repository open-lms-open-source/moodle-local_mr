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
 * MR Model Abstract
 *
 * A model basic model.  You must define
 * all of your properties and get/set methods
 * for them.
 *
 * @author Mark Nielsen
 * @package mr
 * @example controller/model.php See this class in action
 */
abstract class mr_model_abstract {
    /**
     * @param array $options Set initial properties
     */
    public function __construct($options = array()) {
        $this->set_options($options);
    }

    /**
     * A way to bulk set model properties
     *
     * Will look for method set_{property} methods.
     *
     * @param array|object $options
     * @return mr_model_abstract
     */
    public function set_options($options) {
        foreach ($options as $name => $value) {
            $method = "set_$name";
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * A way to export model properties
     *
     * Will look for method get_{property} methods.
     *
     * @return stdClass
     */
    public function get_options() {
        $options = new stdClass();
        foreach (get_object_vars($this) as $name) {
            $method = "get_$name";
            if (method_exists($this, $method)) {
                $options->$name = $this->$method();
            }
        }
        return $options;
    }
}