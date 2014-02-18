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
require_once($CFG->dirroot.'/user/lib.php');

/**
 * MR Fixture User
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_fixture_user extends mr_fixture_abstract {
    /**
     * Properties to use for the user
     *
     * @var array
     */
    protected $options = array();

    /**
     * @param array|object $options Properties to use for the user
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

        if (!$this->exists()) {
            $user = (object) $this->get_options();

            // Clean user table - can happen when unit tests fail...
            if (!empty($user->username) and $record = $DB->get_record('user', array('username' => $user->username))) {
                $this->delete_user($record);
            }
            if (!empty($user->idnumber) and $record = $DB->get_record('user', array('idnumber' => $user->idnumber))) {
                $this->delete_user($record);
            }
            if (!property_exists($user, 'mnethostid')) {
                $user->mnethostid = $CFG->mnet_localhost_id;
            }
            $userid = user_create_user($user);
            $this->set_results($DB->get_record('user', array('id' => $userid), '*', MUST_EXIST));
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
            $this->delete_user($this->get_results());
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
        return $DB->record_exists('user', array('id' => $fixture->id));
    }

    /**
     * Does work of deleting the user
     *
     * @param stdClass $user
     */
    protected function delete_user($user) {
        global $DB;

        // delete_user($user); This stopped working.
        $DB->delete_records('user', array('id' => $user->id));
    }

    /**
     * Set properties to use for the user
     *
     * @param array|object $options
     * @return mr_fixture_user
     */
    public function set_options($options) {
        $this->options = (array) $options;
        return $this;
    }

    /**
     * Get the properties used for the user
     *
     * @return array
     */
    public function get_options() {
        return $this->options;
    }
}