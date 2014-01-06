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
 * Admin functions/classes
 */
require_once($CFG->libdir.'/adminlib.php');

/**
 * @see mr_controller
 */
require_once($CFG->dirroot.'/local/mr/framework/controller.php');

/**
 * MR Controller for Administrative Settings Pages
 *
 * @package mr
 * @author Mark Nielsen
 */
abstract class mr_controller_admin extends mr_controller {
    /**
     * Controller setup
     *
     * Setup specific to admin setting pages.
     *
     * @return void
     */
    public function setup() {
        $this->admin_setup();
    }

    /**
     * Generate a call to admin_externalpage_setup()
     *
     * One will most likely want to override this for
     * specific actions.
     *
     * @return void
     * @see admin_externalpage_setup
     * @see $action
     */
    public function admin_setup() {
        admin_externalpage_setup("{$this->component}_{$this->name}_{$this->action}");
    }

    /**
     * Get controller context
     *
     * @return object
     */
    public function get_context() {
        return context_system::instance();
    }

    /**
     * Generate a new URL to this controller
     *
     * @param array $extraparams Extra parameters to add to the URL
     * @return moodle_url
     */
    public function new_url($extraparams = array()) {
        return new moodle_url("/$this->plugin/view.php", array_merge(array('controller' => $this->name), $extraparams));
    }
}