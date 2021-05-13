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

use coding_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class persistor.
 * @package local_mr\api
 */
abstract class persistor {

    public const MAX_UNPERSISTED_ROWS = 5000; // When this amount of rows is reached, the rows are flushed.

    /**
     * @var string The file where rows will be persisted.
     */
    protected $file;

    /**
     * Columns row.
     * @var array
     */
    protected $columns;

    /**
     * Unflushed rows.
     * @var array
     */
    protected $rows;

    public function __construct(string $file) {
        $this->columns = [];
        $this->clear_rows();
        $this->file = $file;
    }

    public function set_columns($columns) {
        $this->columns = $columns;
    }

    /**
     * @param $row
     * @throws coding_exception
     */
    public function add_row($row) {
        if (empty($this->file)) {
            throw new coding_exception('File path has not been set for persistence.');
        }

        $this->rows[] = $row;

        if (count($this->rows) >= self::MAX_UNPERSISTED_ROWS) {
            $this->flush();
            $this->clear_rows();
        }
    }

    public function get_file() : string {
        return $this->file;
    }

    public abstract function flush();

    public abstract function finish();

    private function clear_rows() {
        $this->rows = [];
    }
}
