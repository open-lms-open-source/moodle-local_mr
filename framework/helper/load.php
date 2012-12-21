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
 * @see mr_helper_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/helper/abstract.php');

/**
 * MR Helper Load
 *
 * This helper assists with dynamically including
 * class files and also with dynamically instantiating
 * those classes.
 *
 * This class is namespace driven.  A namespace is a relative
 * directory path from Moodle's directory root. Examples:
 *  - local/mr/framework
 *  - blocks/helloworld
 *
 * Files are loaded based on the passed path and current or
 * passed namespace.  Once combined, a file name is generated.
 * Example: path = foo/bar, namespace = blocks/helloworld, derived file
 * path would be /path/to/moodle/blocks/helloworld/foo/bar.php
 *
 * Class names are derived in the same way, but no ".php" and
 * the forwardslahes are replaced with underscores.  So, the above
 * example would load the class: blocks_helloworld_foo_bar.
 *
 * There are currently two exceptions to the generation of class names:
 *  1 For class names, blocks can be block as Moodle very often ignores the "s", but the namespace MUST be blocks
 *  2 lib_mr gets switched to just mr.  This is so all the mr classes are not lib_mr_*
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_helper_load extends mr_helper_abstract {
    /**
     * Default name space for loading files/classes
     *
     * @var string
     */
    protected $namespace = 'local/mr/framework';

    /**
     * Set the namespace of the mr_helper that created this instance
     *
     * Also, set our current namespace
     *
     * @param string $namespace The namespace, EG: blocks/helloworld
     * @return void
     */
    public function _set_helper_namespace($namespace) {
        parent::_set_helper_namespace($namespace);
        $this->set_namespace($namespace);
    }

    /**
     * Set namespace
     *
     * @param string $namespace New namespace, EG: blocks/reports
     * @return mr_helper_load
     */
    public function set_namespace($namespace) {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Get the current namespace
     *
     * @return string
     */
    public function get_namespace() {
        return $this->namespace;
    }

    /**
     * Load an instance from a file
     *
     * @param string $path Relative file path to class definition, EG: controller/mycontroller
     *                     Include "/*" at the end of path to load all files in path
     *                     Include "/**" at the end of path to load all files in path and sub directories
     * @param array $arguments Arguments to pass to the constructor
     * @param string $namespace Alter namespace
     * @return mixed
     * @throws coding_exception
     */
    public function direct($path, $arguments = array(), $namespace = NULL) {
        global $CFG;

        // Load all classes in a directory
        if (substr($path, -1) == '*') {
            // See if it is recursive or not
            if (substr($path, -3) == '/**') {
                $descend = true;
            } else if (substr($path, -2) == '/*') {
                $descend = false;
            } else {
                throw new coding_exception("Improper use of asterisk in path ($path), use {path}/* or {path}/**");
            }
            $path  = rtrim($path, '/*');
            $path  = $this->resolve_namespace($path, $namespace);
            $files = get_directory_list("$CFG->dirroot/$path", array('abstract.php'), $descend);

            // Generate instances for each file
            $instances = array();
            foreach ($files as $file) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $dirname  = pathinfo($file, PATHINFO_DIRNAME);
                $name     = trim("$dirname/$filename", '/.');

                $instances[$name] = $this->direct("$path/$name", $arguments, '');
            }
            return $instances;
        }

        // Load the class file
        $this->file($path, $namespace);

        // Get the class name
        $classname = $this->classname($path, $namespace);

        // Make an instance
        return $this->instance($classname, $arguments);
    }

    /**
     * Load plugins
     *
     * This method does not augment the namespace, use
     * set_namespace if it needs to be modified.
     *
     * Based on the following layout:
     * - /namespace/plugin/PLUGINPATH/class.php
     * Where PLUGINPATH is a the plugin's directory or directory
     * path, meaning you could have multiple levels of plugins.
     *
     * Examples:
     *  - /namespace/plugin/foo/class.php
     *  - /namespace/plugin/foo/bar/class.php
     *  - /namespace/plugin/foo/bar/baz/class.php
     *
     * Do not name any plugins "base".  This is reserved for
     * any plugins that want to use /namespace/plugin/base/class.php
     * as their plugin's base class.
     *
     * Example Calls to this method
     * <code>
     * <?php
     *      // If you have the single plugin type layout
     *      $helper = new mr_helper('namespace/path');
     *      $helper->load->plugin();       // Loads all plugins
     *      $helper->load->plugin('*');    // Loads all plugins
     *      $helper->load->plugin('bar');  // Loads a plugin with name 'bar'
     *
     *      // If you have the Multiple plugin type layout
     *      $helper = new mr_helper('namespace/path');
     *      $helper->load->plugin('foo/*');   // This will load all plugins of type foo
     *      $helper->load->plugin('foo/bar'); // This will load the bar plugin of type foo
     *
     *      // If you have arguments, you can use any of the above and then
     *      // keep passing arguments.  The arguments will be passed to every
     *      // plugin that gets created
     *      $helper = new mr_helper('namespace/path');
     *
     *      // Loads plugin with name 'one' and passes $arg1 and $arg2 to its constructor
     *      $helper->load->plugin('one', $arg1, $arg2);
     *
     *      // Loads all plugins and passes $arg1 and $arg2 to each plugin's constructor
     *      $helper->load->plugin('*', $arg1, $arg2);
     * ?>
     * </code>
     *
     * @param string $plugin The plugin path
     * @return mixed
     * @example controller/plugin.php
     * @throws coding_exception
     */
    public function plugin($plugin = '*') {
        global $CFG;

        // Get remaining args and shift off $plugin
        $args = func_get_args();
        array_shift($args);

        // Check to see if we are dealing with loading multiple plugins
        if (substr($plugin, -1) === '*') {
            $plugin   = rtrim($plugin, '/*');
            $relative = 'plugin';

            if (!empty($plugin)) {
                $relative .= "/$plugin";
            }
            $relative = clean_param($relative, PARAM_PATH);
            $absolute = "$CFG->dirroot/$this->namespace/$relative";

            // Check path
            if (!is_dir($absolute)) {
                throw new coding_exception("Derived path is not a directory: $this->namespace/$relative");
            }

            $plugins = get_list_of_plugins("$this->namespace/$relative", 'base');

            // We should find plugins!
            if (empty($plugins)) {
                throw new coding_exception("Failed to find any plugins in $this->namespace/$relative");
            }
            $loaded = array();
            foreach ($plugins as $plugin) {
                $loaded[$plugin] = $this->direct("$relative/$plugin/class", $args);
            }
            return $loaded;
        }
        // Loading a single plugin...

        // Clean plugin
        $plugin = clean_param($plugin, PARAM_PATH);

        // Load the plugin class file
        return $this->direct("plugin/$plugin/class", $args);
    }

    /**
     * Load a file
     *
     * @param string $path Relative file path to file, EG: controller/mycontroller
     * @param string $namespace Alter namespace
     * @return void
     * @throws coding_exception
     */
    public function file($path, $namespace = NULL) {
        global $CFG;

        $path     = $this->resolve_namespace($path, $namespace);
        $path     = clean_param($path, PARAM_PATH);
        $fullpath = "$CFG->dirroot/$path.php";

        if (!file_exists($fullpath)) {
            throw new coding_exception("Path does not exist: $path.php");
        }
        require_once($fullpath);
    }

    /**
     * Generate a class name from path
     *
     * @param string $path Relative file path to class definition, EG: controller/mycontroller
     * @param string $namespace Alter namespace
     * @return string
     * @throws coding_exception
     */
    public function classname($path, $namespace = NULL) {
        $path = $this->resolve_namespace($path, $namespace);

        // Make a classname!
        $classname = str_replace('/', '_', $path);

        if (class_exists($classname, false)) {
            return $classname;
        }

        // Try modifying classname for MR Framework
        $newclass = str_replace('local_mr_framework', 'mr', $classname);

        if (class_exists($newclass, false)) {
            return $newclass;
        }

        // Try replacing blocks with block
        $newclass = substr_replace($classname, 'block', 0, 6);

        if (class_exists($newclass, false)) {
            return $newclass;
        }
        throw new coding_exception("Failed to derive classname from path: $path");
    }

    /**
     * Generate an instance of a class
     *
     * @param string $classname Name of class
     * @param array $arguments Arguments to pass to the class constructor
     * @return mixed
     * @throws coding_exception
     */
    public function instance($classname, $arguments = array()) {
        $reflection = new ReflectionClass($classname);

        if ($reflection->isInstantiable()) {
            if ($reflection->getConstructor() instanceof ReflectionMethod) {
                return $reflection->newInstanceArgs($arguments);
            } else {
                return $reflection->newInstance();
            }
        }
        throw new coding_exception("Failed to instantiate class: $classname");
    }

    /**
     * Combine path with either passed namespace or
     * with currently set namesapce.
     *
     * @param string $path Relative file path to class definition, EG: controller/mycontroller
     * @param string $namespace Alter namespace
     * @return string
     */
    protected function resolve_namespace($path, $namespace) {
        if (is_null($namespace)) {
            $namespace = $this->namespace;
        }
        if (!empty($namespace)) {
            return str_replace('local/mr/framework/mr', 'local/mr/framework', "$namespace/$path");
        }
        return $path;
    }
}