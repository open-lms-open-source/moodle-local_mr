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
 * MR File Export
 *
 * Manages exporting of data to
 * files and the different export
 * formats.
 *
 * @package mr
 * @author Mark Nielsen
 */
class mr_file_export implements renderable {
    // Need a way to say...
    // Only x y z plugins are ok to use
    // Only plugins that generate files
    // Request var
    // Methods for sending file and closing...

    protected $instance = NULL;

    public function __construct(...) {


        if ($mrexport = optional_param('mrexport', '', PARAM_SAFEDIR) {
            $this->init($mrexport);
        }
    }

    public function instance() {
        if (!$this->instance instanceof ) {
            throw new coding_exception('Must call init() before the export instance is available');
        }
        return $this->instance;
    }

    public function init($format) {
        // Set this
        $this->exporting = true;

        // Bump to 5 minutes
        set_time_limit((MINSECS * 5));

        // Make the export instance
        helper->load

        // Perform export
        $export->init($report->name());
    }

    public function close() {
        // Close, may return a file
        $file = $export->close();

        // Any cleanup
        $export->cleanup();

        return $file;
    }

    public function send() {
        // Close, may return a file
        $file = $export->close();

        if ($export->generates_file() and file_exists($file)) {
            $content = file_get_contents($file);
        }

        // Any cleanup
        $export->cleanup();

        // Send contents for download (only for exports that generate a file)
        if (!empty($content)) {
            send_file($content, pathinfo($file, PATHINFO_BASENAME), 'default', 0, true, true);
        }
        die;
    }

    public function is_exporting() {
        return $this->exporting;
    }
}