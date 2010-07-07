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
 * @see mr_format_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/format/abstract.php');

/**
 * MR Format Date
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_format_date extends mr_format_abstract {
    /**
     * If date is zero, use this value
     *
     * @var string
     */
    protected $ifempty = '';

    /**
     * Use this date format
     *
     * @var string
     */
    protected $format = '';

    /**
     * Constructor
     *
     * @param mixed $ifempty If the date is empty then use this value
     * @param string $format Date format, passed to userdate()
     */
    public function __construct($ifempty = '', $format = '') {
        $this->ifempty = $ifempty;
        $this->format  = $format;
    }

    /**
     * Format date
     */
    public function format($value) {
        if (empty($value)) {
            return $this->ifempty;
        } else {
            return userdate($value, $this->format);
        }
    }
}