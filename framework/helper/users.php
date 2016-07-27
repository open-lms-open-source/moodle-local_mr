<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * A helper class containing user related functions.
 *
 * @author    Corey Wallis
 * @package   mr
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * A helper class containing user related functions.
 *
 * @author    Corey Wallis
 * @package   mr
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mr_helper_users extends mr_helper_abstract {

    /**
     * Get a list of users by role
     *
     * @param array          $roleids    a list of roleids
     * @param context_course $context    a course context
     * @param bool           $parent     if true, get list of users assigned in higher context as well
     * @param string         $sort       the field(s) to sort the list on
     * @param int            $limitfrom  apply limit from record
     * @param int            $limitnum   limit number of returned records
     *
     * @return mixed array|false an array of records, or false if nothing found
     */
    public static function get_users_by_role($roleids, $context, $parent = false, $sort = null, $limitfrom = '', $limitnum = '') {

        if (is_null($sort)) {
            $sort = 'u.id';
        }

        // Get the list of users.
        $users = get_role_users($roleids, $context, $parent, 'ra.id raid, u.id', $sort, true, '', $limitfrom, $limitnum);

        // Return false if nothing is found.
        if (empty($users)) {
            return false;
        }

        // Process the returned records.
        $tmp = array();
        foreach ($users as $u) {
            if (!isset($tmp[$u->id])) {
                $o = new \stdClass();
                $o->id = $u->id;
                $tmp[$u->id] = $o;
            }
        }

        return $tmp;
    }
}
