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
 * MR Autoload
 *
 * Used to automatically load class files.  This
 * is optimized to automatically
 * include MR Framework class files.  Extreme caution
 * must be used when adding additonal autoloaders, meaning
 * debug what this class does when you add your new autoloader.
 * You may find that it would be more efficient to extend this class
 * and override autoload() to suite your needs.
 *
 * @package mr
 * @author Mark Nielsen
 */
class mr_autoload {

    /**
     * Instance of mr_autoload
     *
     * @var mr_autoload
     */
    protected static $instance;

    /**
     * Class Loader
     *
     * @var mr_helper_load
     */
    protected $load;

    /**
     * Constructor
     *
     * Set up the helper to assist with autoloading.
     *
     * @param string $namespace The namespace to pass to the helper, cannot be empty string.
     * @throws coding_exception
     */
    public function __construct($namespace = 'local/mr/framework') {
        if ($namespace === '') {
            throw new coding_exception('Cannot autoload with an empty namespace.  This will enable autoload for all of Moodle.');
        }
        $this->load = new mr_helper_load();
        $this->load->_set_helper_namespace($namespace);
    }

    /**
     * Return a static default instance of mr_autoload
     *
     * @return mr_autoload
     */
    public static function get_instance() {
        if (!self::$instance instanceof mr_autoload) {
            self::$instance = new mr_autoload();
        }
        return self::$instance;
    }

    /**
     * Autoload
     *
     * Does the actual autoloading and is optimized for
     * MR Framework.  If you want to register your own autoloader
     * it might be wise to extend this class and optimize autoload()
     * for your particular use case.
     *
     * @param string $class The class name that needs to be loaded
     * @return boolean
     */
    public function autoload($class) {
        // Quick check to prevent trying to autoload everything in Moodle
        if (strpos($class, 'mr_') !== 0 and $this->load->get_namespace() == 'local/mr/framework') {
            return false;
        }
        try {
            // Change to a path and auto correct block
            $path = str_replace(array('_', 'block/'), array('/', 'blocks/'), $class);

            // Try to load the class file
            $this->load->file($path);
        } catch (coding_exception $e) {
            return false;
        }
        return class_exists($class, false);
    }

    /**
     * Register a mr_autoload
     *
     * Don't call this unless you know what you are doing!
     *
     * @param mr_autoload $autoload An instance of mr_autoload or NULL
     * @return void
     * @throws coding_exception
     */
    public static function register(mr_autoload $autoload = NULL) {
        if (is_null($autoload)) {
            $autoload = self::get_instance();
        }
        spl_autoload_register(array($autoload, 'autoload'));
    }

    /**
     * Unregister a mr_autoload
     *
     * Don't call this unless you know what you are doing!
     *
     * @param mr_autoload $autoload An instance of mr_autoload or NULL
     * @return void
     * @throws coding_exception
     */
    public static function unregister(mr_autoload $autoload = NULL) {
        if (is_null($autoload)) {
            $autoload = self::get_instance();
        }
        spl_autoload_unregister(array($autoload, 'autoload'));
    }
}