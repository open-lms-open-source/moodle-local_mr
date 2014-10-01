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
 * @see mr_file_export_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/file/export/abstract.php');

/**
 * MR File Export Spreadsheet Abstract
 *
 * @package mr
 * @author Mark Nielsen
 */
abstract class mr_file_export_spreadsheet_abstract extends mr_file_export_abstract {
    /**
     * Max rows per worksheet
     */
    const MAXROWS = 65535;

    /**
     * Workbook instance
     *
     * @var MoodleExcelWorkbook
     */
    protected $workbook;

    /**
     * Current worksheet
     *
     * @var MoodleExcelWorksheet
     */
    protected $writer;

    /**
     * Current row
     *
     * @var int
     */
    protected $row = 0;

    /**
     * Worksheet count
     *
     * @var int
     */
    protected $worksheet = 1;

    /**
     * Export name
     *
     * @var string
     */
    protected $name = '';

    /**
     * Cannot make files with the lib
     */
    public function generates_file() {
        return false;
    }

    /**
     * Open workbook and send to browser
     *
     * @param string $name The preferred file name (no extension)
     * @return void
     */
    public function init($name) {
        $this->name = $name;
        $filename   = clean_filename($this->name);
        $filename   = trim($filename, '_');

        $this->workbook = $this->new_workbook();
        $this->workbook->send("$filename.".$this->get_extension());

        // Adding the worksheet
        $this->writer = $this->workbook->add_worksheet("$this->name $this->worksheet");
    }

    /**
     * Write headers to the file
     */
    public function set_headers($headers) {
        $this->add_row($headers);
    }

    /**
     * Write the row to the file
     */
    public function add_row($row) {
        if ($this->row >= self::MAXROWS) {
            $this->worksheet++;

            $this->writer =& $this->workbook->add_worksheet("$this->name $this->worksheet");
            $this->row    = 0;
        }

        $column = 0;
        foreach ($row as $cell) {
            if (is_numeric($cell)) {
                $this->writer->write_number($this->row, $column, $cell);
            } else {
                $this->writer->write_string($this->row, $column, $cell);
            }
            $column++;
        }
        $this->row++;
    }

    /**
     * Close the file pointer and return file
     */
    public function close() {
        $this->workbook->close();
    }

    /**
     * Generate a new workbook
     *
     * @return mixed
     */
    abstract public function new_workbook();
}