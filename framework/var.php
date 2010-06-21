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
 */
class mr_var extends ArrayObject {

    /**
     * mr_var object provides storage for shared objects.
     *
     * @var mr_var
     */
    private static $_instance = NULL;

    /**
     * Retrieves the default mr_var instance.
     *
     * @return mr_var
     */
    public static function get_instance() {
        if (self::$_instance === NULL) {
            self::init();
        }
        return self::$_instance;
    }

    /**
     * Initialize the default mr_var instance.
     *
     * @return void
     */
    protected static function init() {
        if (self::$_instance === NULL) {
            // Automatically set mrconfig
            $config = get_config('local/mr');

            if (empty($config)) {
                $config = new stdClass;
            }
            self::$_instance = new mr_var(array('mrconfig' => $config));
        }
    }

    /**
     * Unset the default mr_var instance.
     *
     * @return void
     */
    public static function _unset_instance() {
        self::$_instance = NULL;
    }

    /**
     * Getter method, basically same as offsetGet().
     *
     * This method can be called from an object of type mr_var, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index - get the value associated with $index
     * @return mixed
     * @throws coding_exception if no entry is registerd for $index.
     */
    public static function get($index) {
        $instance = self::get_instance();

        if (!$instance->offsetExists($index)) {
            throw new coding_exception("No entry is registered for key '$index'");
        }
        return $instance->offsetGet($index);
    }

    /**
     * Setter method, basically same as offsetSet().
     *
     * This method can be called from an object of type mr_var, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index The location in the ArrayObject in which to store
     *   the value.
     * @param mixed $value The object to store in the ArrayObject.
     * @return void
     */
    public static function set($index, $value) {
        $instance = self::get_instance();
        $instance->offsetSet($index, $value);
    }

    /**
     * Returns TRUE if the $index is a named value in mr_var,
     * or FALSE if $index was not found in mr_var.
     *
     * @param  string $index
     * @return boolean
     */
    public static function exists($index) {
        if (self::$_instance === null) {
            return false;
        }
        return self::$_instance->offsetExists($index);
    }

    /**
     * Constructs a parent ArrayObject with default
     * ARRAY_AS_PROPS to allow acces as an object
     *
     * @param array $array data array
     * @param integer $flags ArrayObject flags
     */
    public function __construct($array = array(), $flags = parent::ARRAY_AS_PROPS) {
        parent::__construct($array, $flags);
    }

    /**
     * @param string $index
     * @returns mixed
     *
     * Workaround for http://bugs.php.net/bug.php?id=40442 (ZF-960).
     */
    public function offsetExists($index) {
        return array_key_exists($index, $this);
    }
}