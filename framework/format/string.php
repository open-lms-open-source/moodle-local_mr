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
 * Format string
 *
 * @author Sam Chaffee
 * @author Mark Nielsen
 * @package mr
 */
class mr_format_string extends mr_format_abstract {
    /**
     * If the value is NULL, then return this value
     *
     * @var mixed
     */
    protected $isnull;

    /**
     * Run the string through format_string()
     *
     * @var string
     */
    protected $format;

    /**
     * This gets passed to format_string()
     *
     * @var boolean
     */
    protected $striplinks;

    /**
     * Constructor
     *
     * @param mixed $isnull If the value being formatted is NULL, then use this value instead
     * @param boolean $format Run the value though format_string()
     * @param boolean $striplinks Passed to format_string() when $format = true
     */
    public function __construct($isnull = '', $format = true, $striplinks = true) {
        $this->isnull     = $isnull;
        $this->format     = $format;
        $this->striplinks = $striplinks;
    }

    /**
     * Format the string
     */
    public function format($value) {
        if (is_null($value)) {
            return $this->isnull;
        } else if ($this->format) {
            $value = format_string($value, $this->striplinks);
        }
        return $value;
    }
}