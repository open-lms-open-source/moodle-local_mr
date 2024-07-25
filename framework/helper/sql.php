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
 * @author    Adam Olley
 * @package   mr
 * @copyright Copyright (c) 2016 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mr_helper_sql extends mr_helper_abstract {

    /**
     * Get a SQL snippet for limiting returned rows.
     *
     * This should only be used where its necessary to limit the rows in a
     * subquery. When limiting the rows to an entire query you should make use
     * of the core DML parameters for this.
     *
     * @param int $limitfrom the offset (number of records to skip)
     * @param int $limitnum  the maximum number of records to return
     * @return string
     */
    public static function limit($limitfrom = 0, $limitnum = 0) {
        global $DB;
        $sql = '';

        // The SQL building below is taken from dml class for the respecitve db families.
        switch ($DB->get_dbfamily()) {
            case 'mysql':
                if ($limitfrom or $limitnum) {
                    if ($limitnum < 1) {
                        $limitnum = "18446744073709551615";
                    }
                    $sql = "LIMIT $limitfrom, $limitnum";
                }
                break;
            case 'postgres':
                if ($limitfrom or $limitnum) {
                    if ($limitnum < 1) {
                        $limitnum = "ALL";
                    } else if (PHP_INT_MAX - $limitnum < $limitfrom) {
                        // this is a workaround for weird max int problem
                        $limitnum = "ALL";
                    }
                    $sql = "LIMIT $limitnum OFFSET $limitfrom";
                }
                break;
            default:
                $sql = '';
        }
        return $sql;
    }

    /**
     * Get a SQL snippet for retrieving the human readable date from a unix
     * timestamp.
     *
     * Format takes the format used by phps date functions and translates it to
     * the values needed by the DB. i.e:
     *
     * $format: n/d/Y
     * mysql: %c/%d/%Y
     * postgres: fmMM/DD/YYYY
     *
     * Date format docs:
     * https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_date-format
     * https://www.postgresql.org/docs/8.1/static/functions-formatting.html#FUNCTIONS-FORMATTING-DATETIME-TABLE
     *
     * @param string $field  The field in table to extract the date from.
     * @param string $format What format to return the date in.
     * @return string
     */
    public static function from_unixtime($field, $format = 'n/d/Y') {
        global $DB;

        $search = ['Y', 'd', 'm', 'n', 'F'];

        $sql = "";
        $dbformat = $format;
        switch ($DB->get_dbfamily()) {
            case 'mysql':
                $replace = ['%Y', '%d', '%m', '%c', '%M'];
                foreach ($search as $i => $char) {
                    $dbformat = str_replace($char, $replace[$i], $dbformat);
                }
                $sql = "FROM_UNIXTIME($field, '$dbformat')";
                break;
            case 'postgres':
                $replace = ['YYYY', 'DD', 'MM', 'fmMM', 'fmMonth'];
                foreach ($search as $i => $char) {
                    $dbformat = str_replace($char, $replace[$i], $dbformat);
                }
                $sql = "to_char(date(to_timestamp($field)), '$dbformat')";
                break;
            default:
                $sql = "''";
        }
        return $sql;
    }
}
