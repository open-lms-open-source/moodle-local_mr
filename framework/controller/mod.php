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
 * @see mr_controller
 */
require_once($CFG->libdir.'/mr/controller.php');

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
        global $COURSE;

        // Course module ID or module instance ID
        $id = optional_param('id', 0, PARAM_INT);
        $a  = optional_param('a', 0, PARAM_INT);

        // Get required course module record
        if ($id) {
            if (!$this->cm = get_coursemodule_from_id($this->strmodule, $id)) {
                throw new coding_exception('Course Module ID was incorrect');
            }
        } else if ($a) {
            if (!$this->cm = get_coursemodule_from_instance($this->strmodule, $a)) {
                throw new coding_exception('Course Module Instance ID was incorrect');
            }
        } else {
            throw new coding_exception('No Course Module or Instance ID was passed');
        }

        // Get the module instance
        if (!$this->instance = get_record($this->strmodule, 'id', $this->cm->instance)) {
            throw new coding_exception("Module instance could not be found for $this->strmodule with id = {$this->cm->instance}");
        }
        require_login($this->cm->course, true, $this->cm);

        // Module header setup
        $this->set_headerparams(
            'button', update_module_button($this->cm->id, $this->cm->course, get_string($this->stridentifier, $this->strmodule)),
            'menu', navmenu($COURSE, $this->cm),
            'navigation', ''
        );
    }

    /**
     * Generate a new URL to this controller
     *
     * Include course module ID in the URL
     */
    public function new_url($extraparams = array()) {
        global $CFG;

        return new moodle_url("$CFG->wwwroot/$this->plugin/view.php", array_merge(array('controller' => $this->name, 'id' => $this->cm->id), $extraparams));
    }

    /**
     * Get controller context
     *
     * Get module context
     *
     * @return object
     */
    public function get_context() {
        return get_context_instance(CONTEXT_MODULE, $this->cm->id);
    }

    /**
     * Build header navigation
     *
     * @param mixed $navigation First param to build_navigation
     * @return object
     */
    protected function build_navigation($navigation) {
        return build_navigation($navigation, $this->cm);
    }
}