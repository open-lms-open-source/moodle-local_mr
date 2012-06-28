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
 * MR Fixture Group Member
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_fixture_role_assignment extends mr_fixture_abstract {
    /**
     * @var mr_fixture_user
     */
    protected $user;

    /**
     * @var mr_fixture_role
     */
    protected $role;

    /**
     * @var context|mr_fixture_user|mr_fixture_course
     */
    protected $context;

    /**
     * Properties to use for the role assignment
     *
     * @var array
     */
    protected $options = array();

    /**
     * @param mr_fixture_role $role The role to assign
     * @param mr_fixture_user $user The user to use for the role assignment
     * @param context|mr_fixture_user|mr_fixture_course $context The context to use for the role assignment
     * @param array|object $options The options
     */
    public function __construct(mr_fixture_role $role, mr_fixture_user $user, $context, $options = array()) {
        parent::__construct();
        $this->set_role($role)
             ->set_user($user)
             ->set_context($context)
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
            $this->get_role()->build();
            $this->get_user()->build();

            $raid = role_assign(
                $this->get_role()->get('id'),
                $this->get_user()->get('id'),
                $this->get_contextid(),
                $this->get_option('component', ''),
                $this->get_option('itemid', 0),
                $this->get_option('timemodified', '')
            );
            $this->set_results($DB->get_record('role_assignments', array('id' => $raid), '*', MUST_EXIST));
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
        if ($this->exists() and $this->get_role()->exists() and $this->get_user()->exists()) {
            role_unassign(
                $this->get_role()->get('id'),
                $this->get_user()->get('id'),
                $this->get_contextid(),
                $this->get_option('component', ''),
                $this->get_option('itemid', 0)
            );
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
        return $DB->record_exists('role_assignments', array('id' => $fixture->id));
    }

    /**
     * @param \context|mr_fixture_user|mr_fixture_course $context
     * @return mr_fixture_role_assignment
     */
    public function set_context($context) {
        $this->context = $context;
        return $this;
    }

    /**
     * @return \context|mr_fixture_user|mr_fixture_course
     */
    public function get_context() {
        return $this->context;
    }

    /**
     * Derive the context ID from the context varaible
     *
     * @return int
     * @throws coding_exception
     */
    public function get_contextid() {
        $context = $this->get_context();
        if ($context instanceof mr_fixture_user) {
            if (!$context->exists()) {
                throw new coding_exception('User fixture does not exist yet.  You are calling this too early.');
            }
            $context = context_user::instance($context->get('id'));

        } else if ($context instanceof mr_fixture_course) {
            if (!$context->exists()) {
                throw new coding_exception('Course fixture does not exist yet.  You are calling this too early.');
            }
            $context = context_course::instance($context->get('id'));

        } else if (!$context instanceof context) {
            throw new coding_exception('Invalid context argument');
        }
        return $context->id;
    }

    /**
     * Set properties to use for the role assignment
     *
     * @param array|object $options
     * @return mr_fixture_role_assignment
     */
    public function set_options($options) {
        $this->options = (array) $options;
        return $this;
    }

    /**
     * Get the properties used for the role assignment
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
     * @param \mr_fixture_role $role
     * @return mr_fixture_role_assignment
     */
    public function set_role($role) {
        $this->role = $role;
        return $this;
    }

    /**
     * @return \mr_fixture_role
     */
    public function get_role() {
        return $this->role;
    }

    /**
     * @param \mr_fixture_user $user
     * @return mr_fixture_role_assignment
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
}