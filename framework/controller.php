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
 * @see mr_helper
 */
require_once($CFG->dirroot.'/local/mr/framework/helper.php');

/**
 * @see mr_html_notify
 */
require_once($CFG->dirroot.'/local/mr/framework/html/notify.php');

/**
 * @see mr_html_tabs
 */
require_once($CFG->dirroot.'/local/mr/framework/html/tabs.php');

/**
 * @see mr_html_heading
 */
require_once($CFG->dirroot.'/local/mr/framework/html/heading.php');

/**
 * @see mr_readonly
 */
require_once($CFG->dirroot.'/local/mr/framework/readonly.php');

/**
 * @see mr_var
 */
require_once($CFG->dirroot.'/local/mr/framework/var.php');

/**
 * MR Controller
 *
 * This class is used as a view controller and provides
 * a nice way to handle header/footer printing, error/success
 * messaging, access control and of course executing the code
 * specific to your action.
 *
 * @package mr
 * @author Mark Nielsen
 * @example controller/default.php See a class that extends mr_controller
 */
abstract class mr_controller extends mr_readonly {
    /**
     * Name of the controller, this is automatically defined in __construct
     *
     * @var string
     */
    protected $name = 'UNKNOWN';

    /**
     * Current action being executed by the controller
     *
     * @var string
     */
    protected $action = '';

    /**
     * URL of the controller
     *
     * @var moodle_url
     */
    protected $url;

    /**
     * Helper
     *
     * @var mr_helper
     */
    protected $helper;

    /**
     * Global configuration for plugin
     *
     * @var object
     * @see $plugin
     * @example settings.php See how to save global config to automatically populate this data member
     * @example controller/default.php See this in action
     */
    protected $config;

    /**
     * The plugin path
     *
     * @var string
     */
    protected $plugin;

    /**
     * Get string component
     *
     * @var string
     */
    protected $component;

    /**
     * Get string idenifier
     *
     * @var string
     */
    protected $identifier;

    /**
     * Controller tabs
     *
     * @var mr_html_tabs
     */
    protected $tabs;

    /**
     * Controller heading
     *
     * @var mr_html_heading
     */
    protected $heading;

    /**
     * Either plugin's renderer or core renderer
     *
     * @var renderer_base
     */
    protected $output;

    /**
     * MR Frameworks renderer
     *
     * @var local_mr_renderer
     */
    protected $mroutput;

    /**
     * Constructor
     *
     * Setup the controller with plugin specific configurations.
     *
     * @param string $plugin The plugin path
     * @param string $identifier Get string idenifier
     * @param string $component Get string component
     * @param string $action Set the current action of the controller
     */
    public function __construct($plugin, $identifier, $component, $action) {
        global $PAGE;

        // Controller name
        $class = get_class($this);
        if (strpos($class, '\\') !== false) {
            $classparts = explode('\\', $class);
            $this->name = str_replace('_controller', '', end($classparts));
        } else {
            $classparts = explode('_', $class);
            $this->name = end($classparts);
        }

        // Store plugin information
        $this->component   = $component;
        $this->plugin      = $plugin;
        $this->identifier  = $identifier;

        // Rest of the variable setup
        $this->action   = $action;
        $this->helper   = new mr_helper($this->plugin);
        $this->notify   = new mr_html_notify($this->component);
        $this->heading  = new mr_html_heading($this->component);
        $this->config   = $this->get_config();

        // Run base controller setup
        $this->setup();

        // Get URL
        $this->url = $this->new_url();

        // Build tab structure
        $this->init_tabs();

        // Controller specific constructor customizations
        $this->init();

        // Load up renderers (Last, otherwise cannot customize layout)
        try {
            $this->output = $PAGE->get_renderer($this->component);
        } catch (moodle_exception $e) {
            $this->output = $PAGE->get_renderer('core'); // Should this be $OUTPUT ?
        }
        try {
            $this->mroutput = $PAGE->get_renderer('local_mr', 'extended');
        } catch (moodle_exception $e) {
            $this->mroutput = $PAGE->get_renderer('local_mr');
        }
    }

    /**
     * Controller setup
     *
     * @return void
     */
    public function setup() {
        global $CFG, $COURSE, $PAGE;

        require_login(optional_param('courseid', SITEID, PARAM_INT));

        // We want to send relative URL to $PAGE so $PAGE can set it to https or not
        $moodleurl   = $this->new_url(array('action' => $this->action));
        $relativeurl = str_replace($CFG->wwwroot, '', $moodleurl->out_omit_querystring());

        $PAGE->set_title(format_string($COURSE->fullname));
        $PAGE->set_heading(format_string($COURSE->fullname));
        $PAGE->set_context($this->get_context());
        $PAGE->set_url($relativeurl, $moodleurl->params());
        $this->heading->set($this->identifier);
    }

    /**
     * Generate a new URL to this controller
     *
     * @param array $extraparams Extra parameters to add to the URL
     * @return moodle_url
     */
    public function new_url($extraparams = array()) {
        global $COURSE;

        return new moodle_url("/$this->plugin/view.php", array_merge(array('controller' => $this->name, 'courseid' => $COURSE->id), $extraparams));
    }

    /**
     * Controller Initialization
     *
     * Override if your controller needs to
     * do specific setup before running. Everything
     * should be setup at this point except for $this->output and
     * $this->mroutput.
     *
     * Example tasks to do in init():
     *      - Override defaults: title, tab selection, etc
     *      - Change layout: $PAGE->set_pagelayout(...)
     *
     * @return void
     * @see $action
     * @see $output
     * @see $mroutput
     */
    protected function init() {
    }

    /**
     * Setup controller tabs
     *
     * This goes through all of the controllers
     * and calls the add_tabs() method to get all
     * the available tabs.
     *
     * @return void
     */
    protected function init_tabs() {
        global $CFG;

        $url = $this->new_url();
        $url->remove_params('controller');

        $this->tabs = new mr_html_tabs($url, $this->component);
        $this->tabs->set($this->name, $this->action);

        // Restirct to only files and single depth
        $files = get_directory_list("$CFG->dirroot/$this->plugin/controller", '', false);

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $this->helper->load->file("controller/$name");
            $classname = $this->helper->load->classname("controller/$name");

            call_user_func_array(array($classname, 'add_tabs'), array($this, &$this->tabs));
        }
    }

    /**
     * Get global plugin config
     *
     * @return object
     * @example settings.php See how to save global config to work with this method
     * @todo This should use $this->component instead of $this->plugin.  This will break mod/assignment/efolio
     */
    public function get_config() {
        if (!mr_var::instance()->exists($this->plugin)) {
            if (!$config = get_config($this->plugin)) {
                $config = new stdClass;
            }
            mr_var::instance()->set($this->plugin, $config);
        }
        return mr_var::instance()->get($this->plugin);
    }

    /**
     * Get controller context
     *
     * @return \context
     */
    public function get_context() {
        global $COURSE;

        return context_course::instance($COURSE->id);
    }

    /**
     * Call the necessary calls to require_capability
     *
     * Use $this->action for current action being executed
     *
     * @return void
     * @see $action
     */
    public function require_capability() {
    }

    /**
     * Render a controller view
     *
     * Based on "controller" and "action" parameters, create a controller
     * instance that corresponds to "controller" param and then, if available
     * call the method defined in the "action" parameter, but with the word
     * "_action" appended to it.  Example, "controller" parameter is set to "foo"
     * and the "action" parameter is set to "bar", then this will load up
     * controller/foo.php and call method "bar_action".
     *
     * @param string $plugin The plugin path
     * @param string $identifier Get string idenifier
     * @param string $component Get string component
     * @return void
     * @throws coding_exception
     */
    public static function render($plugin, $identifier, $component) {
        $controller = optional_param('controller', 'default', PARAM_PATH);
        $action     = optional_param('action', 'view', PARAM_ALPHA);
        $helper     = new mr_helper($plugin);

        // Load controller's class file
        try {
            // If controller is not a path, then assume under controller dir
            $path = $controller;
            if (strpos($controller, '/') === false) {
                $path = "controller/$controller";
            }
            $helper->load->file($path);
            $classname = $helper->load->classname($path);
        } catch (Exception $e) {
            // Fallback to auto-loader style controllers.
            $classname = "\\$component\\controller\\{$controller}_controller";
            if (!class_exists($classname)) {
                throw $e; // Throw original error.
            }
        }

        // Hook method to execute
        $method = "{$action}_action";

        // Ensure the method is available and is public
        $reflection = new ReflectionClass($classname);
        if (!$reflection->hasMethod($method) or $reflection->getMethod($method)->isPublic() != true) {
            throw new coding_exception("Unable to handle request for $method");
        }
        // Action is OK, instantiate the controller
        $controller = $reflection->newInstance($plugin, $identifier, $component, $action);

        // Capability check
        $controller->require_capability();

        // Execute
        $return = $controller->$method();

        if ($return) {
            $controller->print_header();
            echo $return;
            $controller->print_footer();
        }
    }

    /**
     * Header output
     *
     * @return void
     */
    public function print_header() {
        echo $this->output->header();
        echo $this->mroutput->render($this->heading);
        echo $this->mroutput->render($this->notify);
        echo $this->mroutput->render($this->tabs);
    }

    /**
     * Footer output
     *
     * @return void
     */
    public function print_footer() {
        echo $this->output->footer();
    }

    /**
     * Add tabs for the controller
     *
     * @param mr_controller $controller The current active controller
     * @param mr_html_tabs &$tabs The current set of tabs - add tabs to this
     * @return void
     * @see mr_html_tabs
     */
    public static function add_tabs($controller, &$tabs) {
    }
}