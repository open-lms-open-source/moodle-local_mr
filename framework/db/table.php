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

/**
 * @see mr_db_record
 */
require_once($CFG->dirroot.'/local/mr/framework/db/record.php');

/**
 * MR DB Table
 *
 * Table abstraction.
 *
 * @author Mark Nielsen
 * @package mr
 * @example controller/db.php See this class in action
 */
class mr_db_table {
    /**
     * The name of the table
     *
     * @var string
     */
    protected $table;

    /**
     * Cache meta column data for tables
     *
     * @var array
     */
    static protected $columns = array();

    /**
     * Constructor
     *
     * @param string $table The table this model represents
     */
    public function __construct($table) {
        $this->table = $table;
    }

    /**
     * Display table name if casted to a string
     *
     * @return string
     */
    public function __toString() {
        return $this->get_name();
    }

    /**
     * Route calls to Moodle's lib/dmllib.php functions
     *
     * You can call the following lib/dmllib.php functions
     * through this class.  Pass all parameters as normal
     * except omit the first parameter which is the table name.
     * All methods return their same value as in lib/dmllib.php
     * except when noted otherwise.
     *<ul>
     *    <li>get_record (Returns a mr_db_record)</li>
     *    <li>get_record_select (Returns a mr_db_record)</li>
     *    <li>get_records (Returns an array of mr_db_record)</li>
     *    <li>get_records_select (Returns an array of mr_db_record)</li>
     *    <li>get_records_list (Returns an array of mr_db_record)</li>
     *    <li>count_records</li>
     *    <li>count_records_select</li>
     *    <li>delete_records</li>
     *    <li>delete_records_select</li>
     *    <li>get_field</li>
     *    <li>get_field_select</li>
     *    <li>get_records_menu</li>
     *    <li>get_records_select_menu</li>
     *    <li>record_exists</li>
     *    <li>record_exists_select</li>
     *    <li>set_field</li>
     *    <li>set_field_select</li>
     * </ul>
     *
     * Examples:
     * <code>
     * <?php
     *      $table = new mr_db_table('tablename');
     *      $records = $table->get_records(); // Gets all records in the table
     *      $record  = $table->get_record('field', $value); // Get record from table
     *      $count   = $table->count_records();
     * ?>
     * </code>
     *
     * @param string $name The function name
     * @param array $arguments The arguements to pass, exclude the table name!
     * @return mixed
     * @throws coding_exception
     * @see mr_db_record
     */
    public function __call($name, $arguments) {
        array_unshift($arguments, $this->table);

        switch ($name) {
            case 'get_record':
            case 'get_record_select':
            case 'get_records':
            case 'get_records_select':
            case 'get_records_list':
                return $this->convert(call_user_func_array($name, $arguments));
            case 'count_records':
            case 'count_records_select':
            case 'delete_records':
            case 'delete_records_select':
            case 'get_field':
            case 'get_field_select':
            case 'get_records_menu':
            case 'get_records_select_menu':
            case 'record_exists':
            case 'record_exists_select':
            case 'set_field':
            case 'set_field_select':
                return call_user_func_array($name, $arguments);
        }
        throw new coding_exception("Invalid method call to mr_db_table::$name()");
    }

    /**
     * Get the table's name
     *
     * @return string
     */
    public function get_name() {
        return $this->table;
    }

    /**
     * Get meta column data for the table
     *
     * @return array
     * @throws coding_exception
     */
    public function get_columns() {
        return array_combine(array_keys($this->get_metacolumns()), array_keys($this->get_metacolumns()));
    }

    /**
     * Get meta column data for the table
     *
     * @return array
     * @throws coding_exception
     */
    public function get_metacolumns() {
        global $CFG, $db;

        if (!array_key_exists($this->table, self::$columns)) {
            if (!$columns = $db->MetaColumns($CFG->prefix.$this->table)) {
                throw new coding_exception("Failed to get meta columns for database table $this->table");
            }
            // Change columns to lowercase for easier lookups
            foreach ($columns as $key => $column) {
                self::$columns[$this->table][strtolower($key)] = $column;
            }
        }
        return self::$columns[$this->table];
    }

    /**
     * Determine if a column exists in the table
     *
     * @param string $name The column name
     * @return boolean
     * @throws coding_exception
     */
    public function column_exists($name) {
        return array_key_exists($name, $this->get_columns());
    }

    /**
     * Convert an object or an array of objects into mr_db_records
     *
     * Example:
     * <code>
     * <?php
     *      $table = new mr_db_table('tablename');
     *      $table->convert(get_record(...));
     *      $table->convert(get_records(...));
     * ?>
     * </code>
     *
     * @param mixed $records Can be an object, an array of objects, or false
     * @return mixed
     */
    public function convert($records) {
        // If it doesn't match one of these if statements
        // then it is probably false or just a lost cause...
        if (is_object($records)) {
            $records = $this->record($records);
        } else if (is_array($records)) {
            foreach ($records as $key => $record) {
                $records[$key] = $this->record($record);
            }
        }
        return $records;
    }

    /**
     * Generate a record model for this table
     *
     * @param mixed $default Default record attributes
     * @return mr_db_record
     */
    public function record($default = array()) {
        return new mr_db_record($this, $default);
    }

    /**
     * Save data to the table
     *
     * @param mixed $data Array or object of record data
     * @param boolean $addslashes To add slashes to the data or not
     * @return void
     * @throws coding_exception
     */
    public function save($data, $addslashes = false) {
        if ($addslashes) {
            $this->record()->set($data)->addslashes()->save();
        } else {
            $this->record()->set($data)->save();
        }
    }

    /**
     * Get a row from the table
     *
     * @param int $id The record ID to get
     * @return mixed
     */
    public function get($id) {
        return $this->get_record('id', $id);
    }

    /**
     * Delete a row from the table
     *
     * @param string $id The record ID to delete
     * @return void
     * @throws coding_exception
     */
    public function delete($id) {
        if (!$this->delete_records('id', $id)) {
            throw new coding_exception("Failed to delete record with id = $id from table $this->table");
        }
    }
}