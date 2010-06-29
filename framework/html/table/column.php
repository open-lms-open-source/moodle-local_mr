<?php
/**
 * Model Table Column
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->libdir.'/mr/bootstrap.php');
require_once($CFG->dirroot.'/blocks/reports/model/config.php');

class block_reports_model_column {

    /**
     * Column settings/configurations
     *
     * @var block_reports_model_config
     */
    protected $config;

    /**
     * Column formatting
     *
     * @var array
     */
    protected $formats = array();

    /**
     * Keep track of column suppression
     * Only used for printing HTML
     *
     * @var array
     */
    protected static $suppress = array();

    /**
     * Column definition
     *
     * @param string $field The SQL field that the column represents.  This param can take several forms.
     *                      Example: tablealias.fieldname OR someothername AS fieldname
     *                      If preferred, you can set the field via config and pass the resulting
     *                      fieldname here.
     * @param string $heading Column heading
     * @param array $config Override default configs
     */
    public function __construct($field, $heading = '', $config = array()) {

        $this->config = new block_reports_model_config();

        // Extract column name from field name
        if (stripos($field, ' AS ') !== false) {
            $name = preg_split('/ as /i', $field);
            $name = $name[1];
        } else {
            // Strip off alias if found
            $name = explode('.', $field);

            if (isset($name[1])) {
                $name = $name[1];
            } else {
                $name = $name[0];
            }
        }

        // General defaults
        $this->config->set(array(
            'name' => $name,          // Column name - this is what the resulting SQL field will be
            'heading' => $heading,    // Column heading
            'suppress' => false,      // Column supression, if true and rows contain same data, first will be shown
            'sortable' => true,       // Allow column to be sorted
            'sorting' => array(),     // Sorting rules, override to do multi column sorting, EG: array($name => NULL, 'nothercolumn' => SORT_ASC)
            'attributes' => array(),  // Column HTML attributes
            'group' => NULL,          // Future feature I guess, group columns together
            'field' => $field,        // The actual SQL select field, can be "something AS newname" etc...
            'editor' => false,        // YUI inline editor, shortcut value "textbox", see also http://developer.yahoo.com/yui/examples/datatable/dt_cellediting.html
        ));

        // Override defaults with passed configs
        $this->config->set($config);
    }

    /**
     * Get the column name
     *
     * @return string
     */
    public function get_name() {
        return $this->config->name;
    }

    /**
     * Get the select field for the SQL query
     *
     * @return mixed
     */
    public function get_select_field() {
        if (!empty($this->config->field)) {
            return $this->config->field;
        }
        return false;
    }

    /**
     * Return sorting rules for this column
     *
     * @return array
     */
    public function get_sorting() {
        if (empty($this->config->sorting)) {
            // Default sorting, by name and current direction
            return array($this->config->name => NULL);
        }
        return $this->config->sorting;
    }

    /**
     * Add column heading to passed param
     *
     * @param array $headings List of headings
     * @return void
     */
    public function add_heading(&$headings) {
        $headings[$this->config->name] = $this->config->heading;
    }

    /**
     * Does this column have a heading
     *
     * @return boolean
     */
    public function has_heading() {
        return !empty($this->config->heading);
    }

    /**
     * A config setter - overwrites old value
     *
     * @param string $name Name of the config to set
     * @param mixed $value The value of the config
     * @return block_reports_model_column
     */
    public function set_config($name, $value) {
        $this->config->$name = $value;
        return $this;
    }

    /**
     * Add a column format
     *
     * @param mixed $format This can be block_reports_model_format_abstract or a string
     *                      representing the format's name.  If a string, then keep passing
     *                      args which will be passed to the format's constructor
     * @return block_reports_model_column
     */
    public function add_format($format) {
        if (is_string($format)) {
            // Get remaining args, burn format
            $args = func_get_args();
            array_shift($args);

            $helper = new mr_helper('blocks/reports');
            $format = $helper->load("model/format/$format", $args);

        } else if (!$format instanceof block_reports_model_format_abstract) {
            throw new block_reports_exception('Invalid format parameter');
        }
        $this->formats[] = $format;

        return $this;
    }

    /**
     * Given a row from the SQL query, get the column's field value
     *
     * @param object $row The SQL row
     * @return mixed
     */
    public function extract_row_data($row) {
        if (array_key_exists($this->config->name, $row)) {
            $value = $row[$this->config->name];
            foreach ($this->formats as $format) {
                $value = $format->format($value);
            }
            return $value;
        }
        return false;
    }

    /**
     * Derive cell value from row and position
     *
     * @param int $position Current column position
     * @param object $row Database record object
     * @return string
     */
    public function get_cell($position, $row) {
        $cell = $this->extract_row_data($row);
        if ($cell === false) {
            $cell = '';
        }

        // Enfoce column suppression
        if ($this->config->suppress) {
            if (isset(self::$suppress[$position]) and self::$suppress[$position] == $cell) {
                $cell = '';  // Suppressed
            } else {
                if (isset(self::$suppress[$position]) and self::$suppress[$position] != $cell and $position == 0) {
                    self::$suppress = array();
                }
                self::$suppress[$position] = $cell;
            }
        }
        return $cell;
    }

    /**
     * Print Table Heading
     *
     * @param int $position The column position
     * @param moodle_url $url Base URL
     * @param string $sort The current sort column
     * @param string $order The current sort order
     * @return string
     */
    public function th(&$position, $url, $sort, $order) {
        global $CFG;

        // Adjust attributes for headers
        $attributes = $this->config->attributes;
        if (isset($attributes['class'])) {
            $attributes['class'] .= " header c$position";
        } else {
            $attributes['class'] = "header c$position";
        }
        $attributes['scope'] = 'col';

        // Figure out column sort controls
        if ($this->config->sortable) {
            if ($sort == $this->config->name) {
                if ($order == SORT_ASC) {
                    $icon    = ' <img src="'.$CFG->pixpath.'/t/down.gif" alt="'.get_string('asc').'" />';
                    $sortstr = get_string('asc');
                    $torder  = 'desc';
                } else {
                    $icon    = ' <img src="'.$CFG->pixpath.'/t/up.gif" alt="'.get_string('desc').'" />';
                    $sortstr = get_string('desc');
                    $torder  = 'asc';
                }
            } else {
                $icon    = '';
                $torder  = 'asc';
                $sortstr = get_string('asc');
            }
            $url     = $url->out(false, array('tsort' => $this->config->name, 'torder' => $torder));
            $heading = get_string('sortby').' '.$this->config->heading.' '.$sortstr;
            $heading = $this->config->heading.get_accesshide($heading);
            $heading = "<a href=\"$url\">$heading</a>$icon";
        } else {
            $heading = $this->config->heading;
        }
        return "\t\t<th".$this->attribute_string($attributes).">$heading</th>\n";
    }

    /**
     * Table header for AJAX view - generate
     * JS to define column header
     *
     * @return string
     */
    public function th_ajax() {
        if ($this->config->sortable) {
            $sortable = 'true';
        } else {
            $sortable = 'false';
        }
        $label = addslashes_js($this->config->heading);
        $name  = $this->get_name();

        if ($this->config->editor) {
            $editor = ", editor: {$this->config->editor}";
        } else {
            $editor = '';
        }

        return "{key:\"$name\", label:\"$label\", sortable:$sortable$editor}";
    }

    /**
     * Print Table Data cell
     *
     * @param int $position Current column position
     * @param object $row The current SQL row
     * @return string
     */
    public function td(&$position, $row) {
        // Adjust attributes for cells
        $attributes = $this->config->attributes;
        if (isset($attributes['class'])) {
            $attributes['class'] .= " cell c$position";
        } else {
            $attributes['class'] = "cell c$position";
        }
        $cell = $this->get_cell($position, $row);

        return "\t\t<td".$this->attribute_string($attributes).">$cell</td>\n";
    }

    /**
     * Get column name and value for AJAX - will be used
     * to generate record object for JSON
     *
     * @param int $position Current column position
     * @param object $row The current SQL row
     * @return array
     */
    public function td_ajax(&$position, $row) {
        return array($this->get_name() => $this->get_cell($position, $row));
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