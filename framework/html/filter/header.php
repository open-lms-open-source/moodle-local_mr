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
 * MR HTML Filter Header
 *
 * @author Sam Chaffee
 * @package mr
 */
class mr_html_filter_header extends mr_html_filter_abstract {

    /**
     * Header "filter" constructor
     *
     * @param string $name - name for the filter instance
     * @param string $label - label to the left of the checkbox
     */
    public function __construct($name, $label) {
        parent::__construct($name, $label, false, '');
    }

    /**
     * Add filter form element
     *
     * @param MoodleQuickForm $mform Filter form
     * @return mr_html_filter_abstract
     */
    public function add_element($mform) {
        $mform->addElement('header', $this->name, $this->label);
        return $this;
    }

    /**
     * Stub method to override abstract method
     */
    public function sql() {}
}