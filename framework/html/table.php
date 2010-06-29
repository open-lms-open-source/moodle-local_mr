<?php
/**
 * Model Table
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->libdir.'/mr/bootstrap.php');
require_once($CFG->dirroot.'/blocks/reports/model/column.php');
require_once($CFG->dirroot.'/blocks/reports/model/column/dynamic.php');

class block_reports_model_table {
    /**
     * Page request param
     */
    protected $REQUEST_PAGE = 'tpage';

    /**
     * Page request param
     */
    protected $REQUEST_PERPAGE = 'tperpage';

    /**
     * Sort request param
     */
    protected $REQUEST_SORT = 'tsort';

    /**
     * Sort request param
     */
    protected $REQUEST_ORDER = 'torder';

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
     * Current page
     *
     * @var int
     */
    protected $page = 0;

    /**
     * Per page
     *
     * @var int
     */
    protected $perpage = 50;

    /**
     * Per page options
     *
     * @var mixed
     */
    protected $perpageopts = false;

    /**
     * Total rows for table
     *
     * @var int
     */
    protected $rowtotal = 0;

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
        $this->page         = optional_param($this->REQUEST_PAGE, 0, PARAM_INT);
        $this->perpage      = optional_param($this->REQUEST_PERPAGE, $this->perpage, PARAM_INT);
        $this->preferences  = $preferences;
        $this->helper       = new mr_helper('blocks/reports');
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
        $this->set_sortorder();
    }

    /**
     * Convert this table into a simple string
     *
     * @return string
     */
    public function __toString() {
        return "page{$this->page}perpage{$this->perpage}sort{$this->sort}order{$this->order}";
    }

    /**
     * Store sort and weight based on current settings
     *
     * @return void
     */
    protected function set_sortorder() {
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
     * @return block_reports_model_table
     */
    public function set_attributes($attributes) {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Set perpage
     *
     * @param int $size Page size
     * @return block_reports_model_table
     */
    public function set_perpage($size) {
        $this->perpage = $size;
        return $this;
    }

    /**
     * Perpage options
     *
     * @param mixed $options An array of options or false
     * @return block_reports_model_table
     */
    public function set_perpageopts($options) {
        $this->perpageopts = $options;
        return $this;
    }

    /**
     * Get the perpage size
     *
     * @return int
     */
    public function get_perpage() {
        return $this->perpage;
    }

    /**
     * Set rowtotal
     *
     * @param int $total The total
     * @return block_reports_model_table
     */
    public function set_rowtotal($total) {
        $this->rowtotal = $total;
        return $this;
    }

    /**
     * Set the message for when the table is empty
     *
     * @param string $emptymessage The message to set
     * @return block_reports_model_table
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
     * @return block_reports_model_table
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
     * @return block_reports_model_table
     */
    public function set_cachekey($key) {
        $this->cachekey = $key;
        return $this;
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
                    if ($dynamic instanceof block_reports_model_column_dynamic) {
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
            return ' ORDER BY '.implode(', ', $sort);
        }
        return '';
    }

    /**
     * Get limitfrom SQL value
     *
     * @return int
     */
    public function get_limitfrom() {
        return $this->page * $this->perpage;
    }

    /**
     * Get limitnum SQL value
     *
     * @return mixed
     */
    public function get_limitnum() {
        if (empty($this->perpage)) {
            return '';
        }
        return $this->perpage;
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
     * Disable sorting
     *
     * @return block_reports_model_table
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
     * @param mixed $name Column SQL field name (see block_reports_model_column) OR
     *                     an instance of block_reports_model_column.  If the latter,
     *                     than $heading and $config are ignored.
     * @param string $heading Column heading
     * @param array $config Column configuration
     * @return block_reports_model_table
     */
    public function add_column($name, $heading = '', $config = array()) {
        if ($name instanceof block_reports_model_column) {
            $column = $name;
        } else {
            $column = new block_reports_model_column($name, $heading, $config);
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
     * @param string $name Column SQL field name (see block_reports_model_column)
     * @param string $heading Column heading
     * @param array $config Column configuration
     * @return block_reports_model_table
     */
    public function add_column_type($type, $name, $heading = '', $config = array()) {
        return $this->add_column(
            $this->helper->load("model/column/$type", array($name, $heading, $config))
        );
    }

    /**
     * Add column format
     *
     * @param mixed $columns Column name(s) to apply the format to
     * @param string $format Format name or block_reports_model_format_abstract
     * @param mixed $x Keep passing params to pass to the format's constructor
     * @return block_reports_model_table
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
     * @param array $attributes Row attributes
     * @return block_reports_model_table
     */
    public function add_row($row, $attributes = array()) {

        $row = (array) $row;

        // Send the row to export or to $this->rows
        if ($this->export) {
            $data = array();
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
            $this->rows[] = array(
                'data' => $row,
                'attributes' => $attributes,
            );
        }
        return $this;
    }

    /**
     * Table to HTML
     *
     * @return string
     */
    public function html() {
        if (is_null($this->cachekey) or !$html = $this->helper->cache->load($this->cachekey)) {
            $html = "\n<table".$this->attribute_string($this->attributes).">\n";

            // Check if we have any column headings
            $haveheadings = false;
            foreach ($this->columns as $column) {
                if ($column->has_heading()) {
                    $haveheadings = true;
                    break;
                }
            }

            if ($haveheadings) {
                $html    .= "\t<tr class=\"headers\">\n";
                $position = 0;
                foreach ($this->columns as $column) {
                    // Must set sortable to false if table is not sort enabled or if empty $rows
                    if (!$this->sortenabled or empty($this->rows)) {
                        $column->set_config('sortable', false);
                    }
                    $html .= $column->th($position, $this->url, $this->sort, $this->order);
                    $position++;
                }
                $html .= "\t</tr>\n";
            }

            if (empty($this->rows)) {
                $colcount = count($this->columns);
                $html    .= "\t<tr class=\"r0\"><td class=\"cell c0 nothing\" colspan=\"$colcount\">$this->emptymessage</td></tr>\n";
            } else {
                foreach ($this->rows as $count => $row) {
                    $attributes = $row['attributes'];
                    if (!isset($attributes['class'])) {
                        $attributes['class'] = 'r'.($count % 2);
                    } else {
                        $attributes['class'] = 'r'.($count % 2).' '.$attributes['class'];
                    }

                    $html .= "\t<tr".$this->attribute_string($attributes).">\n";

                    $position = 0;
                    foreach ($this->columns as $column) {
                        $html .= $column->td($position, $row['data']);
                        $position++;
                    }
                    $html .= "\t</tr>\n";
                }
            }
            $html .= "</table>\n";

            // Save to cache
            if (!is_null($this->cachekey)) {
                $this->helper->cache($html, $this->cachekey);
            }
        }
        return $html;
    }

    /**
     * Return the paging bar HTML
     *
     * @return string
     */
    public function html_perpage() {
        static $count = 1;

        if (is_null($this->cachekey) or ($bar = $this->helper->cache->load("pagingbar_$this->cachekey")) === false) {
            if (!empty($this->perpage)) {
                $bar = print_paging_bar($this->rowtotal, $this->page, $this->perpage, $this->url, $this->REQUEST_PAGE, false, true);
            } else {
                $bar = '';
            }
            if ($this->perpageopts) {
                $options = array();
                foreach ($this->perpageopts as $opt) {
                    if ($opt == 'all') {
                        $options[10000] = get_string('all');
                    } else {
                        $options[$opt] = $opt;
                    }
                }
                $choose = '&nbsp;'.popup_form($this->url->out()."&amp;$this->REQUEST_PERPAGE=", $options, "perpageformidXXX", $this->perpage, '', '', '', true);

                if (substr($bar, strlen($bar)-6) == '</div>') {
                    // Place it within the paging bar
                    $bar = substr($bar, 0, -6)."$choose</div>";
                } else {
                    $bar .= print_box(get_string('pagesize', 'block_reports').$choose, 'centerpara', '', true);
                }
            }
            // Save to cache
            if (!is_null($this->cachekey)) {
                $this->helper->cache($bar, "pagingbar_$this->cachekey");
            }
        }

        // This allows us to use the cache for multiple paging bars
        $bar = str_replace('perpageformidXXX', "perpageformid$count", $bar);
        $count++;

        return $bar;
    }

    /**
     * Attributes array to a string
     *
     * @param array $attributes Array
     * @return string
     */
    protected function attribute_string($attributes) {
        if (empty($attributes)) {
            return '';
        }
        $pairs = array();
        foreach ($attributes as $name => $value) {
            $pairs[] = "$name=\"".s($value).'"';
        }
        return ' '.implode(' ', $pairs);
    }
}