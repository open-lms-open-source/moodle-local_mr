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
 * MR Fixture User Profile Field Category
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_fixture_user_profile_category extends mr_fixture_abstract {
    /**
     * The category name
     *
     * @var string
     */
    protected $name;

    /**
     * @param string $name The category name
     */
    public function __construct($name = 'simpletest') {
        parent::__construct();
        $this->set_name($name);
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
            if ($record = $DB->get_record('user_info_category', array('name' => $this->get_name()))) {
                $this->set_results($record);
            } else {
                $fixture            = new stdClass;
                $fixture->name      = $this->get_name();
                $fixture->sortorder = $DB->count_records('user_info_category') + 1;

                $fixture->id = $DB->insert_record('user_info_category', $fixture);
                $this->set_results($fixture);
            }
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

        if ($this->exists()) {
            $fieldids = $DB->get_records_menu('user_info_field', array('categoryid' => $this->get('id')), '', 'id, id');
            if (!empty($fieldids)) {
                $DB->delete_records_list('user_info_data', 'fieldid', $fieldids);
                $DB->delete_records('user_info_field', array('categoryid' => $this->get('id')));
            }
            $DB->delete_records('user_info_category', array('id' => $this->get('id')));
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
        return $DB->record_exists('user_info_category', array('id' => $fixture->id));
    }

    /**
     * @param string $name
     * @return mr_fixture_user_profile_category
     */
    public function set_name($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function get_name() {
        return $this->name;
    }
}