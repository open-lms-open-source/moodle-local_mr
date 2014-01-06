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

/**
 * MR Fixture Enrollment
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_fixture_enrollment extends mr_fixture_abstract {
    /**
     * @var mr_fixture_course
     */
    protected $course;

    /**
     * @var mr_fixture_user
     */
    protected $user;

    /**
     * @var mr_fixture_role|null
     */
    protected $role = null;

    /**
     * @var enrol_plugin
     */
    protected $enroll;

    /**
     * Parameters to pass to the enrol_plugin->enrol_user()
     *
     * @var array
     */
    protected $options = array();

    /**
     * @param mr_fixture_course $course The course to enroll the user into
     * @param mr_fixture_user $user The user to enroll
     * @param enrol_plugin $enroll The enrollment plugin to use, defaults to enrol_get_plugin('manual')
     * @param mr_fixture_role|null $role The role to use for the enrollment, optional
     * @param array|object $options Parameters to pass to the enrol_plugin->enrol_user()
     */
    public function __construct(mr_fixture_course $course, mr_fixture_user $user, enrol_plugin $enroll = null, mr_fixture_role $role = null, $options = array()) {
        parent::__construct();

        if (is_null($enroll)) {
            $enroll = enrol_get_plugin('manual');
        }
        $this->set_course($course)
             ->set_user($user)
             ->set_enroll($enroll)
             ->set_role($role)
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
            $this->get_user()->build();
            $this->get_course()->build();

            $role = $this->get_role();
            if (!is_null($role)) {
                $role->build();
                $roleid = $role->get('id');
            } else {
                $roleid = null;
            }
            $instance = $this->get_option('instance', null);
            if (empty($instance)) {
                $instance = $this->fetch_enrollment_instance();
            }
            $this->get_enroll()->enrol_user(
                $instance,
                $this->get_user()->get('id'),
                $roleid,
                $this->get_option('timestart', 0),
                $this->get_option('timeend', 0),
                $this->get_option('status')
            );
            $conditions = array('enrolid' => $instance->id, 'userid' => $this->get_user()->get('id'));
            $this->set_results($DB->get_record('user_enrolments', $conditions, '*', MUST_EXIST));
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
        global $DB;

        if ($this->exists() and $this->get_user()->exists() and $this->get_course()->exists()) {
            $instance = $DB->get_record('enrol', array('id' => $this->get('enrolid')), '*', MUST_EXIST);
            $this->get_enroll()->unenrol_user($instance, $this->get_user()->get('id'));
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
        return $DB->record_exists('user_enrolments', array('id' => $fixture->id));
    }

    /**
     * Find or create an enrollment instance record
     *
     * @return stdClass
     */
    protected function fetch_enrollment_instance() {
        global $DB;

        // Try to find one...
        if (!$instance = $DB->get_record('enrol', array('enrol' => $this->get_enroll()->get_name(), 'courseid' => $this->get_course()->get('id')), '*', IGNORE_MULTIPLE)) {
            $instanceid = $this->get_enroll()->add_instance($this->get_course()->get_results());
            $instance   = $DB->get_record('enrol', array('id' => $instanceid), '*', MUST_EXIST);
        }
        return $instance;
    }

    /**
     * @param \mr_fixture_course $course
     * @return mr_fixture_enrollment
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
     * @param \mr_fixture_user $user
     * @return mr_fixture_enrollment
     */
    public function set_user($user) {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \mr_fixture_user
     */
    public function get_user() {
        return $this->user;
    }

    /**
     * @param \enrol_plugin $enroll
     * @return mr_fixture_enrollment
     */
    public function set_enroll($enroll) {
        $this->enroll = $enroll;
        return $this;
    }

    /**
     * @return \enrol_plugin
     */
    public function get_enroll() {
        return $this->enroll;
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

    /**
     * @param $name
     * @param mixed $default
     * @return mixed
     */
    public function get_option($name, $default = null) {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }
        return $default;
    }

    /**
     * @param \mr_fixture_role|null $role
     * @return mr_fixture_enrollment
     */
    public function set_role($role) {
        $this->role = $role;
        return $this;
    }

    /**
     * @return \mr_fixture_role|null
     */
    public function get_role() {
        return $this->role;
    }
}