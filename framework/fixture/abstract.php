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
 * @see mr_fixture_interface
 */
require_once($CFG->dirroot.'/local/mr/framework/fixture/interface.php');

/**
 * MR Fixture Abstract
 *
 * @author Mark Nielsen
 * @package mr
 */
abstract class mr_fixture_abstract implements mr_fixture_interface {
    /**
     * @var stdClass
     */
    protected $results;

    public function __construct() {
        $this->set_results(new stdClass);
    }

    /**
     * Get a property from the build results
     *
     * Build results vary from fixture to fixture
     * so not all fixtures may respond the same.
     *
     * @param string $name The name of the property to get from the build results
     * @return mixed
     * @throws moodle_exception
     * @throws coding_exception
     */
    public function get($name) {
        $results = $this->get_results();
        if (property_exists($results, $name)) {
            return $results->{$name};
        }
        // Try to help...
        if (!$this->exists()) {
            $debuginfo = 'This fixture hasn\'t been built yet, try calling build() prior to using get(...)';
        } else {
            $debuginfo = null;
        }
        throw new coding_exception("The property '$name'' does not exist in the fixture build results.", $debuginfo);
    }

    /**
     * Get the build results
     *
     * What is returned is largely up to the fixture
     * so be sure you know which type of fixture you
     * are using.
     *
     * @return stdClass
     */
    public function get_results() {
        return $this->results;
    }

    /**
     * Set the build results
     *
     * This value is highly dependent upon the fixture
     * class that you are using.  Be sure to know which
     * type of fixture you are using.
     *
     * @param stdClass $results The results to set
     * @return mr_fixture_interface
     */
    public function set_results($results) {
        $this->results = $results;
        return $this;
    }
}