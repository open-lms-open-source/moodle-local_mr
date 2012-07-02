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
 * MR Fixture User Profile Field
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_fixture_user_profile extends mr_fixture_abstract {
    /**
     * The profile field category
     *
     * @var mr_fixture_user_profile_category
     */
    protected $category;

    /**
     * Properties to use for the user profile field
     *
     * @var array
     */
    protected $options = array();

    protected $defaults = array(
        'shortname' => 'simpletest',
        'name' => 'simpletest',
        'datatype' => 'text',
    );

    /**
     * @param mr_fixture_user_profile_category $category The profile field's category
     * @param array|object $options Properties to use for the user profile field
     */
    public function __construct(mr_fixture_user_profile_category $category, $options = array()) {
        parent::__construct();
        $this->set_options($options)
             ->set_category($category);
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
            // Build dependents
            $this->get_category()->build();

            $options = $this->get_options();
            $options = array_merge($this->defaults, $options);

            $options['categoryid'] = $this->get_category()->get('id');

            // Clean table - can happen when unit tests fail...
            if ($record = $DB->get_record('user_info_field', array('shortname' => $options['shortname']))) {
                $this->delete_field($record);
            }

            require_once($CFG->dirroot.'/user/profile/definelib.php');
            require_once($CFG->dirroot.'/user/profile/field/'.$options['datatype'].'/define.class.php');
            $class = 'profile_define_'.$options['datatype'];

            /** @var $field profile_define_base */
            $field = new $class();
            $field->define_save((object) $options);

            $this->set_results($DB->get_record('user_info_field', array('shortname' => $options['shortname']), '*', MUST_EXIST));
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
            $this->delete_field($this->get_results());
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
        return $DB->record_exists('user_info_field', array('id' => $fixture->id));
    }

    /**
     * Does work of deleting the field
     *
     * @param stdClass $field
     */
    protected function delete_field($field) {
        global $DB;

        $DB->delete_records('user_info_data', array('id' => $field->id));
        $DB->delete_records('user_info_field', array('id' => $field->id));
    }

    /**
     * Set properties to use for the profile field
     *
     * @param array|object $options
     * @return mr_fixture_user_profile
     */
    public function set_options($options) {
        $this->options = (array) $options;
        return $this;
    }

    /**
     * Get the properties used for the profile field
     *
     * @return array
     */
    public function get_options() {
        return $this->options;
    }

    /**
     * @param \mr_fixture_user_profile_category $category
     * @return mr_fixture_user_profile
     */
    public function set_category(mr_fixture_user_profile_category $category) {
        $this->category = $category;
        return $this;
    }

    /**
     * @return \mr_fixture_user_profile_category
     */
    public function get_category() {
        return $this->category;
    }
}