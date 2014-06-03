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
 * MR Controller for Blocks
 *
 * @package mr
 * @author Mark Nielsen
 */
abstract class mr_controller_block extends mr_controller {
    /**
     * Block instance record
     *
     * This is NOT an instance of your block,
     * EG NOT: new block_blockname()
     *
     * This can be false if not found in the course.
     *
     * @var object
     */
    protected $instance = false;

    /**
     * The block name
     *
     * @var string
     */
    protected $blockname = 'UNKNOWN';

    /**
     * Controller setup
     *
     * Set $blockname and $instance.  For best results
     * pass an instanceid URL param.  Getting
     * the instance is not required and is usually
     * not necessary.
     *
     * @return void
     * @see $blockname, $instance
     * @throws coding_exception
     */
    public function setup() {
        global $DB;

        // Run parent routine
        parent::setup();

        // Derive blockname from plugin path
        $this->blockname = str_replace('blocks/', '', $this->plugin);

        // Attempt to get the block instance record
        if ($instanceid = optional_param('instanceid', 0, PARAM_INT)) {
            $this->instance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);
        }
    }

    /**
     * Generate a new URL to this controller
     *
     * Only include block instance ID if it is passed.
     */
    public function new_url($extraparams = array()) {
        if ($this->instance) {
            $extraparams['instanceid'] = $this->instance->id;
        }
        return parent::new_url($extraparams);
    }

    /**
     * Get controller context
     *
     * Get block context if we have block instance, otherwise
     * use default context
     *
     * @return object
     */
    public function get_context() {
        if ($this->instance) {
            return context_block::instance($this->instance->id);
        }
        return parent::get_context();
    }
}