<?php
/**
 * User preferences model
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

class block_reports_model_preferences {
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
     * Setup
     *
     * @param int $courseid Course ID
     * @param string $plugin Plugin name
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
     * @return block_reports_model_preferences
     */
    public function load() {
        global $USER;

        if (!isset($USER->block_reports_preferences)) {
            // Get ALL preferences for user
            $preferences = array();
            if ($prefs = get_records('block_reports_preferences', 'userid', $USER->id)) {
                foreach ($prefs as $pref) {
                    $preferences[$pref->courseid][$pref->plugin][$pref->name] = $pref->value;
                }
            }
            $USER->block_reports_preferences = $preferences;
        }
        return $this;
    }

    /**
     * Reload preferences
     *
     * @return block_reports_model_preferences
     */
    public function reload() {
        global $USER;

        // Unload
        unset($USER->block_reports_preferences);

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

        if (isset($USER->block_reports_preferences[$courseid][$plugin][$name])) {
            return $USER->block_reports_preferences[$courseid][$plugin][$name];
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
     * @return mixed
     */
    public function set($name, $value, $plugin = NULL, $courseid = NULL) {
        global $USER;

        $this->load();
        $this->process_args($courseid, $plugin);

        if (isset($USER->block_reports_preferences[$courseid][$plugin][$name]) and 
            $USER->block_reports_preferences[$courseid][$plugin][$name] == $value) {

            // Already set
            return true;
        }

        $record = new stdClass;
        $record->userid   = $USER->id;
        $record->courseid = $courseid;
        $record->plugin   = addslashes($plugin);
        $record->name     = addslashes($name);
        $record->value    = addslashes($value);

        if ($record->id = get_field_select('block_reports_preferences', 'id', "userid = $record->userid AND
                                                                               courseid = $record->courseid AND
                                                                               plugin = '$record->plugin' AND
                                                                               name = '$record->name'")) {
            $result = update_record('block_reports_preferences', $record);
        } else {
            $result = insert_record('block_reports_preferences', $record);
        }
        if ($result) {
            $USER->block_reports_preferences[$courseid][$plugin][$name] = $value;
        }
        return (boolean) $result;
    }

    /**
     * Delete a preference
     *
     * @param string $name Preference name
     * @param string $plugin Override plugin name
     * @param int $courseid Override course ID
     * @return mixed
     */
    public function delete($name, $plugin = NULL, $courseid = NULL) {
        global $USER;

        $this->load();
        $this->process_args($courseid, $plugin);

        if (!isset($USER->block_reports_preferences[$courseid][$plugin][$name])) {
            return true; // Not set already
        }

        $name   = addslashes($name);
        $plugin = addslashes($plugin);

        $result = (boolean) delete_records_select('block_reports_preferences', "userid = $USER->id AND
                                                                                courseid = $courseid AND
                                                                                plugin = '$plugin' AND
                                                                                name = '$name'");

        if ($result) {
            unset($USER->block_reports_preferences[$courseid][$plugin][$name]);
        }
        return $result;
    }
}