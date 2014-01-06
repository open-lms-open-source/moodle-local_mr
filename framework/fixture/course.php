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
 * @see mr_fixture_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/fixture/abstract.php');

require_once($CFG->dirroot.'/course/lib.php');

/**
 * MR Fixture Course
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_fixture_course extends mr_fixture_abstract {
    /**
     * Properties to use for the course
     *
     * @var array
     */
    protected $options = array();

    /**
     * @param array|object $options Properties to use for the course
     */
    public function __construct($options = array()) {
        parent::__construct();
        $this->set_options($options);
    }

    /**
     * Create the fixture
     *
     * This method must be safe to call multiple times.
     *
     * @return void
     * @throws moodle_exception
     */
    public function build() {
        global $CFG, $DB;

        require_once($CFG->libdir.'/coursecatlib.php');

        if (!$this->exists()) {
            $course = (object) $this->get_options();

            // Clean course table - can happen when unit tests fail...
            if (!empty($course->shortname) and $record = $DB->get_record('course', array('shortname' => $course->shortname))) {
                delete_course($record, false);
            }
            if (!empty($course->idnumber) and $record = $DB->get_record('course', array('idnumber' => $course->idnumber))) {
                delete_course($record, false);
            }

            // Try to help folks out...
            if (!property_exists($course, 'category')) {
                $course->category = coursecat::get_default()->id;
            }
            if (!property_exists($course, 'fullname')) {
                $course->fullname = '';
            }
            $course = create_course($course);
            $this->set_results($DB->get_record('course', array('id' => $course->id), '*', MUST_EXIST));
        }
    }

    /**
     * Delete the fixture
     *
     * This method must be safe to call multiple times.
     *
     * @return void
     * @throws moodle_exception
     */
    public function destroy() {
        if ($this->exists()) {
            delete_course($this->get_results(), false);
        }
        $this->set_results(new stdClass);
    }

    /**
     * Determine if the fixture exists
     *
     * @return boolean
     */
    public function exists() {
        global $DB;

        $fixture = $this->get_results();
        if (empty($fixture) or empty($fixture->id)) {
            return false;
        }
        return $DB->record_exists('course', array('id' => $fixture->id));
    }

    /**
     * Set properties to use for the course
     *
     * @param array|object $options
     * @return mr_fixture_course
     */
    public function set_options($options) {
        $this->options = (array) $options;
        return $this;
    }

    /**
     * Get the properties used for the course
     *
     * @return array
     */
    public function get_options() {
        return $this->options;
    }
}