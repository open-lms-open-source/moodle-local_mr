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
 * MR HTML Filter Recent
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_html_filter_recent extends mr_html_filter_abstract {

    /**
     * Default is zero
     */
    public function preferences_defaults() {
        return array($this->name => 0);
    }

    /**
     * Add select input
     */
    public function add_element($mform) {
        $options = array(
            0          => get_string('nolimit', 'local_mr'),
            '-1 day'   => get_string('oneday', 'local_mr'),
            '-1 week'  => get_string('oneweek', 'local_mr'),
            '-1 month' => get_string('onemonth', 'local_mr'),
            '-2 month' => get_string('xmonths', 'local_mr', 2),
            '-3 month' => get_string('xmonths', 'local_mr', 3),
            '-6 month' => get_string('xmonths', 'local_mr', 6),
            '-1 year'  => get_string('oneyear', 'local_mr')
        );

        $mform->addElement('select', $this->name, $this->label, $options);
        $mform->setType($this->name, PARAM_TEXT);
        $mform->setDefault($this->name, $this->preferences_get($this->name));

        if ($this->advanced) {
            $mform->setAdvanced($this->name);
        }
        return $this;
    }

    /**
     * Limit a field to being greater than our recent time
     */
    public function sql() {
        $preference = $this->preferences_get($this->name);

        if (!empty($preference) and ($time = strtotime($preference)) !== false) {
            return array("$this->field >= ?", $time);
        }
        return false;
    }
}