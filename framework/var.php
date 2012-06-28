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
 * MR Var
 *
 * Variable storage class.  Very similar to Zend_Registry
 * (http://framework.zend.com/manual/en/zend.registry.html).
 *
 * This does not replace the session nor does it replace
 * mr_cache.  This is a per PHP execution storage of variables.
 *
 * Note: index "mrconfig" is automatically set to the config
 * for local/mr/framework.  Please do not overrite with something else.
 *
 * @package mr
 * @author Mark Nielsen
 * @todo implement ArrayAccess
 */
class mr_var extends stdClass {

    /**
     * mr_var object provides storage for shared objects.
     *
     * @var mr_var
     */
    private static $_instance = NULL;

    /**
     * Get the global static instance of mr_var
     *
     * @return mr_var
     */
    public static function instance() {
        if (self::$_instance === NULL) {
            // Automatically set mrconfig
            $config = get_config('local/mr');

            if (empty($config)) {
                $config = new stdClass;
            }
            self::$_instance = new mr_var(array('mrconfig' => $config));
        }
        return self::$_instance;
    }

    /**
     * Setup with initial values
     *
     * @param array $init Initial values
     */
    public function __construct($init = array()) {
        $this->set($init);
    }

    /**
     * Set values
     *
     * @throws coding_exception
     * @param mixed $param Pass an array(name => value, etc) or ($name, $value)
     * @return mr_var
     */
    public function set() {
        $args = func_get_args();

        if (count($args) == 1) {
            $args = array_shift($args);

            if (!is_array($args)) {
                throw new coding_exception('Invalid call to method set: single arg is not an array');
            }
        } else if (count($args) == 2) {
            $args = array($args[0] => $args[1]);
        } else {
            throw new coding_exception('Invalid call to method set: invalid argument count');
        }
        foreach ($args as $name => $value) {
            $this->$name = $value;
        }
        return $this;
    }

    /**
     * Get a value at index
     *
     * @param string $index The index's value to fetch
     * @throws coding_exception
     * @return mr_var
     * @todo allow the passing of a default?
     */
    public function get($index) {
        if (!property_exists($this, $index)) {
            throw new coding_exception("No entry is registered for key '$index'");
        }
        return $this->$index;
    }

    /**
     * Check if an index exists
     *
     * @param string $index The index to check
     * @return mr_var
     */
    public function exists($index) {
        return property_exists($this, $index);
    }

    /**
     * Unset an index
     *
     * @param string $index The index to unset
     * @return mr_var
     */
    public function remove($index) {
        unset($this->$index);
        return $this;
    }
}