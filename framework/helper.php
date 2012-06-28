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
 * @see mr_helper_load
 */
require_once($CFG->dirroot.'/local/mr/framework/helper/load.php');

/**
 * MR Helper
 *
 * This class is namespace driven.  A namespace is a relative
 * directory path from Moodle's directory root. Examples:
 *  - local/mr/framework
 *  - blocks/helloworld
 *
 * This model controls access to classes defined
 * in local/mr/framework/helper/ or in /path/to/current/namespace/helper/
 * and those classes methods.
 *
 * Each namespace has its own copy of any helper class used and
 * for each namespace, a helper class is only instantiated once.
 *
 * The MR Helper can be used in two ways:
 *  1 mr_helper->HELPERNAME->HELPERMETHOD() - these are routed through __get()
 *  2 mr_helper->HELPERNAME() - these are routed through __call()
 *
 * Examples:
 * <code>
 * <?php
 *      $helper = new mr_helper('blocks/helloworld');
 *      $helper->world->say_hello(); // Call blocks_helloworld_helper_world::say_hello()
 *      $helper->world->direct();    // Call blocks_helloworld_helper_world::direct()
 *      $helper->world();            // Call blocks_helloworld_helper_world::direct() (Short cut)
 *
 *      // If a helper is not found in blocks/helloworld/helper/
 *      // then we will look in local/mr/framework/helper:
 *      $helper->buffer('foo');      // Call mr_helper_buffer::direct()
 * ?>
 * </code>
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_helper {
    /**
     * MR Lib namespace
     *
     * @var string
     */
    private $mrnamespace = 'local/mr/framework';

    /**
     * Helper instances
     *
     * Instances are organized by namespace then
     * by the helper name.  Each helper is created
     * only once per namespace.
     *
     * Example: array('blocks/helloworld' => array('world' => new blocks_helloworld_helper_world()))
     *
     * @var array
     */
    protected static $instances = array();

    /**
     * Current namespace for including helpers
     *
     * @var string
     */
    protected $namespace;

    /**
     * Automatically add the load helper
     * since this model uses it to load
     * other helpers
     *
     * @param string $namespace Current namespace, EG: blocks/reports
     */
    public function __construct($namespace = NULL) {
        if (is_null($namespace)) {
            $this->namespace = $this->mrnamespace;
        } else {
            $this->namespace = $namespace;
        }
        if (!isset(self::$instances[$this->namespace])) {
            self::$instances[$this->namespace] = array();
        }
        if (!array_key_exists('load', self::$instances[$this->namespace])) {
            self::$instances[$this->namespace]['load'] = new mr_helper_load();
            self::$instances[$this->namespace]['load']->_set_helper_namespace($this->namespace);
        }
    }

    /**
     * Static interface for getting an instance of mr_helper
     *
     * This is useful when you need to quickly call a single
     * helper method.  Example:
     *
     * <code>
     * <?php
     *      $return = mr_helper::get('blocks/helloworld')->world();
     * ?>
     * </code>
     *
     * @param string $namespace Current namespace, EG: blocks/reports
     * @return mr_helper
     */
    public static function get($namespace = NULL) {
        return new mr_helper($namespace);
    }

    /**
     * Get a helper
     *
     * @param string $name Helper name
     * @return mr_helper_abstract
     * @throws coding_exception
     */
    public function __get($name) {
        if (!array_key_exists($name, self::$instances[$this->namespace])) {
            try {
                // First try current namespace
                self::$instances[$this->namespace][$name] = $this->load("helper/$name");

            } catch (coding_exception $e) {
                // On fail, try MR namespace
                if ($this->namespace != $this->mrnamespace) {
                    self::$instances[$this->namespace][$name] = $this->load("helper/$name", array(), $this->mrnamespace);
                } else {
                    throw $e;
                }
            }
            if (!self::$instances[$this->namespace][$name] instanceof mr_helper_abstract) {
                throw new coding_exception("Helper '$name' does not extend mr_helper_abstract");
            }
            self::$instances[$this->namespace][$name]->_set_helper_namespace($this->namespace);
        }
        return self::$instances[$this->namespace][$name];
    }

    /**
     * Call a helper's direct method
     *
     * @param string $name Helper name
     * @param array $arguments Direct method args
     * @return mixed
     * @throws coding_exception
     */
    public function __call($name, $arguments) {
        if (!method_exists($this->$name, 'direct')) {
            throw new coding_exception("The helper $name does not implement method 'direct'");
        }
        return call_user_func_array(array($this->$name, 'direct'), $arguments);
    }
}