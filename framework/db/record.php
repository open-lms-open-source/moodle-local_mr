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
 * @see mr_db_table
 */
require_once($CFG->dirroot.'/local/mr/framework/db/table.php');

/**
 * MR DB Record
 *
 * Table record abstraction.  Keep track of changes
 * and only write to db when necessary.
 *
 * @author Mark Nielsen
 * @package mr
 * @example controller/db.php See this class in action
 */
class mr_db_record implements ArrayAccess, IteratorAggregate, Countable {
    /**
     * The record's table
     *
     * @var mr_db_table
     */
    protected $_table;

    /**
     * The record
     *
     * @var object
     */
    protected $_record;

    /**
     * Changed values in the record
     *
     * @var object
     */
    protected $_change;

    /**
     * Is the record flagged to be deleted
     *
     * @var boolean
     */
    protected $_delete = false;

    /**
     * If true, then checks for column exists are bypassed.
     * Only use when performance is an issue (EG: processing hundreds
     * of thousands) and that you KNOW all columns are correct
     *
     * @var boolean
     */
    protected $trustcolumns = false;

    /**
     * Constructor
     *
     * @param mixed $table The table name or an instance of mr_db_table
     * @param mixed $default The default record data - just setting this will not trigger a save
     * @param boolean $trustcolumns If true, then checks for column exists are bypassed.
     *                             Only use when performance is an issue (EG: processing hundreds
     *                             of thousands) and that you KNOW all columns are correct
     */
    public function __construct($table, $default = NULL, $trustcolumns = false) {
        if ($table instanceof mr_db_table) {
            $this->_table = $table;
        } else {
            $this->_table = new mr_db_table($table);
        }
        $this->_change      = new stdClass;
        $this->_record      = new stdClass;
        $this->trustcolumns = $trustcolumns;

        // Apply default to _record
        if (!is_null($default)) {
            if (is_array($default)) {
                $default = (object) $default;
            }
            $this->_record = $default;

            if (!$this->trustcolumns) {
                // Remove ID field if it is invalid
                if (empty($this->_record->id) or !is_number($this->_record->id)) {
                    unset($this->_record->id);
                }

                // Remove values that are not in the table
                $columns = $this->_table->get_metacolumns();
                foreach ($this->_record as $name => $value) {
                    if (!array_key_exists($name, $columns)) {
                        unset($this->_record->$name);
                    }
                }
            }
        }
    }

    /**
     * Set a value to the record.  Records changes.
     *
     * @param string $name
     * @param mixed $value
     * @throws coding_exception
     * @return void
     */
    public function __set($name, $value) {
        if (!$this->trustcolumns and !$this->_table->column_exists($name)) {
            throw new coding_exception("Column $name does not exist in table $this->_table");
        }
        if ($name == 'id' and (!is_number($value) or $value <= 0)) {
            throw new coding_exception("Must set the id column to an integer greater than zero.  Value given: $value");
        }
        if (!property_exists($this->_record, $name) or $this->_record->$name !== $value) {
            $this->_record->$name = $value;
            $this->_change->$name = $value;
        }
    }

    /**
     * Get a record value
     *
     * @param string $name
     * @throws coding_exception
     * @return mixed
     */
    public function __get($name) {
        if (property_exists($this->_record, $name)) {
            return $this->_record->$name;
        }
        throw new coding_exception("Invalid member call: $name");
    }

    /**
     * If a record value isset
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        return isset($this->_record->$name);
    }

    /**
     * Unset a record value
     *
     * @param string $name
     * @return void
     */
    public function __unset($name) {
        unset($this->_record->$name);
        unset($this->_change->$name);
    }

    /**
     * Proxy to __isset
     *
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return $this->__isset($offset);
    }

    /**
     * Proxy to __get
     *
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->__get($offset);
    }

    /**
     * Proxy to __set
     *
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        $this->__set($offset, $value);
    }

    /**
     * Proxy to __unset
     *
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     */
    public function offsetUnset($offset) {
        $this->__unset($offset);
    }

    /**
     * Return an iterator to iterate over the record data
     *
     * Required by the IteratorAggregate implementation
     *
     * @return ArrayIterator|Traversable
     */
    public function getIterator() {
        return new ArrayIterator($this->_record);
    }

    /**
     * Return record count
     *
     * Required by the Countable implementation
     *
     * @return int
     */
    public function count() {
        return count(get_object_vars($this->_record));
    }

    /**
     * Set data to the record
     *
     * Data names that do not match table
     * columns will be ignored.
     *
     * @param mixed $data An array or object of data
     * @return mr_db_record
     */
    public function set($data) {
        foreach ($data as $name => $value) {
            if ($this->trustcolumns or $this->_table->column_exists($name)) {
                $this->__set($name, $value);
            }
        }
        return $this;
    }

    /**
     * Get record's table
     *
     * @return mr_db_table
     */
    public function get_table() {
        return $this->_table;
    }

    /**
     * Get record column names that are currently set
     *
     * @return array
     */
    public function get_columns() {
        $columns = array_keys(get_object_vars($this->_record));
        return array_combine($columns, $columns);
    }

    /**
     * Does the record need updating?
     *
     * @return boolean
     */
    public function is_update() {
        if (!$this->_delete and $this->is_changed()) {
            return isset($this->_record->id);
        }
        return false;
    }

    /**
     * Does the record need to be inserted?
     *
     * @return boolean
     */
    public function is_insert() {
        if (!$this->_delete and $this->is_changed()) {
            return !isset($this->_record->id);
        }
        return false;
    }

    /**
     * Does the record need to be deleted?
     *
     * @return boolean
     */
    public function is_delete() {
        return ($this->_delete and isset($this->_record->id));
    }

    /**
     * Does the record have changes to be saved?
     *
     * Returns true when the record needs to be
     * deleted or has changes that need to be saved.
     *
     * @return boolean
     */
    public function is_changed() {
        if ($this->_delete) {
            return true;
        }
        return (boolean) count(get_object_vars($this->_change));
    }

    /**
     * Save the record.
     *
     * The record can either be deleted, updated
     * or inserted based on record ID and current
     * state.  Only performs these actions if actually
     * necessary.
     *
     * @param boolean $bulk Bulk flag which gets passed to inserts and updates
     * @return mr_db_record
     * @throws coding_exception
     */
    public function save($bulk = false) {
        // Check for delete
        if ($this->is_delete()) {
            // Delete the record and reset _record so we don't try to delete again
            $this->_table->delete($this->_record->id);
            $this->_record = new stdClass;

        // Check for update
        } else if ($this->is_update()) {
            $this->_change->id = $this->_record->id;
            $this->_table->update_record($this->_change, $bulk);

        // Check for insert
        } else if ($this->is_insert()) {
            $this->_record->id = $this->_table->insert_record($this->_record, true, $bulk);
        }
        // Reset change state
        $this->_change = new stdClass;

        return $this;
    }

    /**
     * Flag the record for deletion, but don't actually delete it yet
     *
     * This is mostly used in conjunction with mr_db_queue
     *
     * @return mr_db_record
     */
    public function queue_delete() {
        $this->_delete = true;
        return $this;
    }

    /**
     * Delete the record.
     *
     * @return mr_db_record
     * @throws coding_exception
     */
    public function delete() {
        $this->queue_delete();
        $this->save();

        return $this;
    }
}