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
 * MR HTML Filter HTML
 *
 * @author Sam Chaffee
 * @package mr
 */
class mr_html_filter_html extends mr_html_filter_abstract {

    /**
     * @var string html for this element
     */
    protected $html;

    /**
     * HTML "filter" constructor
     *
     * @param string $name - name for the filter instance
     * @param string $html - html to be added to the form
     */
    public function __construct($name, $html) {
        parent::__construct($name, null, false, '');

        $this->html = $html;
    }

    /**
     * Add a html element to the form
     *
     * @param MoodleQuickForm $mform
     * @return block_reports_model_filter_header
     */
    public function add_element($mform) {
        $mform->addElement('html', $this->html);
        return $this;
    }

    /**
     * Stub method to override abstract method
     */
    public function sql() {}
}