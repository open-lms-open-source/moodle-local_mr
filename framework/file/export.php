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
 * @example controller/table.php See how to use this class
 */
class mr_file_export implements renderable {

    /**
     * Flag for if exporting is currently underway or not
     *
     * @var boolean
     */
    protected $exporting = false;

    /**
     * Loaded exporters, only these can be used
     *
     * @var array
     */
    protected $exporters = array();

    /**
     * Export file name
     *
     * @var string
     */
    protected $filename;

    /**
     * Exporter instance
     *
     * @var mr_file_export_abstract
     */
    protected $instance = NULL;

    /**
     * Moodle URL - used for rendering
     *
     * @var moodle_url
     */
    protected $url;

    /**
     * Exporter setup
     *
     * There are many ways to define which exporters are available.  The $exporters
     * param can be a string or an array of strings that get sent to the mr_helper_load class.
     *
     * Examples:
     * <code>
     * <?php
     *      // Load all exporters
     *      $export = new mr_file_export('**');
     *
     *      // Load only text exporters
     *      $export = new mr_file_export('text/*');
     *
     *      // Load all spreadsheet and text/csv exporters
     *      $export = new mr_file_export(array('text/csv', 'spreadsheet/*'));
     *
     *      // Load an instance of a class that extends mr_file_export_abstract
     *      $export = new mr_file_export(new blocks_dummy_file_export_csv());
     *
     *      // Load an instance and all text exporters
     *      $export = new mr_file_export(array('text/*', new blocks_dummy_file_export_csv()));
     *
     * ?>
     * </code>
     *
     * @param mixed $exporters This can take on many forms, see above for examples.
     * @param boolean $requirefile If true, then no export plugin will be included that cannot generate a file
     * @param moodle_url $url Moodle URL for current page, used for rendering only
     * @param string $filename The exported file's name
     * @throws coding_exception
     */
    public function __construct($exporters = '**', $requirefile = false, moodle_url $url = NULL, $filename = 'export') {
        // Store params
        $this->url = $url;
        $this->set_filename($filename);

        if (!is_array($exporters)) {
            $exporters = array($exporters);
        }

        // Load exporters
        $helper = new mr_helper();
        foreach ($exporters as $exporter) {
            //check to see if $exporter is an instance of mr_file_export_absract
            if ($exporter instanceof mr_file_export_abstract) {
                //add the exporter instance the exporters data member
                $this->exporters[$exporter->type()] = $exporter;

                //next exporter
                continue;
            }
            $plugins = $helper->load("file/export/$exporter");

            // Might return a single plugin
            if (!is_array($plugins)) {
                $plugins = array($plugins);
            }
            foreach ($plugins as $plugin) {
                $this->exporters[$plugin->type()] = $plugin;
            }
        }
        // Make sure we successfully loaded some
        if (empty($this->exporters)) {
            throw new coding_exception('Failed to load exporters, check the $exporters param value');
        }

        // If files are requred, then weed out any exporters that cannot produce a file
        if ($requirefile) {
            foreach ($this->exporters as $name => $exporter) {
                if (!$exporter->generates_file()) {
                    unset($this->exporters[$name]);
                }
            }
            if (empty($this->exporters)) {
                throw new coding_exception('All loaded exporters do not generate files, but files required');
            }
        }

        // Auto-detect if we are exporting, if yes, fire 'er up!
        if ($exporter = optional_param('mrexporter', '', PARAM_PATH)) {
            $this->init($exporter);
        }
    }

    /**
     * Once an export has started, you can get access
     * to the current exporter through this method.
     *
     * @throws coding_exception
     * @return mr_file_export_abstract
     */
    public function instance() {
        if (!$this->instance instanceof mr_file_export_abstract) {
            throw new coding_exception('Must call init() before the export instance is available');
        }
        return $this->instance;
    }

    /**
     * Are we currently exporting?
     *
     * @return boolean
     */
    public function is_exporting() {
        return $this->exporting;
    }

    /**
     * Start exporting process
     *
     * Can be manually called.
     *
     * @param string $exporter The exporter to use
     * @param string $filename Optionally change the file name
     * @return mr_file_export
     * @throws coding_exception
     */
    public function init($exporter, $filename = NULL) {
        if ($this->is_exporting()) {
            throw new coding_exception('Cannot re-init while exporting');
        }
        $this->set_filename($filename);

        // Set this
        $this->exporting = true;

        // Bump to 5 minutes
        core_php_time_limit::raise((MINSECS * 5));

        // Set the exporter instance
        if (!array_key_exists($exporter, $this->exporters)) {
            throw new coding_exception('The passed exporter is not one of the available exporters');
        }
        $this->instance = $this->exporters[$exporter];

        // Start export
        $this->instance->init($this->filename);
        return $this;
    }

    /**
     * Stop exporting and if the exporter
     * returns a file, then the file is returned,
     * otherwise, always returns false.
     *
     * To remove the export file from the file
     * system, you must call mr_file_export::cleanup()
     *
     * @return mixed
     */
    public function close() {
        if ($this->is_exporting()) {
            // Close, may return a file
            $file = $this->instance->close();

            // Flip this off
            $this->exporting = false;

            if (file_exists($file)) {
                return $file;
            }
        }
        return false;
    }

    /**
     * Calls the exporters cleanup.
     *
     * If the exporter generates a file, then the file
     * is deleted.
     *
     * @return mr_file_export
     */
    public function cleanup() {
        if ($this->instance instanceof mr_file_export_abstract) {
            $this->instance->cleanup();
        }
        return $this;
    }

    /**
     * Closes, cleans and sends the exported file to the
     * browser for download.
     *
     * If a file is sent, this method will kill the PHP script!
     *
     * @return void
     */
    public function send() {
        global $CFG;

        require_once($CFG->libdir.'/filelib.php');

        if ($this->is_exporting()) {
            // We might get a file
            $file = $this->close();

            // Grab file contents if we got em
            if ($file !== false) {
                $content = file_get_contents($file);
            }

            // Any cleanup (will delete the file)
            $this->cleanup();

            // Send contents for download (only for exports that generate a file)
            if (!empty($content)) {
                send_file($content, pathinfo($file, PATHINFO_BASENAME), 0, 0, true, true);
            }
            die;
        }
    }

    /**
     * Change the export file name
     *
     * @param string $filename The new file name
     * @return mr_file_export
     * @throws coding_exception
     */
    public function set_filename($filename) {
        if ($this->is_exporting()) {
            throw new coding_exception('Cannot change the file name while exporting');
        }
        if (!is_null($filename)) {
            $this->filename = $filename;
        }
        return $this;
    }

    /**
     * Get the URL
     *
     * @return moodle_url
     * @throws coding_exception
     */
    public function get_url() {
        if (!$this->url instanceof moodle_url) {
            throw new coding_exception('Must pass an instance of moodle_url');
        }
        return $this->url;
    }

    /**
     * Get select options for the currently available exporters
     *
     * @return array
     */
    public function get_select_options() {
        $options = array();
        foreach ($this->exporters as $type => $exporter) {
            $options[$type] = $exporter->name();
        }
        return $options;
    }

    /**
     * Get URL select options for the currently available exporters
     *
     * @return array
     */
    public function get_url_select_options() {
        $options = array();
        foreach ($this->exporters as $type => $exporter) {
            $url = clone($this->get_url());
            $url->param('mrexporter', $type);
            $options[$url->out(false)] = $exporter->name();
        }
        return $options;
    }
}