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
 * @see mr_helper_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/helper/abstract.php');

/**
 * MR Helper Buffer
 *
 * This helper is used to buffer functions that
 * do not return their output.
 *
 * @author Mark Nielsen
 * @package mr
 * @see mr_helper_buffer::direct()
 * @example controller/default.php See this in action
 */
class mr_helper_buffer extends mr_helper_abstract {
    /**
     * Assists with calling functions that do no return output
     *
     * @param string $callback First param is a callback
     * @param mixed $argX Keep passing arguments to pass to the callback
     * @return string
     */
    public function direct() {
        $arguments = func_get_args();
        $callback  = array_shift($arguments);

        ob_start();
        call_user_func_array($callback, $arguments);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}