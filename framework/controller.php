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
 * @see mr_helper
 */
require_once($CFG->libdir.'/mr/helper.php');

/**
 * @see mr_notify
 */
require_once($CFG->libdir.'/mr/notify.php');

/**
 * @see mr_tabs
 */
require_once($CFG->libdir.'/mr/tabs.php');

/**
 * @see mr_var
 */
require_once($CFG->libdir.'/mr/var.php');

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
abstract class mr_controller {
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
     * Paramaters to be passed to print_header_simple()
     *
     * @var array
     */
    protected $headerparams = array();

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
     * Get string module name
     *
     * @var string
     */
    protected $strmodule;

    /**
     * Get string identifier for default name
     *
     * @var string
     */
    protected $stridentifier;

    /**
     * Constructor
     *
     * Setup the controller with plugin specific configurations.
     *
     * @param string $plugin The plugin path
     * @param string $stridentifier Get string module name
     * @param string $strmodule Get string identifier for default name
     */
    public function __construct($plugin, $stridentifier, $strmodule) {
        global $CFG;

        // Controller name
        $this->name = end(explode('_', get_class($this)));

        // Store plugin information
        $this->strmodule      = $strmodule;
        $this->plugin         = $plugin;
        $this->stridentifier  = $stridentifier;

        $this->helper = new mr_helper($this->plugin);
        $this->notify = new mr_notify($this->strmodule);
        $this->config = $this->get_config();

        // Run base controller setup
        $this->setup();

        // Get URL
        $this->url = $this->new_url();
    }

    /**
     * To the outside world, just about all data members
     * are readonly, provide get access here
     *
     * @param string $method Method name should be get_{dataMember}()
     * @param array $arguments Should be empty, no args needed
     * @return mixed
     * @throws coding_exception
     */
    public function __call($method, $arguments) {
        $parts = explode('_', $method);

        if (!empty($parts[0]) and $parts[0] == 'get' and !empty($parts[1])) {
            $member = $parts[1];
            if (isset($this->$member)) {
                return $this->$member;
            }
        }
        throw new coding_exception("Method $method does not exist in class ".get_class($this));
    }

    /**
     * Controller setup
     *
     * @return void
     */
    public function setup() {
        require_login(optional_param('courseid', SITEID, PARAM_INT));
    }

    /**
     * Generate a new URL to this controller
     *
     * @param array $extraparams Extra parameters to add to the URL
     * @return moodle_url
     */
    public function new_url($extraparams = array()) {
        global $CFG, $COURSE;

        return new moodle_url("$CFG->wwwroot/$this->plugin/view.php", array_merge(array('controller' => $this->name, 'courseid' => $COURSE->id), $extraparams));
    }

    /**
     * Controller Initialization
     *
     * Override if your controller needs to
     * do specific setup before running. Everything
     * should be setup at this point.
     *
     * @return void
     * @see $action
     */
    protected function init() {
    }

    /**
     * Get global plugin config
     *
     * @return object
     * @example settings.php See how to save global config to work with this method
     */
    public function get_config() {
        if (!mr_var::exists($this->plugin)) {
            if (!$config = get_config($this->plugin)) {
                $config = new stdClass;
            }
            mr_var::set($this->plugin, $config);
        }
        return mr_var::get($this->plugin);
    }

    /**
     * Get controller context
     *
     * @return object
     */
    public function get_context() {
        global $COURSE;

        return get_context_instance(CONTEXT_COURSE, $COURSE->id);
    }

    /**
     * Set the current action of the controller
     *
     * @param string $action The current action
     * @return void
     * @see $action
     */
    public function set_action($action) {
        $this->action = $action;
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
     * @param string $stridentifier Get string module name
     * @param string $strmodule Get string identifier for default name
     * @return void
     * @throws coding_exception
     */
    public static function render($plugin, $stridentifier, $strmodule) {
        $controller = optional_param('controller', 'default', PARAM_PATH);
        $action     = optional_param('action', 'view', PARAM_ALPHA);
        $helper     = new mr_helper($plugin);

        try {
            // If controller is not a path, then assume under controller dir
            if (strpos($controller, '/') === false) {
                $controller = "controller/$controller";
            }
            $controller = $helper->load($controller, array($plugin, $stridentifier, $strmodule));

            // Hook method to execute
            $method = "{$action}_action";

            // Ensure the method is available and is public
            $reflection = new ReflectionClass($controller);
            if (!$reflection->hasMethod($method) or $reflection->getMethod($method)->isPublic() != true) {
                throw new coding_exception("Unable to handle request for $method");
            }
            // Action is OK, let the controller know what its doing...
            $controller->set_action($action);

            // Specific controller instance setup
            $controller->init();

            // Capability check
            $controller->require_capability();

            // Execute
            $return = $controller->$method();

            if ($return) {
                $controller->print_header();
                echo $return;
                $controller->print_footer();
            }
        } catch (moodle_exception $e) {
            error($e->getMessage());
        } catch (Exception $e) {
            error($e->getMessage());
        }
    }

    /**
     * Set header parameters
     *
     * Keep passing header paramater name and value pairs
     * to set header values.  EG: set_headerparams('title', 'Foo', 'heading', 'Bar')
     *
     * @return void
     */
    protected function set_headerparams() {
        $arguments = func_get_args();

        $pairs = array_chunk($arguments, 2);
        foreach ($pairs as $pair) {
            // Might be an odd number of params, handle it
            if (!array_key_exists(1, $pair)) {
                debugging('Cannot pass odd number of arguments to set_headerparams', DEBUG_DEVELOPER);
                continue;
            }

            $this->headerparams[$pair[0]] = $pair[1];
        }
    }

    /**
     * Sets the defaults for the params used in print_header_simple()
     *
     * @return void
     */
    protected function set_header_defaults() {
        $defaults = array(
            'title'      => get_string($this->stridentifier, $this->strmodule),
            'heading'    => get_string($this->stridentifier, $this->strmodule),
            'navigation' => get_string($this->stridentifier, $this->strmodule),
            'focus'      => '',
            'meta'       => '',
            'cache'      => true,
            'button'     => '',
            'menu'       => '',
            'usexml'     => false,
            'bodytags'   => '',
            'helpfile'   => '',
            'helpmod'    => $this->strmodule,
            'tab'        => $this->name,
            'subtab'     => $this->action
        );

        // Merge the defaults in w/o overriding values already set in headerparams
        $this->headerparams = array_merge($defaults, $this->headerparams);
    }

    /**
     * Build header navigation
     *
     * @param mixed $navigation First param to build_navigation
     * @return object
     */
    protected function build_navigation($navigation) {
        return build_navigation($navigation);
    }

    /**
     * Header output
     *
     * @return void
     */
    public function print_header() {
        // Really only want to call just before printing header to avoid unecessary processing
        $this->set_header_defaults();

        // Make it shorter cuz I'm lazy ><
        $p = $this->headerparams;

        // Print header, heading, messages and tabs
        print_header_simple($p['title'], $p['heading'], $this->build_navigation($p['navigation']), $p['focus'],
                            $p['meta'], $p['cache'], $p['button'], $p['menu'], $p['usexml'], $p['bodytags']);

        if (empty($p['helpfile'])) {
            print_heading($p['title']);
        } else {
            print_heading_with_help($p['title'], $p['helpfile'], $p['helpmod']);
        }
        echo $this->notify->display();
        $this->print_tabs($p['tab'], $p['subtab']);
    }

    /**
     * Footer output
     *
     * @return void
     */
    public function print_footer() {
        print_footer();
    }

    /**
     * Print tabs for the controller
     *
     * This goes through all of the controllers
     * and calls the add_tabs() method to get all
     * the available tabs.
     *
     * @param string $tab Current top level tab
     * @param string $subtab Current sub tab
     * @return void
     */
    public function print_tabs($tab, $subtab = NULL) {
        global $CFG;

        if (!empty($tab)) {
            $url = $this->new_url();
            $url->remove_params('controller');

            $tabs = new mr_tabs($url, $this->strmodule);

            // Restirct to only files and single depth
            $files = get_directory_list("$CFG->dirroot/$this->plugin/controller", '', false);

            foreach ($files as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                $this->helper->load->file("controller/$name");
                $classname = $this->helper->load->classname("controller/$name");

                call_user_func_array(array($classname, 'add_tabs'), array($this, &$tabs));
            }
            echo $tabs->display($tab, $subtab);
        }
    }

    /**
     * Add tabs for the controller
     *
     * @param mr_controller $controller The current active controller
     * @param mr_tabs $tabs The current set of tabs - add tabs to this
     * @return void
     * @see mr_tabs
     */
    public static function add_tabs($controller, &$tabs) {
    }
}