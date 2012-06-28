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
 * MR Fixture Role
 *
 * WARNING: This fixture is not like the others,
 * it depends on the system default roles.  Therefore,
 * it does not actually create new roles nor does it
 * delete any roles.
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_fixture_role extends mr_fixture_abstract {
    /**
     * @var string
     */
    protected $shortname;

    /**
     * @param string $shortname Any of the standard shortnames, EG: coursecreator,
     *                          editingteacher, frontpage, guest, manager,
     *                          student, teacher, user
     */
    public function __construct($shortname = 'student') {
        parent::__construct();
        $this->set_shortname($shortname);
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
            $this->set_results($DB->get_record('role', array('shortname' => $this->get_shortname()), '*', MUST_EXIST));
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
        $this->set_results(new stdClass);
    }

    /**
     * Determine if the fixture exists
     *
     * @return boolean
     */
    public function exists() {
        $fixture = $this->get_results();
        if (empty($fixture) or empty($fixture->id)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $shortname
     * @return mr_fixture_role
     */
    public function set_shortname($shortname) {
        $this->shortname = $shortname;
        return $this;
    }

    /**
     * @return string
     */
    public function get_shortname() {
        return $this->shortname;
    }
}