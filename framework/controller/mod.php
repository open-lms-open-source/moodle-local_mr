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
 * @see mr_controller
 */
require_once($CFG->dirroot.'/local/mr/framework/controller.php');

/**
 * MR Controller for Modules
 *
 * @package mr
 * @author Mark Nielsen
 */
abstract class mr_controller_mod extends mr_controller {

    /**
     * Course module record
     *
     * @var object
     */
    protected $cm;

    /**
     * Module instance record
     *
     * @var object
     */
    protected $instance;

    /**
     * Controller setup
     *
     * Get $cm and $instance and perform
     * proper call to require_login()
     *
     * @return void
     * @see $cm, $instance
     * @throws coding_exception
     */
    public function setup() {
        global $DB, $COURSE, $PAGE;

        // Course module ID or module instance ID
        $id = optional_param('id', 0, PARAM_INT);
        $a  = optional_param('a', 0, PARAM_INT);

        // Get required course module record
        if ($id) {
            $this->cm = get_coursemodule_from_id($this->component, $id, 0, false, MUST_EXIST);
        } else if ($a) {
            $this->cm = get_coursemodule_from_instance($this->component, $a, 0, false, MUST_EXIST);
        } else {
            throw new coding_exception('No Course Module or Instance ID was passed');
        }

        // Get the module instance
        $this->instance = $DB->get_record($this->component, array('id' => $this->cm->instance), '*', MUST_EXIST);

        require_login($this->cm->course, true, $this->cm);

        $PAGE->set_title(format_string($this->instance->name));
        $PAGE->set_heading(format_string($COURSE->fullname));
        $PAGE->set_activity_record($this->instance);
        $PAGE->set_context($this->get_context());
        $PAGE->set_url($this->new_url(array('action' => $this->action)));
        $this->heading->text = format_string($this->instance->name);
    }

    /**
     * Generate a new URL to this controller
     *
     * Include course module ID in the URL
     */
    public function new_url($extraparams = array()) {
        return new moodle_url("/$this->plugin/view.php", array_merge(array('controller' => $this->name, 'id' => $this->cm->id), $extraparams));
    }

    /**
     * Get controller context
     *
     * Get module context
     *
     * @return object
     */
    public function get_context() {
        return context_module::instance($this->cm->id);
    }
}