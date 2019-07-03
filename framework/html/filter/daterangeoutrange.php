<?php
/**
 * Blackboard Open LMS framework
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
 * @copyright Copyright (c) 2019 Blackboard Inc. (http://www.blackboard.com)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @package mr
 * @author Sebastian Gracia
 */

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * @see mr_html_filter_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/html/filter/abstract.php');

/**
 * MR HTML Filter Date Range.
 *
 * @author Sebastian Gracia
 * @package mr
 */
class mr_html_filter_daterangeoutrange extends mr_html_filter_daterange {

    /**
     * Set limits on field.
     */
    public function sql() {
        $sql    = array();
        $params = array();

        $preference = $this->preferences_get($this->name.'_sd');
        if (!empty($preference)) {
            $sql[]    = "$this->field < ?";
            $params[] = $preference;
        }
        $preference = $this->preferences_get($this->name.'_ed');
        if (!empty($preference)) {
            // Note, we may want "$this->field > 0 AND " added to the following.
            $sql[]    = "$this->field > ?";
            $params[] = $preference;
        }

        if (!empty($sql)) {
            return array(implode(' OR ', $sql), $params);
        }
        return false;
    }
}
