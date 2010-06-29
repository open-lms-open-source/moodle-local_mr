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

/**
 * MR Preferences
 *
 * Keeps track of user's course preferences
 * in the session.  Feel free to extend this
 * class to provide some alternative storage
 * (EG: store in a database table) but keep
 * the session parts for caching hits.
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_preferences {
    /**
     * The plugin that the preferences belong
     *
     * @var string
     */
    protected $plugin = '';

    /**
     * The course ID
     *
     * @var int
     */
    protected $courseid = 0;

    /**
     * Constructor: set defaults
     *
     * @param int $courseid Course ID
     * @param string $plugin Plugin name, EG: blocks/blockname, block_blockname, etc
     */
    public function __construct($courseid, $plugin) {
        $this->process_args($courseid, $plugin);

        $this->courseid = $courseid;
        $this->plugin   = $plugin;
    }

    /**
     * Helper method - handle passed courseid and plugin values
     *
     * @param int $courseid Course ID
     * @param string $plugin Plugin name
     * @return void
     */
    protected function process_args(&$courseid, &$plugin) {
        if (is_null($plugin)) {
            $plugin = $this->plugin;
        }
        if (is_null($courseid)) {
            $courseid = $this->courseid;
        }
        if ($courseid == SITEID) {
            $courseid = 0;
        }
    }

    /**
     * Load the preferences for a user
     *
     * @return mr_preferences
     */
    public function load() {
        global $USER;

        if (!isset($USER->mr_preferences)) {
            $USER->mr_preferences = array();
        }
        return $this;
    }

    /**
     * Reload preferences
     *
     * @return mr_preferences
     */
    public function reload() {
        global $USER;

        // Unload
        unset($USER->mr_preferences);

        // Load
        return $this->load();
    }

    /**
     * Get current plugin value
     *
     * @return string
     */
    public function get_plugin() {
        return $this->plugin;
    }

    /**
     * Get current courseid
     *
     * @return int
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * Get a preference
     *
     * @param string $name Preference name
     * @param mixed $default Return this value if preference is not found
     * @param string $plugin Override plugin name
     * @param int $courseid Override course ID
     * @return mixed
     */
    public function get($name, $default = NULL, $plugin = NULL, $courseid = NULL) {
        global $USER;

        $this->load();
        $this->process_args($courseid, $plugin);

        if (isset($USER->mr_preferences[$courseid][$plugin][$name])) {
            return $USER->mr_preferences[$courseid][$plugin][$name];
        }
        return $default;
    }

    /**
     * Set a preference
     *
     * @param string $name Preference name
     * @param mixed $value Value to save
     * @param string $plugin Override plugin name
     * @param int $courseid Override course ID
     * @return mr_preferences
     */
    public function set($name, $value, $plugin = NULL, $courseid = NULL) {
        global $USER;

        $this->load();
        $this->process_args($courseid, $plugin);

        $USER->mr_preferences[$courseid][$plugin][$name] = $value;

        return $this;
    }

    /**
     * Delete a preference
     *
     * @param string $name Preference name
     * @param string $plugin Override plugin name
     * @param int $courseid Override course ID
     * @return mr_preferences
     */
    public function delete($name, $plugin = NULL, $courseid = NULL) {
        global $USER;

        $this->load();
        $this->process_args($courseid, $plugin);

        unset($USER->mr_preferences[$courseid][$plugin][$name]);

        return $this;
    }
}