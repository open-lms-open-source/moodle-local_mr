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
 * MR Format Number
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_format_number extends mr_format_abstract {
    /**
     * Decimals to round to
     *
     * @var int
     */
    protected $decimals;

    /**
     * Units to use for the number
     *
     * @var string
     */
    protected $units;

    /**
     * If the value is NULL, then use this value
     *
     * @var mixed
     */
    protected $isnull;

    /**
     * Constructor
     *
     * @param int $decimals Number of decimal places to use
     * @param string $units Any units to append to the decimal
     * @param mixed $isnull The value to use when the number is null
     * @author Mark Nielsen
     */
    public function __construct($decimals = 2, $units = '', $isnull = 0) {
        $this->decimals = $decimals;
        $this->units    = $units;
        $this->isnull   = $isnull;
    }

    /**
     * Format number
     */
    public function format($value) {
        if (is_null($value)) {
            $value = $this->isnull;
        }
        return number_format($value, $this->decimals).$this->units;
    }
}