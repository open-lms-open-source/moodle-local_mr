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
 * @see mr_db_table
 */
require_once($CFG->libdir.'/mr/db/table.php');

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
     * Constructor
     *
     * @param mixed $table The table name or an instance of mr_db_table
     * @param mixed $default The default record data - just setting this will not trigger a save
     */
    public function __construct($table, $default = NULL) {
        global $CFG, $db;

        if ($table instanceof mr_db_table) {
            $this->_table = $table;
        } else {
            $this->_table = new mr_db_table($table);
        }
        $this->_change = new stdClass;
        $this->_record = new stdClass;

        // Apply default to _record
        if (!is_null($default)) {
            if (is_array($default)) {
                $default = (object) $default;
            }
            $this->_record = $default;

            // Remove values that are not in the table
            foreach ($this->_record as $name => $value) {
                if (!$this->_table->column_exists($name)) {
                    unset($this->_record->$name);
                }
            }
        }
    }

    /**
     * Set a value to the record.  Records changes.
     *
     * @return void
     * @throws coding_exception
     */
    public function __set($name, $value) {
        if (!$this->_table->column_exists($name)) {
            throw new coding_exception("Column $name does not exist in table $this->_table");
        }
        if (!property_exists($this->_record, $name) or $this->_record->$name != $value) {
            $this->_record->$name = $value;
            $this->_change->$name = $value;
        }
    }

    /**
     * Get a record value
     *
     * @return void
     * @throws coding_exception
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
     * @return boolean
     */
    public function __isset($name) {
        return isset($this->_record->$name);
    }

    /**
     * Unset a record value
     *
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
     * @return string
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
     * @return ArrayIterator
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
            if ($this->_table->column_exists($name)) {
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
     * <b>WARNING</b>: this method will not automatically add slashes
     * to the record data.  If you need to add slashes, then
     * either add slashes before sending the value to mr_db_record
     * or use mr_db_record::addslashes()
     *
     * Example of adding slashes to the record:
     * <code>
     * <?php
     *      // Add slashes to record before saving
     *      $record = new mr_db_record('tablename');
     *      $record->field = "It's";
     *      $record->addslashes()->save();
     * ?>
     * </code>
     * @return void
     * @throws coding_exception
     * @see mr_db_record::addslashes()
     */
    public function save() {
        // Check for delete
        if ($this->is_delete()) {
            // Delete the record and reset _record so we don't try to delete again
            $this->_table->delete($this->_record->id);
            $this->_record = new stdClass;

        // Check for update
        } else if ($this->is_update()) {
            $this->_change->id = $this->_record->id;
            if (!update_record($this->_table->get_name(), $this->_change)) {
                throw new coding_exception("Failed to update record with id = {$this->_record->id} in table $this->_table");
            }

        // Check for insert
        } else if ($this->is_insert()) {
            if (!$id = insert_record($this->_table->get_name(), $this->_record)) {
                throw new coding_exception("Failed to insert record into table $this->_table");
            }
            $this->_record->id = $id;
        }
        // Reset change state
        $this->_change = new stdClass;
    }

    /**
     * Delete the record.
     *
     * @return void
     * @throws coding_exception
     */
    public function delete() {
        $this->queue_delete();
        $this->save();
    }

    /**
     * Add slashes to mr_db_record
     *
     * @return mr_db_record
     * @see mr_db_record::save() Example usage
     */
    public function addslashes() {
        $this->_record = $this->_do_addslashes($this->_record);
        $this->_change = $this->_do_addslashes($this->_change);

        return $this;
    }

    /**
     * Addslashes to object - don't
     * add slashes to numbers or to NULL values
     *
     * @param object $object Object to add slashes to
     * @return object
     */
    protected function _do_addslashes($object) {
        $slashed = new stdClass;
        foreach ($object as $key => $value) {
            if (!is_null($value) and !is_numeric($value)) {
                $slashed->$key = addslashes($value);
            } else {
                $slashed->$key = $value;
            }
        }
        return $slashed;
    }
}