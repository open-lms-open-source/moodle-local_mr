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
 * @author Sam Chaffee
 */

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * @see mr_html_filter_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/html/filter/abstract.php');

/**
 * MR HTML Filter Number Range
 *
 * @author Sam Chaffee
 * @package mr
 */
class mr_html_filter_numberrange extends mr_html_filter_abstract {

    /**
     * The low value default
     *
     * @var mixed
     */
    protected $lv_default;

    /**
     * The high value default
     *
     * @var mixed
     */
    protected $hv_default;

    /**
     * Construct
     *
     * @param string $name Filter name
     * @param string $label Filter label
     * @param bool $advanced Filter advanced form setting
     * @param string $field SQL field, defaults to $name
     * @param int $lv_default Low value default
     * @param int $hv_default High value default
     */
    public function __construct($name, $label, $advanced = false, $field = NULL, $lv_default = 0, $hv_default = 0) {
        parent::__construct($name, $label, $advanced, $field);

        $this->lv_default = $lv_default;
        $this->hv_default = $hv_default;
    }

    /**
     * Return the group element
     */
    public function get_element_name() {
        return $this->name.'_grp';
    }

    /**
     * Defaults for two fields
     */
    public function preferences_defaults() {
        return array($this->name.'_lv' => $this->lv_default, $this->name.'_hv' => $this->hv_default);
    }

    /**
     * Enforce checkboxes - if not set
     * then set date to 0
     */
    public function preferences_update($data) {
        $name = "{$this->name}_lc";
        if (empty($data->$name)) {
            $name = "{$this->name}_lv";
            $data->$name = 0;
        }
        $name = "{$this->name}_hc";
        if (empty($data->$name)) {
            $name = "{$this->name}_hv";
            $data->$name = 0;
        }
        return parent::preferences_update($data);
    }

    /**
     * Add text fields and checkboxes to enable/disable
     */
    public function add_element($mform) {
        // Naming
        //  l = low
        //  c = checkbox
        //  v = textbox
        //  h = high

        $attributes = array('size' => 5);

        $group[] =& $mform->createElement('checkbox', $this->name.'_lc', null, get_string('greaterthanoreq', 'block_reports'));
        $group[] =& $mform->createElement('text', $this->name.'_lv', null, $attributes);
        $group[] =& $mform->createElement('checkbox', $this->name.'_hc', null, get_string('lessthanoreq', 'block_reports'));
        $group[] =& $mform->createElement('text', $this->name.'_hv', null, $attributes);
        $mform->addElement('group', $this->name.'_grp', $this->label, $group, '', false);

        if ($this->advanced) {
            $mform->setAdvanced($this->name.'_grp');
        }

        // Handle defaults
        $mform->setDefault($this->name.'_lv', $this->preferences_get($this->name.'_lv'));
        $mform->setDefault($this->name.'_hv', $this->preferences_get($this->name.'_hv'));
        $mform->setDefault($this->name.'_lc', (int) $this->preferences_get($this->name.'_lv'));
        $mform->setDefault($this->name.'_hc', (int) $this->preferences_get($this->name.'_hv'));

        // Set the type
        $mform->setType($this->name.'_lv', PARAM_FLOAT);
        $mform->setType($this->name.'_hv', PARAM_FLOAT);

        // Disable text fields when checkbox is not checked
        $mform->disabledIf($this->name.'_lv', $this->name.'_lc', 'notchecked');
        $mform->disabledIf($this->name.'_hv', $this->name.'_hc', 'notchecked');

        return $this;
    }

    /**
     * Set limits on field
     */
    public function sql() {
        $sql = array();
        $params = array();

        $preference = $this->preferences_get($this->name.'_lv');
        if (!empty($preference)) {
            $sql[] = "$this->field >= ?";
            $params[] = $preference;
        }
        $preference = $this->preferences_get($this->name.'_hv');
        if (!empty($preference)) {
            // Note, we may want "$this->field > 0 AND " added to the following
            $sql[] = "$this->field <= ?";
            $params[] = $preference;
        }

        if (!empty($sql)) {
            return array(implode(' AND ', $sql), $params);
        }
        return false;
    }
}
