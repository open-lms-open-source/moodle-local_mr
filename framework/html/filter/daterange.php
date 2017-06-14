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
 * @see mr_html_filter_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/html/filter/abstract.php');

/**
 * MR HTML Filter Date Range
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_html_filter_daterange extends mr_html_filter_abstract {
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
        return array($this->name.'_sd' => 0, $this->name.'_ed' => 0);
    }

    /**
     * Enforce checkboxes - if not set
     * then set date to 0
     */
    public function preferences_update($data) {
        $name = "{$this->name}_sc";
        if (empty($data->$name)) {
            $name = "{$this->name}_sd";
            $data->$name = 0;
        }
        $name = "{$this->name}_ec";
        if (empty($data->$name)) {
            $name = "{$this->name}_ed";
            $data->$name = 0;
        }
        return parent::preferences_update($data);
    }

    /**
     * Add date fields and checkboxes to enable/disable
     */
    public function add_element($mform) {
        // Naming
        //  s = start
        //  c = checkbock
        //  d = date_selector
        //  e = end

        // Creating subgroups to keep each checkbox aligned to its date selector.
        $subgroup1[] =& $mform->createElement('checkbox', $this->name.'_sc', null, get_string('isafter', 'filters'));
        $subgroup1[] =& $mform->createElement('date_selector', $this->name.'_sd', null);
        $mform->addGroup($subgroup1, $this->name.'_grp1', $this->label, ' ', false);
        $subgroup2[] =& $mform->createElement('checkbox', $this->name.'_ec', null, get_string('isbefore', 'filters'));
        $subgroup2[] =& $mform->createElement('date_selector', $this->name.'_ed', null);
        $mform->addGroup($subgroup2, $this->name.'_grp2', '', ' ', false);

        if ($this->advanced) {
            $mform->setAdvanced($this->name.'_grp');
        }

        // Handle defaults
        $mform->setDefault($this->name.'_sd', $this->preferences_get($this->name.'_sd'));
        $mform->setDefault($this->name.'_ed', $this->preferences_get($this->name.'_ed'));
        $mform->setDefault($this->name.'_sc', (int) $this->preferences_get($this->name.'_sd'));
        $mform->setDefault($this->name.'_ec', (int) $this->preferences_get($this->name.'_ed'));

        // Disable date fields when checkbox is not checked
        $mform->disabledIf($this->name.'_sd[day]', $this->name.'_sc', 'notchecked');
        $mform->disabledIf($this->name.'_sd[month]', $this->name.'_sc', 'notchecked');
        $mform->disabledIf($this->name.'_sd[year]', $this->name.'_sc', 'notchecked');
        $mform->disabledIf($this->name.'_ed[day]', $this->name.'_ec', 'notchecked');
        $mform->disabledIf($this->name.'_ed[month]', $this->name.'_ec', 'notchecked');
        $mform->disabledIf($this->name.'_ed[year]', $this->name.'_ec', 'notchecked');

        return $this;
    }

    /**
     * Set limits on field
     */
    public function sql() {
        $sql    = array();
        $params = array();

        $preference = $this->preferences_get($this->name.'_sd');
        if (!empty($preference)) {
            $sql[]    = "$this->field >= ?";
            $params[] = $preference;
        }
        $preference = $this->preferences_get($this->name.'_ed');
        if (!empty($preference)) {
            // Note, we may want "$this->field > 0 AND " added to the following
            $sql[]    = "$this->field <= ?";
            $params[] = $preference;
        }

        if (!empty($sql)) {
            return array(implode(' AND ', $sql), $params);
        }
        return false;
    }
}