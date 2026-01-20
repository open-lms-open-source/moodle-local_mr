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

/**
 * Plugin API.
 *
 * @package   local_mr\api\traits
 * @copyright Copyright (c) 2020 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mr\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class json_persistor.
 * @package local_mr\api
 */
class json_persistor extends persistor {

    private $started = 0;

    public function flush() {
        if (empty($this->started)) {
            $handle = fopen($this->file, 'w');
            // Opening array bracket.
            fputs($handle, '[' . PHP_EOL);
        } else {
            $handle = fopen($this->file, 'a');
        }
        $rowsstr = '';
        foreach ($this->rows as $row) {
            $rowjson = json_encode($row);
            $rowsep = '';
            if ($this->started) {
                $rowsep = ',' . PHP_EOL;
            } else {
                $this->started = true;
            }
            $rowsstr .= $rowsep . $rowjson;
        }
        fputs($handle, $rowsstr);
        fclose($handle);
    }

    public function finish() {
        $handle = fopen($this->file, 'a');
        // Closing array bracket.
        fputs($handle, PHP_EOL . ']' . PHP_EOL);
        fclose($handle);
    }
}
