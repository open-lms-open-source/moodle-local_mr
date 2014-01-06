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
 * MR Fixture Group Member
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_fixture_group_member extends mr_fixture_abstract {
    /**
     * @var mr_fixture_group
     */
    protected $group;

    /**
     * @var mr_fixture_enrollment
     */
    protected $enrollment;

    /**
     * @param mr_fixture_group $group The group to use for the membership
     * @param mr_fixture_enrollment $enrollment The enrollment to use for the membership
     */
    public function __construct(mr_fixture_group $group, mr_fixture_enrollment $enrollment) {
        parent::__construct();
        $this->set_group($group)->set_enrollment($enrollment);
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
            $this->get_group()->build();
            $this->get_enrollment()->build();

            groups_add_member(
                $this->get_group()->get_results(),
                $this->get_user()->get_results()
            );
            $conditions = array('groupid' => $this->get_group()->get('id'), 'userid' => $this->get_user()->get('id'));
            $this->set_results($DB->get_record('groups_members', $conditions, '*', MUST_EXIST));
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
        if ($this->exists() and $this->get_group()->exists() and $this->get_enrollment()->exists()) {
            groups_remove_member(
                $this->get_group()->get_results(),
                $this->get_user()->get_results()
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
        $fixture = $this->get_results();
        if (empty($fixture) or empty($fixture->id) or !$this->get_group()->exists() or !$this->get_user()->exists()) {
            return false;
        }
        return groups_is_member(
            $this->get_group()->get('id'),
            $this->get_user()->get('id')
        );
    }

    /**
     * @param \mr_fixture_group $group
     * @return mr_fixture_group_member
     */
    public function set_group($group) {
        $this->group = $group;
        return $this;
    }

    /**
     * @return \mr_fixture_group
     */
    public function get_group() {
        return $this->group;
    }

    /**
     * @param \mr_fixture_enrollment $enrollment
     * @return mr_fixture_group_member
     */
    public function set_enrollment($enrollment) {
        $this->enrollment = $enrollment;
        return $this;
    }

    /**
     * @return \mr_fixture_enrollment
     */
    public function get_enrollment() {
        return $this->enrollment;
    }

    /**
     * @return mr_fixture_user
     */
    public function get_user() {
        return $this->get_enrollment()->get_user();
    }
}