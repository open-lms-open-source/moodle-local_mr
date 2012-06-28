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
 * MR HTML Filter Abstract
 *
 * @author Mark Nielsen
 * @package mr
 */
abstract class mr_html_filter_abstract {
    /**
     * The filter's name
     *
     * @var string
     */
    protected $name;

    /**
     * Filter label in the form
     *
     * @var string
     */
    protected $label;

    /**
     * If it should be flagged as
     * advanced or not in the form
     *
     * @var boolean
     */
    protected $advanced;

    /**
     * SQL field name (if different from $name)
     *
     * @var string
     */
    protected $field;

    /**
     * User preferences (filter values are stored here)
     *
     * @var mr_preferences
     */
    protected $preferences;

    /**
     * Element help button parameters
     *
     * @var array
     */
    protected $helpbutton = array();

    /**
     * Element disabledIf parameters
     *
     * @var array
     */
    protected $disabledif = array();

    /**
     * Construct
     *
     * @param string $name Filter name
     * @param string $label Filter label
     * @param bool $advanced Filter advanced form setting
     * @param string $field SQL field, defaults to $name
     */
    public function __construct($name, $label, $advanced = false, $field = NULL) {
        $this->name     = $name;
        $this->label    = $label;
        $this->advanced = $advanced;

        if (is_null($field)) {
            $this->field = $this->name;
        } else {
            $this->field = $field;
        }
    }

    /**
     * Convert this filter into a simple string
     *
     * @return string
     */
    public function __toString() {
        $string = '';
        foreach ($this->preferences_defaults() as $name => $default) {
            $string .= "$name".$this->preferences_get($name);
        }
        return $string;
    }

    /**
     * Get the field's name
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Get the filter's primary form element's name
     *
     * This is used to attach help buttons, disabledIfs,
     * etc to the filter's element.
     *
     * @return string
     */
    public function get_element_name() {
        return $this->get_name();
    }

    /**
     * Get SQL field name
     *
     * @return string
     */
    public function get_field() {
        return $this->field;
    }

    /**
     * Filter defaults
     *
     * @return array
     */
    public function preferences_defaults() {
        return array($this->name => '');
    }

    /**
     * Preferences init
     *
     * @param mr_preferences $preferences
     * @return mr_html_filter_abstract
     */
    public function preferences_init($preferences) {
        $this->preferences = $preferences;

        return $this;
    }

    /**
     * Get a preference value (default can be returned)
     *
     * @param string $name Preference name
     * @return mixed
     */
    public function preferences_get($name) {
        $defaults = $this->preferences_defaults();

        return $this->preferences->get($name, $defaults[$name]);
    }

    /**
     * Update user preferences to current filter settings
     *
     * @param object $data Form data
     * @return mr_html_filter_abstract
     */
    public function preferences_update($data) {
        foreach ($this->preferences_defaults() as $name => $default) {
            if (!isset($data->$name) or $data->$name == $default) {
                $this->preferences_delete($name);
            } else {
                $this->preferences->set($name, stripslashes($data->$name));
            }
        }
        return $this;
    }

    /**
     * Remove preferences for this filter
     *
     * @param string $name A specific preference to delete, if NULL all are deleted
     * @return mr_html_filter_abstract
     */
    public function preferences_delete($name = NULL) {
        if (is_null($name)) {
            $names = array_keys($this->preferences_defaults());
        } else {
            $names = array($name);
        }
        foreach ($names as $name) {
            $this->preferences->delete($name);
        }
        return $this;
    }

    /**
     * Add a help button to this filter
     *
     * @param string $identifier Help button text identifier
     * @param string $component The plugin component
     * @return mr_html_filter_abstract
     */
    public function add_helpbutton($identifier, $component = 'moodle') {
        $this->helpbutton = array(
            'identifier' => $identifier,
            'component'  => $component,
        );
        return $this;
    }

    /**
     * Add disabledIf to this filter
     *
     * @param string $dependenton The name of the element whose state will be checked for condition
     * @param string $condition The condition to check
     * @param string $value Used in conjunction with condition.
     * @return mr_html_filter_abstract
     */
    public function add_disabledif($dependenton, $condition = 'notchecked', $value = '1') {
        $this->disabledif = array(
            'dependenton' => $dependenton,
            'condition'   => $condition,
            'value'       => $value,
        );
        return $this;
    }

    /**
     * Add all elements for this filter
     *
     * @param MoodleQuickForm $mform The filter form
     * @return mr_html_filter_abstract
     */
    public function add_elements($mform) {
        $this->add_element($mform);

        // Add help button
        if (!empty($this->helpbutton)) {
            $mform->addHelpButton(
                $this->get_element_name(),
                $this->helpbutton['identifier'],
                $this->helpbutton['component']
            );
        }

        // Add disabledIf
        if (!empty($this->disabledif)) {
            $mform->disabledIf(
                $this->get_element_name(),
                $this->disabledif['dependenton'],
                $this->disabledif['condition'],
                $this->disabledif['value']
            );
        }
        return $this;
    }

    /**
     * Add filter form element
     *
     * @param MoodleQuickForm $mform Filter form
     * @return mr_html_filter_abstract
     */
    abstract public function add_element($mform);

    /**
     * Generate this filter's SQL
     *
     * @return mixed
     */
    abstract public function sql();
}