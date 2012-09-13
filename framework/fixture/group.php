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
require_once($CFG->dirroot.'/group/lib.php');

/**
 * MR Fixture Group
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_fixture_group extends mr_fixture_abstract {
    /**
     * @var mr_fixture_course
     */
    protected $course;

    /**
     * Parameters used to create the group
     *
     * @var array
     */
    protected $options = array();

    /**
     * @param mr_fixture_course $course The course to create the group in
     * @param array|object $options Parameters to use to create the group
     */
    public function __construct(mr_fixture_course $course, $options = array()) {
        parent::__construct();
        $this->set_course($course)
            ->set_options($options);
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
        global $DB;

        if (!$this->exists()) {
            // Dependents
            $this->get_course()->build();

            $group = (object) $this->get_options();
            $group->courseid = $this->get_course()->get('id');

            // Helping...!?!
            if (!property_exists($group, 'name')) {
                $group->name = '';
            }

            $groupid = groups_create_group($group);
            $this->set_results($DB->get_record('groups', array('id' => $groupid), '*', MUST_EXIST));
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
        if ($this->exists() and $this->get_course()->exists()) {
            groups_delete_group($this->get_results());
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
        return $DB->record_exists('groups', array('id' => $fixture->id));
    }

    /**
     * @param \mr_fixture_course $course
     * @return mr_fixture_group
     */
    public function set_course($course) {
        $this->course = $course;
        return $this;
    }

    /**
     * @return \mr_fixture_course
     */
    public function get_course() {
        return $this->course;
    }

    /**
     * Set properties to use for the enrollment
     *
     * @param array|object $options
     * @return mr_fixture_user
     */
    public function set_options($options) {
        $this->options = (array) $options;
        return $this;
    }

    /**
     * Get the properties used for the enrollment
     *
     * @return array
     */
    public function get_options() {
        return $this->options;
    }
}