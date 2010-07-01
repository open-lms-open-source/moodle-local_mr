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
 * MR HTML Filter Multiple Select
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_html_filter_selectmulti extends mr_html_filter_abstract {
    /**
     * Select options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Defaults
     *
     * @var array
     */
    protected $defaults = array();

    /**
     * Adding an options param for the select options
     */
    public function __construct($name, $label, $options, $defaults = array(), $advanced = false, $field = NULL) {
        parent::__construct($name, $label, $advanced, $field);
        $this->options  = $options;
        $this->defaults = $defaults;
    }

    /**
     * First option is default
     */
    public function preferences_defaults() {
        return array($this->name => implode(',', $this->defaults));
    }

    /**
     * Enforce checkboxes - if not set
     * then set date to 0
     */
    public function preferences_update($data) {
        $raw = optional_param($this->name, '', PARAM_RAW);
        if (!empty($raw) and !empty($data->{$this->name})) {
            $data->{$this->name} = implode(',', $data->{$this->name});
        } else {
            $data->{$this->name} = '';
        }
        return parent::preferences_update($data);
    }

    /**
     * Add select input
     */
    public function add_element($mform) {
        $mform->addElement('select', $this->name, $this->label, $this->options)->setMultiple(true);

        if ($defaults = $this->preferences_get($this->name)) {
            $mform->setDefault($this->name, explode(',', $defaults));
        }
        if ($this->advanced) {
            $mform->setAdvanced($this->name);
        }

        return $this;
    }

    /**
     * Limit by input value
     */
    public function sql() {
        global $db;

        $preference = $this->preferences_get($this->name);
        if (!empty($preference)) {
            $preference = explode(',', $preference);

            if (count($preference) == 1) {
                if (is_numeric($preference[0])) {
                    return $this->field.' = '.$preference[0];
                }
                return $this->field.' = '.$db->quote($preference[0]);
            } else {
                $values = array();
                foreach ($preference as $value) {
                    if (is_numeric($value)) {
                        $values[] = $value;
                    } else {
                        $values[] = $db->quote($value);
                    }
                }
                return $this->field.' IN ('.implode(',', $values).')';
            }
        }
        return false;
    }
}