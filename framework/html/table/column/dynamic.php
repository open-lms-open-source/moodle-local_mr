<?php
/**
 * Model Table Column Dynamic
 *
 * This column type represents a dynamic list
 * of columns.  Most methods redirect the call
 * to all of its contained columns.
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/local/mr/framework/html/table/column.php');

class mr_html_table_column_dynamic extends mr_html_table_column {

    /**
     * The dynamic columns
     *
     * @var array
     */
    private $columns = array();

    /**
     * Add a column to the model
     *
     * @param mr_html_table_column $column The column object
     * @return mr_html_table_column_dynamic
     */
    public function add_column($column) {
        $this->columns[$column->get_name()] = $column;
        return $this;
    }

    /**
     * Get a column
     *
     * @param string $name The column name
     * @return mixed
     */
    public function get_column($name) {
        if (array_key_exists($name, $this->columns)) {
            return $this->columns[$name];
        }
        return false;
    }

    /**
     * Get all columns
     *
     * @return array
     */
    public function get_columns() {
        return $this->columns;
    }

    /**
     * Get select fields of all dynamic columns
     */
    public function get_select_field() {
        $fields = array();
        foreach ($this->columns as $column) {
            if ($field = $column->get_select_field()) {
                $fields[] = $field;
            }
        }
        return implode(', ', $fields);
    }

    /**
     * Add headings of all dynamci columns
     */
    public function add_heading(&$headings) {
        foreach ($this->columns as $column) {
            $column->add_heading($headings);
        }
    }

    /**
     * Check all columns for headings
     */
    public function has_heading() {
        foreach ($this->columns as $column) {
            if ($column->has_heading()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set config to all columns
     */
    public function set_config($name, $value) {
        foreach ($this->columns as $column) {
            $column->set_config($name, $value);
        }
        return $this;
    }

    /**
     * Add format to all columns
     */
    public function add_format($format) {
        $args = func_get_args();
        foreach ($this->columns as $column) {
            call_user_func_array(array($column, 'add_format'), $args);
        }
        return $this;
    }

    /**
     * Get row data from all columns
     */
    public function extract_row_data($row) {
        $data = array();
        foreach ($this->columns as $column) {
            $cell = $column->extract_row_data($row);

            if (is_array($cell)) {
                $data += $cell;
            } else if ($cell !== false) {
                $data[] = $cell;
            } else {
                $data[] = '';
            }
        }
        return $data;
    }

    /**
     * Table header for AJAX view
     */
    public function th_ajax() {
        $th = array();
        foreach ($this->columns as $column) {
            $th[] = $column->th_ajax();
        }
        return implode(',', $th);
    }

    /**
     * Get column name and value for AJAX
     *
     * @return array
     */
    public function td_ajax(&$position, $row) {
        $tds = array();
        foreach ($this->columns as $column) {
            $tds = array_merge($tds, $column->td_ajax($position, $row));
            $position++;
        }
        $position--; // Back off one...

        return $tds;
    }
}