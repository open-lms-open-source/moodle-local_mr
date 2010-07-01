<?php
/**
 * Model Table
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

/**
 * @see mr_readonly
 */
require_once($CFG->dirroot.'/local/mr/framework/readonly.php');

require_once($CFG->dirroot.'/local/mr/framework/html/table/column.php');
require_once($CFG->dirroot.'/local/mr/framework/html/table/column/dynamic.php');

class mr_html_table extends mr_readonly implements renderable {
    /**
     * Sort request param
     * @todo This is stupid
     */
    public $REQUEST_SORT = 'tsort';

    /**
     * Sort order request param
     * @todo This is stupid
     */
    public $REQUEST_ORDER = 'torder';

    /**
     * Table attributes
     *
     * @var array
     */
    protected $attributes = array(
        'class' => 'flexible generaltable boxwidthwide boxaligncenter',
    );

    /**
     * Columns
     *
     * @var array
     */
    protected $columns = array();

    /**
     * Table rows
     *
     * @var arrat
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
     * Route rows to an export plugin
     * instead of $this->rows
     *
     * @var block_reports_plugin_export_base_class
     */
    protected $export;

    /**
     * Cache ID to use
     *
     * @var string
     */
    protected $cachekey = NULL;

    /**
     * Preferences model
     *
     * @var block_reports_model_preferences
     */
    protected $preferences;

    /**
     * Helper model
     *
     * @var mr_helper
     */
    protected $helper;

    /**
     * Setup
     *
     * @param block_reports_model_preferences $preferences User preferences
     * @param moodle_url $url Base url
     * @param string $sort Sorting field
     * @param string $order Sorting order
     */
    public function __construct($preferences, $url, $sort = '', $order = SORT_ASC) {
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
            if ($orderp == 'asc') {
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
     * Set an export plugin.  Model will
     * send all rows to the plugin instead
     * of itself
     *
     * @param block_reports_plugin_export_base_class $export The export plugin
     * @return mr_html_table
     */
    public function set_export($export) {
        $headers = array();
        foreach ($this->columns as $column) {
            $column->add_heading($headers);
        }
        $export->set_headers($headers);

        $this->export = $export;
        return $this;
    }

    /**
     * Set the cache ID
     *
     * @param string $key The key to set
     * @return mr_html_table
     */
    public function set_cachekey($key) {
        $this->cachekey = $key;
        return $this;
    }

    /**
     * Is the table cached for this request or not
     *
     * @return boolean
     */
    public function cached() {
        if (!is_null($this->cachekey)) {
            return ($this->helper->cache->test($this->cachekey) and $this->helper->cache->test("pagingbar_$this->cachekey"));
        }
        return false;
    }

    /**
     * Gets all table columns
     *
     * This will expand dynamic columns and
     * include all of the returned columns
     * into the standard set.
     *
     * @return array
     */
    public function get_columns() {
        $return = array();
        foreach ($this->columns as $name => $column) {
            if ($column instanceof mr_html_table_column_dynamic) {
                $return = array_merge($return, $column->get_columns());
            } else {
                $return[$name] = $column;
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
        foreach ($this->columns as $column) {
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
     */
    public function get_sql_sort() {
        if (!empty($this->sort)) {
            if (array_key_exists($this->sort, $this->columns)) {
                $column = $this->columns[$this->sort];
            } else {
                // Try to find within dynamic column
                foreach ($this->columns as $dynamic) {
                    if ($dynamic instanceof mr_html_table_column_dynamic) {
                        if ($column = $dynamic->get_column($this->sort)) {
                            break;
                        }
                    }
                }
            }
            if (empty($column)) {
                throw new block_reports_exception('Invalid column sorting');
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
        $this->set_sortorder();

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
     * blocks/reports/model/column/
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
     * @param string $format Format name or block_reports_model_format_abstract
     * @param mixed $x Keep passing params to pass to the format's constructor
     * @return mr_html_table
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
                throw new block_reports_exception("Attempting to add a format to a nonexistant column: $column");
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

        // Send the row to export or to $this->rows
        if ($this->export) {
            // @todo Need to handle when html_table_cells and html_table_rows are passed
            $data = array();
            // @todo change $this->columns to get_columns() then wont have to worry about an array of cells
            foreach ($this->columns as $column) {
                $cell = $column->extract_row_data($row);

                if (is_array($cell)) {
                    $data = array_merge($data, $cell);
                } else if ($cell !== false) {
                    $data[] = $cell;
                } else {
                    $data[] = '';
                }
            }
            $this->export->add_row($data);
        } else {
            $this->rows[] = $row;
        }
        return $this;
    }
}