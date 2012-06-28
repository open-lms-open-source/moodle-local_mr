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
 * @see mr_html_table_column
 */
require_once($CFG->dirroot.'/local/mr/framework/html/table/column.php');

/**
 * Model Table Column Dynamic
 *
 * This column type represents a dynamic list
 * of columns.  Most methods redirect the call
 * to all of its contained columns.
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_html_table_column_dynamic extends mr_html_table_column {
    /**
     * The dynamic columns
     *
     * @var mr_html_table_column[]
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
     * @return mr_html_table_column|boolean
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
     * @return mr_html_table_column[]
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
     * Add headings of all dynamic columns
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
}