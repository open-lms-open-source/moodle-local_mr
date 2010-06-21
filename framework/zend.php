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

/**
 * Zend Framework
 *
 * Assists with using the Zend Framework Library
 *
 * @package mr
 * @author Mark Nielsen
 */
class mr_zend {
    /**
     * Set the Zend Framework library include path
     *
     * @return void
     */
    public static function set_includepath() {
        global $CFG;

        // Don't init twice!
        static $init = false;

        if (!$init) {
            // Include path for Zend
            $includepath = get_include_path();
            $searchpath  = $CFG->dirroot.'/search';
            $zendpath    = $CFG->libdir.'/zend';

            // Don't add twice
            if (strpos($includepath, $zendpath) === false) {
                set_include_path($searchpath . PATH_SEPARATOR . $zendpath . PATH_SEPARATOR . $includepath);
            }

            // Init done!
            $init = true;
        }
    }
}