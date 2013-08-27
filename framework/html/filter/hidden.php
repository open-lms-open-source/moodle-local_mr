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
 * MR HTML Filter hidden
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_html_filter_hidden extends mr_html_filter_abstract {
    /**
     * Value of the hidden field
     *
     * @var mixed
     */
    protected $_value;

    /**
     * Construct
     *
     * @param string $name Filter name
     * @param mixed $value Filter value
     * @param string $field SQL field, defaults to $name
     */
    public function __construct($name, $value, $field = NULL) {
        $this->_value = $value;

        parent::__construct($name, '', false, $field);
    }

    /**
     * Defaults to value
     */
    public function preferences_defaults() {
        return array($this->name, $this->_value);
    }

    /**
     * Add hidden field
     */
    public function add_element($mform) {
        $mform->addElement('hidden', $this->name, $this->_value);

        if (is_numeric($this->_value)) {
            $mform->setType($this->name, PARAM_INT);
        } else {
            $mform->setType($this->name, PARAM_TEXT);
        }

        return $this;
    }

    /**
     * Set field to value
     */
    public function sql() {
        return array("$this->field = ?", $this->_value);
    }
}