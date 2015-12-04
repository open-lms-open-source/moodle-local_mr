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
 * MR File Export Text Abstract
 *
 * @package mr
 * @author Mark Nielsen
 */
abstract class mr_file_export_text_abstract extends mr_file_export_abstract {
    /**
     * The export file
     *
     * @var string
     */
    protected $file;

    /**
     * The file point to the export file
     *
     * @var resource
     */
    protected $fp;

    /**
     * Get the text delimiter
     *
     * @return string
     */
    abstract public function get_delimiter();

    /**
     * Make the directory for the file and open the file pointer
     *
     * @param string $name The preferred file name (no extension)
     * @param string $dir The directory to store the file in (temp by default)
     * @return void
     */
    public function init($name, $dir = NULL) {
        global $CFG;

        require_once($CFG->libdir.'/filelib.php');

        if (is_null($dir)) {
            $dir = $CFG->dataroot.'/temp';
        }
        check_dir_exists($dir, true, true);

        // Generate file path
        $name = clean_filename($name);
        $name = trim($name, '_');

        $this->file = "$dir/$name.".$this->get_extension();

        // Make sure it doesn't exist
        $this->cleanup();

        // Open for writing
        $this->fp = fopen($this->file, 'w+');
    }

    /**
     * Write headers to the file
     */
    public function set_headers($headers) {
        fputcsv($this->fp, $headers, $this->get_delimiter());
    }

    /**
     * Write the row to the file
     */
    public function add_row($row) {
        fputcsv($this->fp, $row, $this->get_delimiter());
    }

    /**
     * Close the file pointer and return file
     */
    public function close() {
        fclose($this->fp);
        return $this->file;
    }

    /**
     * Remove the csv file
     */
    public function cleanup() {
        global $CFG;

        require_once($CFG->libdir.'/filelib.php');

        if (file_exists($this->file)) {
            fulldelete($this->file);
        }
    }
}