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
 * @see mr_var
 */
require_once($CFG->dirroot.'/local/mr/framework/var.php');

/**
 * MR HTML Table Column
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_html_table_column {
    /**
     * Column settings/configurations
     *
     * @var mr_var
     */
    protected $config;

    /**
     * Column formatting
     *
     * @var array
     */
    protected $formats = array();

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

        $this->config = new mr_var();

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
            'display' => true,        // Display the column in the table rendering.  Set to false when you want the field fetched in the SQL but not displayed (EG: id)
            'suppress' => false,      // Column supression, if true and rows contain same data, first will be shown
            'sortable' => true,       // Allow column to be sorted
            'sorting' => array(),     // Sorting rules, override to do multi column sorting, EG: array($name => NULL, 'nothercolumn' => SORT_ASC)
            'attributes' => array(),  // Column HTML attributes
            'group' => NULL,          // Future feature I guess, group columns together
            'field' => $field,        // The actual SQL select field, can be "something AS newname" etc...
            'editor' => false,        // YUI inline editor, shortcut value "textbox", see also http://developer.yahoo.com/yui/examples/datatable/dt_cellediting.html
            'export' => true,         // If set to false, then this column is not exported
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
        if (!$this->config->display) {
            return false;
        }
        return !empty($this->config->heading);
    }

    /**
     * A config setter - overwrites old value
     *
     * @param string $name Name of the config to set
     * @param mixed $value The value of the config
     * @return mr_html_table_column
     */
    public function set_config($name, $value) {
        $this->config->set($name, $value);
        return $this;
    }

    /**
     * Get config object
     *
     * @return mr_var
     */
    public function get_config() {
        return $this->config;
    }

    /**
     * Add a column format
     *
     * @param mixed $format This can be mr_format_abstract or a string
     *                      representing the format's name.  If a string, then keep passing
     *                      args which will be passed to the format's constructor
     * @return mr_html_table_column
     * @throws coding_exception
     */
    public function add_format($format) {
        if (is_string($format)) {
            // Get remaining args, burn format
            $args = func_get_args();
            array_shift($args);

            $helper = new mr_helper();
            $format = $helper->load("format/$format", $args);

        } else if (!$format instanceof mr_format_abstract) {
            throw new coding_exception('Invalid format parameter');
        }
        $this->formats[] = $format;

        return $this;
    }

    /**
     * Derive cell value from row
     *
     * @param array $row Generally database record object
     * @return mixed
     * @throws coding_exception
     */
    public function get_cell($row) {
        if ($row instanceof html_table_row) {
            throw new coding_exception('Cannot get cell from html_table_row classes. '.
                                       'This must be done before adding the cell to a '.
                                       'html_table_row instance');
        }
        if (array_key_exists($this->config->name, $row)) {
            $value = $row[$this->config->name];

            // Apply all formats to the value
            foreach ($this->formats as $format) {
                if ($value instanceof html_table_cell) {
                    $value->text = $format->format($value->text);
                } else {
                    $value = $format->format($value);
                }
            }
            return $value;
        }
        return '';
    }
}