<?php
/**
 * Base Export
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/local/mr/framework/plugin.php');

abstract class mr_file_export_abstract extends mr_plugin {
    /**
     * Passed to get_string calls.
     *
     * Implementing abstract mr_plugin::get_component()
     *
     * @return string
     */
    public function get_component() {
        return 'local_mr';
    }

    /**
     * If the export plugin generates a file or not
     * If yes, then can be used for emails
     *
     * @return boolean
     */
    public function generates_file() {
        return true;
    }

    /**
     * The max export size that the export plugin can handle
     *
     * Zero is unlimited
     *
     * @return int
     */
    public function max_rows() {
        return 0;
    }

    /**
     * Init routines, params can be unique to the plugin
     *
     * @return void
     */
    public function init() {}

    /**
     * Header setup
     *
     * @param array $headers Array of header names in correct order
     * @return void
     */
    abstract public function set_headers($headers);

    /**
     * Add a row to the export
     *
     * @param array $row Row cell values
     * @return void
     */
    abstract public function add_row($row);

    /**
     * Close the export and return whatever the export generated
     *
     * @return mixed
     */
    abstract public function close();

    /**
     * Run any cleanup routines
     *
     * @return void
     */
    public function cleanup() {}
}