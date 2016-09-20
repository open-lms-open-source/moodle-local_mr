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
 * @see mr_readonly
 */
require_once($CFG->dirroot.'/local/mr/framework/readonly.php');

/**
 * @see mr_html_paging
 */
require_once($CFG->dirroot.'/local/mr/framework/html/paging.php');

/**
 * @see mr_html_table
 */
require_once($CFG->dirroot.'/local/mr/framework/html/table.php');

/**
 * @see mr_html_filter
 */
require_once($CFG->dirroot.'/local/mr/framework/html/filter.php');

/**
 * @see mr_var
 */
require_once($CFG->dirroot.'/local/mr/framework/var.php');

/**
 * @see mr_preferences
 */
require_once($CFG->dirroot.'/local/mr/framework/preferences.php');

/**
 * @see mr_file_export
 */
require_once($CFG->dirroot.'/local/mr/framework/file/export.php');

/**
 * MR Report Abstract
 *
 * @package mr
 * @author Mark Nielsen
 * @example controller/report.php See how to render this class
 * @example report/users.php See how to extend this class
 */
abstract class mr_report_abstract extends mr_readonly implements renderable {
    /**
     * Table model
     *
     * @var mr_html_table
     */
    protected $table;

    /**
     * Paging model
     *
     * @var mr_html_paging
     */
    protected $paging;

    /**
     * Filter model
     *
     * @var mr_html_filter
     */
    protected $filter;

    /**
     * User preferences
     *
     * @var mr_preferences
     */
    protected $preferences;

    /**
     * Config Model
     *
     * @var mr_var
     */
    protected $config;

    /**
     * Base URL
     *
     * @var moodle_url
     */
    protected $url;

    /**
     * Course ID
     *
     * @var int
     */
    protected $courseid;

    /**
     * Export plugin
     *
     * @var mr_file_export
     */
    protected $export;

    /**
     * SQL executed by this report
     *
     * @var array
     */
    protected $executedsql = array();

    /**
     * Construct
     *
     * @param moodle_url $url Base URL
     * @param int $courseid Course ID
     * @param boolean $autorun Automatically run the report SQL and
     *                         retrieve rows for rendering or exporting
     */
    public function __construct(moodle_url $url, $courseid = NULL, $autorun = true) {
        if (is_null($courseid) or $courseid == 0) {
            $courseid = SITEID;
        }

        $this->url         = $url;
        $this->courseid    = $courseid;
        $this->config      = new mr_var();
        $this->preferences = new mr_preferences($courseid, $this->type());

        // Setup config defaults
        $this->config->set(array(
            'cache' => false,                // Enable report caching
            'ajax' => false,                 // Allow AJAX table view
            'ajaxdefault' => 1,              // Default for AJAX view
            'export' => false,               // Export options, an array of export formats or true for all
            'maxrows' => 65000,              // The maximum number of rows the report can report on
            'perpage' => false,              // Can the page size be changed?
            'perpageopts' => array(          // Page size options
                'all', 10, 25, 50, 100, 200, 500, 1000,
            ),
        ));
        $this->_init();

        if ($autorun) {
            $this->run();
        }
    }

    /**
     * Convert this report into a simple string
     *
     * @return string
     */
    public function __toString() {
        global $USER;

        $report = $this->type();
        return "user{$USER->id}course{$this->courseid}report{$report}$this->table$this->filter";
    }

    /**
     * Run init routines
     *
     * @return void
     */
    protected function _init() {
        $this->init();

        // Determine if we can turn ajax on.
        if (($forceajax = optional_param('forceajax', -1, PARAM_INT)) != -1) {
            $this->preferences->set('forceajax', $forceajax);
        }

        // Setup Paging.
        $this->paging = new mr_html_paging($this->preferences, $this->url);
        if ($this->config->perpage) {
            $this->paging->set_perpageopts($this->config->perpageopts);
        }

        // Setup Export.
        if ($this->config->export) {
            $this->export = new mr_file_export($this->config->export, false, $this->url, $this->name());
        }
    }

    /**
     * Set report specific configs
     *
     * @return void
     */
    public function init() {
    }

    /**
     * Filter setup - override to add a filter
     *
     * @return void
     */
    public function filter_init() {
    }

    /**
     * Table setup
     *
     * Override and set $this->table to an instance of
     * mr_html_table.
     *
     * @return void
     */
    abstract public function table_init();

    /**
     * Run the report SQL and retrieve rows for rendering or exporting.
     *
     * This method also sends the export to the browser if the
     * user is exporting the report.
     *
     * @return void
     */
    public function run() {
        // Initialize the tables and the filters
        $this->table_init();
        $this->filter_init();

        if ($this->filter instanceof mr_html_filter) {
            $this->filter->set_report($this);
        }
        // Determine if we are doing AJAX
        if (!$this->is_exporting() and $this->config->ajax and $this->preferences->get('forceajax', $this->config->ajaxdefault)) {
            if (optional_param('tjson', 0, PARAM_BOOL)) {
                // Filling on AJAX request
                $this->table_fill();
            }
        } else {
            // Normal fill...
            $this->table_fill();
        }

        // If exporting, send it to the browser
        if ($this->is_exporting()) {
            $this->export->send();
        }
    }

    /**
     * Get report description text
     *
     * @return mixed
     */
    public function get_description() {
        $identifier  = $this->type().'_description';
        if (get_string_manager()->string_exists($identifier, $this->get_component())) {
            return get_string($identifier, $this->get_component());
        }
        return false;
    }

    /**
     * Passed to get_string calls.
     *
     * @return string
     */
    abstract public function get_component();

    /**
     * Return a human readable name of the plugin
     *
     * @return string
     */
    public function name() {
        return get_string($this->type(), $this->get_component());
    }

    /**
     * Returns the plugin's name based on class name
     *
     * @return string
     */
    public function type() {
        return get_class($this);
    }

    /**
     * YUI inline cell editing - this gets called to save
     * the edited data.
     *
     * Also perform any additional capability checks in this method!
     *
     * @param object $row Table row data - THIS MUST BE CLEANED BEFORE USE!
     * @param string $column The column that was edited
     * @param string $value The new column value, THIS MUST BE CLEANED BEFORE SAVING!
     * @return mixed Return false on error, return value saved to DB on success,
     *               or return a JSON object (see editcell_action in default controller)
     * @todo What to do with this...
     */
    public function save_cell($row, $column, $value) {
        return false;
    }

    /**
     * Determine if the report is currently exporting
     *
     * This method must be called after _init() because the
     * export is setup during _init().
     *
     * @return boolean
     */
    public function is_exporting() {
        if ($this->export instanceof mr_file_export and $this->export->is_exporting()) {
            return true;
        }
        return false;
    }

    /**
     * Export report
     *
     * Example Code:
     * <code>
     * <?php
     *      $report = new some_report_class(...);
     *      $file   = $report->export('text/csv');
     *
     *      // Do something with $file, then to delete it...
     *      $report->get_export()->cleanup();
     * ?>
     * </code>
     *
     * @param string $exporter The exporter to use, like 'text/csv'
     * @param string $filename Override the file name
     * @return string The file path
     */
    public function export($exporter, $filename = NULL) {
        // Initialize the table and the filters
        $this->table_init();
        $this->filter_init();

        if ($this->filter instanceof mr_html_filter) {
            $this->filter->set_report($this);
        }
        // Set the exporter
        $this->export->init($exporter, $filename);

        // Send rows to export
        $this->table_fill();

        // Return the file
        return $this->export->close();
    }

    /**
     * Generate SQL from filter
     *
     * @return array
     */
    public function filter_sql() {
        if ($this->filter instanceof mr_html_filter) {
            return $this->filter->sql();
        }
        return array('1 = 1', array());
    }

    /**
     * Fill table with data
     *
     * @return void
     */
    public function table_fill() {
        if ($this->is_exporting()) {
            $this->table->set_export($this->export);
            $this->paging->set_export($this->export);
        }

        $total = $this->count_records($this->filter_sql());

        if ($this->config->maxrows == 0 or $total <= $this->config->maxrows) {
            $rs = $this->get_recordset(
                $this->filter_sql(),
                $this->table->get_sql_sort(),
                $this->paging->get_limitfrom(),
                $this->paging->get_limitnum()
            );
            foreach ($rs as $row) {
                $this->table_fill_row($row);
            }
            $rs->close();

            if ($this->paging->get_perpage() > 0) {
                $this->paging->set_total($total);
            }
        } else {
            $this->table->set_emptymessage(
                get_string('toomanyrows', 'local_mr', (object) array('total' => $total, 'max' => $this->config->maxrows))
            );
        }
    }

    /**
     * Add a row to the table
     *
     * @param mixed $row The row to add
     * @return void
     */
    public function table_fill_row($row) {
        $this->table->add_row($row);
    }

    /**
     * Get the recordset to the data for the report
     *
     * @param array $filter Filter SQL and params
     * @param string $sort Sort SQL
     * @param string $limitfrom Limit from SQL
     * @param string $limitnum Limit number SQL
     * @return moodle_recordset
     */
    public function get_recordset($filter = array(), $sort = '', $limitfrom = '', $limitnum = '') {
        global $DB;

        if (empty($filter)) {
            $filter = array('1 = 1', array());
        }
        list($filtersql, $filterparams) = $filter;
        list($sql, $params) = $this->get_sql($this->table->get_sql_select(), $filtersql, $filterparams);

        if (!empty($sort)) {
            $sql .= "\nORDER BY $sort";
        }
        $this->executedsql[] = array("$sql\nlimit $limitfrom, $limitnum", $params);

        return $DB->get_recordset_sql($sql, $params, $limitfrom, $limitnum);
    }

    /**
     * Count the total number of records
     * that are included in the report
     *
     * @param array $filter Filter SQL and params
     * @return int
     */
    public function count_records($filter = array()) {
        global $DB;

        if (empty($filter)) {
            $filter = array('1 = 1', array());
        }
        list($filtersql, $filterparams) = $filter;
        list($sql, $params)  = $this->get_count_sql($filtersql, $filterparams);
        $this->executedsql[] = array($sql, $params);

        return $DB->count_records_sql($sql, $params);
    }

    /**
     * Get the SQL to generate the report rows
     *
     * @param string $fields The fields to select
     * @param string $filtersql The filter SQL
     * @param array $filterparams The filter parameters
     * @return array Must return array(SQL, parameters)
     */
    abstract public function get_sql($fields, $filtersql, $filterparams);

    /**
     * Get the SQL to count to the total report rows
     *
     * @param string $filtersql The filter SQL
     * @param array $filterparams The filter parameters
     * @return array Must return array(SQL, parameters)
     */
    public function get_count_sql($filtersql, $filterparams) {
        return $this->get_sql('COUNT(*)', $filtersql, $filterparams);
    }

    /**
     * A hook into the rendering of the table.
     *
     * If you need to wrap the table in a form or anything
     * like that, then use this method.
     *
     * @param string $tablehtml The rendered table HTML
     * @return string
     */
    public function output_wrapper($tablehtml) {
        return $tablehtml;
    }

    /**
     * A hook into the filter's form definition, called after all
     * filters have been added.
     *
     * This is handy for form customizations, etc.  For major filter
     * form customizations, define your own filter form class and pass
     * the path to your new form to the mr_html_form constructor.
     *
     * @param MoodleQuickForm $mform
     */
    public function filter_definition_hook(MoodleQuickForm &$mform) {}
}