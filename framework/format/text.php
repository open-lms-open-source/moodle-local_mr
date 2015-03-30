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
 * Format text
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_format_text extends mr_format_abstract {
    /**
     * If the value is NULL, then return this value
     *
     * @var mixed
     */
    protected $isnull;

    /**
     * One of the FORMAT_* constants
     *
     * @var string
     */
    protected $format;

    /**
     * The format_text options
     *
     * @var array|object|null
     */
    protected $options;

    /**
     * Constructor
     *
     * @param mixed $isnull If the value being formatted is NULL, then use this value instead
     * @param string $format One of the FORMAT_* constants
     * @param array|object|null $options The format_text options
     */
    public function __construct($isnull = '', $format = FORMAT_MOODLE, $options = null) {
        $this->isnull  = $isnull;
        $this->format  = $format;
        $this->options = $options;
    }

    /**
     * Format the text
     */
    public function format($value) {
        if (is_null($value)) {
            return $this->isnull;
        }
        return format_text($value, $this->format, $this->options);
    }
}