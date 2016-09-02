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
 * @see mr_html_table_column
 */
require_once($CFG->dirroot.'/local/mr/framework/html/table/column.php');

/**
 * @see mr_html_table_column_dynamic
 */
require_once($CFG->dirroot.'/local/mr/framework/html/table/column/dynamic.php');

/**
 * MR HTML Table
 *
 * Used to generate a HTML table with sorting.
 *
 * @author Mark Nielsen
 * @package mr
 * @example controller/table.php See how to use this class
 * @todo Make html_table class available through this class?
 */
class mr_html_table extends mr_readonly implements renderable {
    /**
     * Sort request param
     */
    public $REQUEST_SORT = 'tsort';

    /**
     * Sort order request param
     */
    public $REQUEST_ORDER = 'torder';

    /**
     * Table attributes
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Columns
     *
     * @var mr_html_table_column[]
     */
    protected $columns = array();

    /**
     * Table rows
     *
     * @var array
     */
    protected $rows = array();

    /**
     * Sort enabled flag
     *
     * @var boolean
     */
    protected $sortenabled = true;

    /**
     * Current sort
     *
     * @var string
     */
    protected $sort;

    /**
     * The table's default sort
     *
     * @var string
     */
    protected $defaultsort;

    /**
     * Current sort order
     *
     * @var string
     */
    protected $order;

    /**
     * The table's default order
     *
     * @var string
     */
    protected $defaultorder;

    /**
     * Base URL
     *
     * @var moodle_url
     */
    protected $url;

    /**
     * Message to display when table is empty
     *
     * @var string
     */
    protected $emptymessage;

    /**
     * Export class
     *
     * When set through set_export(), then rows are routed
     * to this export class.
     *
     * @var mr_file_export
     */
    protected $export;

    /**
     * Preferences model
     *
     * @var mr_preferences
     */
    protected $preferences;

    /**
     * Helper model
     *
     * @var mr_helper
     */
    protected $helper;

    /**
     * Table caption
     *
     * As of writing, not supported by plain HTML rendering
     *
     * @var string
     */
    public $caption;

    /**
     * Setup
     *
     * @param mr_preferences $preferences User preferences
     * @param moodle_url $url Base url
     * @param string $sort Sorting field
     * @param int|string $order Sorting order
     */
    public function __construct($preferences, moodle_url $url, $sort = '', $order = SORT_ASC) {
        $this->url          = $url;
        $this->preferences  = $preferences;
        $this->helper       = new mr_helper();
        $this->defaultsort  = $sort;
        $this->defaultorder = $order;
        $this->emptymessage = get_string('nothingtodisplay');

        // Get prior sort information
        $this->sort  = optional_param($this->REQUEST_SORT, $preferences->get('sort', $sort), PARAM_SAFEDIR);
        $this->order = $preferences->get('order', $order);

        if ($orderp = optional_param($this->REQUEST_ORDER, '', PARAM_SAFEDIR)) {
            if ($orderp == SORT_ASC) {
                $this->order = SORT_ASC;
            } else {
                $this->order = SORT_DESC;
            }
        }
        // Save sort order
        $this->save_sortorder();
    }

    /**
     * Convert this table into a simple string
     *
     * @return string
     */
    public function __toString() {
        return "sort{$this->sort}order{$this->order}";
    }

    /**
     * Store sort and weight based on current settings
     *
     * @return void
     */
    protected function save_sortorder() {
        // Store sorting information if necessary
        if ($this->sort != $this->defaultsort) {
            // Sort not the same as default, save
            $this->preferences->set('sort', $this->sort);
        } else {
            // Sort same as default, remove
            $this->preferences->delete('sort');
        }
        if ($this->order != $this->defaultorder) {
            // Order not the same as default, save
            $this->preferences->set('order', $this->order);
        } else {
            // Order same as default, remove
            $this->preferences->delete('order');
        }
    }

    /**
     * Set the sorting column
     *
     * @param string $sort
     * @return mr_html_table
     */
    public function set_sort($sort){
        $this->sort = $sort;
        $this->save_sortorder();
        return $this;
    }

    /**
     * Set the direction of the sorting
     *
     * @param string $order
     * @return mr_html_table
     */
    public function set_order($order){
        $this->order = $order;
        $this->save_sortorder();
        return $this;
    }

    /**
     * Add or override table attributes
     *
     * @param array $attributes An array of attribute/value pairings
     * @return mr_html_table
     */
    public function set_attributes($attributes) {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }


    /**
     * Set the message for when the table is empty
     *
     * @param string $emptymessage The message to set
     * @return mr_html_table
     */
    public function set_emptymessage($emptymessage) {
        $this->emptymessage = $emptymessage;
        return $this;
    }

    /**
     * Set the export instance
     *
     * Table will send all rows to the plugin instead
     * of itself
     *
     * @param mr_file_export $export The export plugin
     * @return mr_html_table
     */
    public function set_export($export) {
        $this->export = $export;

        if ($export->is_exporting()) {
            $headers = array();
            foreach ($this->get_columns(true) as $column) {
                if ($column->get_config()->export) {
                    $column->add_heading($headers);
                }
            }
            foreach ($headers as $header) {
                if (!empty($header)) {
                    $export->instance()->set_headers($headers);
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Get table summary
     *
     * @return null|string
     */
    public function get_summary() {
        if (array_key_exists('summary', $this->attributes) and !empty($this->attributes['summary'])) {
            return $this->attributes['summary'];
        }
        return null;
    }

    /**
     * Gets all table columns
     *
     * This will expand dynamic columns and
     * include all of the returned columns
     * into the standard set.
     *
     * @param boolean $visible Return only columns that are visible to the user
     * @return mr_html_table_column[]
     */
    public function get_columns($visible = false) {
        $return = array();
        foreach ($this->columns as $name => $column) {
            if ($column instanceof mr_html_table_column_dynamic) {
                $return = array_merge($return, $column->get_columns());
            } else {
                $return[$name] = $column;
            }
        }
        if ($visible) {
            foreach ($return as $name => $column) {
                if (!$column->get_config()->display) {
                    unset($return[$name]);
                }
            }
        }

        return $return;
    }

    /**
     * Get select fields from the columns
     *
     * @return string
     */
    public function get_sql_select() {
        $fields = array();
        foreach ($this->get_columns() as $column) {
            if ($field = $column->get_select_field()) {
                $fields[] = $field;
            }
        }
        return implode(', ', $fields);
    }

    /**
     * Get sort SQL
     *
     * @return string
     * @throws coding_exception
     */
    public function get_sql_sort() {
        if (!empty($this->sort)) {
            // Find our column that we are sorting by
            $columns = $this->get_columns(true);
            if (!array_key_exists($this->sort, $columns)) {
                if (empty($this->defaultsort)) {
                    return ''; // Nothing to fallback on
                }
                if (!array_key_exists($this->defaultsort, $columns)) {
                    throw new coding_exception('Invalid column sorting ('.$this->defaultsort.')');
                }
                $column = $columns[$this->defaultsort];
            } else {
                $column = $columns[$this->sort];
            }

            $sort = array();
            foreach ($column->get_sorting() as $column => $order) {
                if (is_null($order)) {
                    $order = $this->order;
                }
                if ($order == SORT_ASC) {
                    $sort[] = "$column ASC";
                } else {
                    $sort[] = "$column DESC";
                }
            }
            return implode(', ', $sort);
        }
        return '';
    }

    /**
     * Disable sorting
     *
     * @return mr_html_table
     */
    public function disable_sort() {
        // Set flag
        $this->sortenabled = false;

        // Restore to defaults
        $this->sort  = $this->defaultsort;
        $this->order = $this->defaultorder;

        // Save
        $this->save_sortorder();

        return $this;
    }

    /**
     * Add a table column
     *
     * @param mixed $name Column SQL field name (see mr_html_table_column) OR
     *                     an instance of mr_html_table_column.  If the latter,
     *                     than $heading and $config are ignored.
     * @param string $heading Column heading
     * @param array $config Column configuration
     * @return mr_html_table
     */
    public function add_column($name, $heading = '', $config = array()) {
        if ($name instanceof mr_html_table_column) {
            $column = $name;
        } else {
            $column = new mr_html_table_column($name, $heading, $config);
        }
        $this->columns[$column->get_name()] = $column;

        return $this;
    }

    /**
     * Add a table column.  This method is a shortcut for
     * adding different column types.  Types are listed in
     * html/table/column/
     *
     * @param string $type The column type
     * @param string $name Column SQL field name (see mr_html_table_column)
     * @param string $heading Column heading
     * @param array $config Column configuration
     * @return mr_html_table
     */
    public function add_column_type($type, $name, $heading = '', $config = array()) {
        return $this->add_column(
            $this->helper->load("html/table/column/$type", array($name, $heading, $config))
        );
    }

    /**
     * Add column format
     *
     * @param mixed $columns Column name(s) to apply the format to
     * @param string $format Format name or mr_format_abstract
     * @param mixed $x Keep passing params to pass to the format's constructor
     * @return mr_html_table
     * @throws coding_exception
     */
    public function add_format($columns, $format) {
        // Get remaining args, burn columns
        $args = func_get_args();
        array_shift($args);

        if (!is_array($columns)) {
            $columns = array($columns);
        }
        foreach ($columns as $column) {
            if (!array_key_exists($column, $this->columns)) {
                throw new coding_exception("Attempting to add a format to a nonexistant column: $column");
            }
            call_user_func_array(array($this->columns[$column], 'add_format'), $args);
        }
        return $this;
    }

    /**
     * Add a row
     *
     * @param mixed $row Table row of data
     * @return mr_html_table
     */
    public function add_row($row) {
        if (is_object($row)) {
            $row = (array) $row;
        }
        // Send the row to export or store internally
        if ($this->export instanceof mr_file_export and $this->export->is_exporting()) {
            $this->export->instance()->add_row($this->extract_data($row));
        } else {
            $this->rows[] = $row;
        }
        return $this;
    }

    /**
     * Extract column data from a row while trying
     * to keep everything in the same order as the
     * columns.  Generally, don't need to call this
     * method unless you are micro managing an export.
     *
     * @param mixed $row Can be an object, array or html_table_row (if this, then ensure proper cell ordering!)
     * @return array
     */
    public function extract_data($row) {
        if (is_object($row)) {
            $row = (array) $row;
        }

        $data    = array();
        $columns = $this->get_columns(true);

        // Try our best with html_table_row
        if ($row instanceof html_table_row) {
            foreach ($row->cells as $cell) {
                if ($cell instanceof html_table_cell) {
                    $data[] = $cell->text;
                } else {
                    $data[] = $cell;
                }
            }
        } else {
            foreach ($columns as $key => $column) {
                if ($this->export instanceof mr_file_export and $this->export->is_exporting() and !$column->get_config()->export) {
                    unset($columns[$key]);
                    continue;
                }
                $cell = $column->get_cell($row);

                if ($cell instanceof html_table_cell) {
                    $data[] = $cell->text;
                } else {
                    $data[] = $cell;
                }
            }
        }
        // Make sure we return even number of columns
        if (count($data) < count($columns)) {
            $data = array_pad($data, count($columns), '');
        }
        return $data;
    }
}