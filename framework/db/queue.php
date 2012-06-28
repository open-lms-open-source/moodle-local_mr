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
 * @see mr_db_record
 */
require_once($CFG->dirroot.'/local/mr/framework/db/record.php');

/**
 * @see mr_db_table
 */
require_once($CFG->dirroot.'/local/mr/framework/db/table.php');

/**
 * MR DB Queue
 *
 * Processes mr_db_record's
 * as efficiently as possible
 *
 * @author Mark Nielsen
 * @package mr
 * @see mr_db_record
 * @example controller/db.php See this class in action
 */
class mr_db_queue {
    /**
     * Record insert queue
     *
     * @var array
     */
    protected $inserts = array();

    /**
     * Record delete queue
     *
     * @var string
     */
    protected $deletes = array();

    /**
     * Stores the number of inserts and updates through lifetime of queue
     *
     * @var array
     */
    protected $counts = array('inserts' => 0, 'updates' => 0, 'deletes' => 0);

    /**
     * Queue size
     *
     * @var int
     */
    protected $size = 500;

    /**
     * Cache meta column data for tables
     *
     * @var array
     */
    static protected $metacolumns = array();

    /**
     * Construct
     *
     * @param int $size Set queue size before auto flushes
     */
    public function __construct($size = 500) {
        $this->size = $size;
    }

    /**
     * Destruct - flush it!
     *
     * @return void
     */
    public function __destruct() {
        global $DB;

        if ($DB instanceof moodle_database) {
            $this->flush();
        }
    }

    /**
     * Add record(s)
     *
     * @param mr_db_record|mr_db_record[] $records Can be a single record or an array of records
     *                       Records must be of type mr_db_record
     * @throws coding_exception
     * @return mr_db_queue
     */
    public function add($records) {
        if (!is_array($records)) {
            $records = array($records);
        }

        foreach ($records as $record) {
            if (!$record instanceof mr_db_record) {
                throw new coding_exception('Invalid object passed');

            } else if ($record->is_update()) {
                //update counter
                $this->counts['updates']++;

                // Its an update, just save it
                $record->save(true);

            } else if ($record->is_insert()) {
                //update counter
                $this->counts['inserts']++;

                // Its an insert, add to queue for bulk insert
                $table = $record->get_table()->get_name();

                if (!isset($this->inserts[$table])) {
                    $this->inserts[$table] = array('records' => array(), 'columns' => array());
                }

                $this->inserts[$table]['records'][] = $record;
                $this->inserts[$table]['columns']  += array_merge($this->inserts[$table]['columns'], $record->get_columns());

                if (count($this->inserts[$table]['records']) >= $this->size) {
                    $this->_flush_inserts($table);
                }
            } else if ($record->is_delete()) {
                //update counter
                $this->counts['deletes']++;

                // Its a delete, add to queue for bulk delete
                $table = $record->get_table()->get_name();

                if (!isset($this->deletes[$table])) {
                    $this->deletes[$table] = array();
                }
                $this->deletes[$table][] = $record->id;

                if (count($this->deletes[$table]) >= $this->size) {
                    $this->_flush_deletes($table);
                }
            }
        }
        return $this;
    }

    /**
     * Flush the queue
     *
     * @param string $table Pass a table name to flush for a specific table, none for all tables
     * @return mr_db_queue
     */
    public function flush($table = NULL) {
        if (is_null($table)) {
            foreach (array_keys($this->inserts) as $table) {
                $this->_flush_inserts($table);
            }
            foreach (array_keys($this->deletes) as $table) {
                $this->_flush_deletes($table);
            }
        } else {
            $this->_flush_inserts($table);
            $this->_flush_deletes($table);
        }
        return $this;
    }

    /**
     * Returns the count of inserts, updates, and deletes for the queue
     * either independently if $type is specified or all together if not
     *
     * @param string $type - the count type to return count for
     * @return mixed - count of specified type or array of counts
     */
    public function get_counts($type = NULL) {
        if (!is_null($type)) {
            return $this->counts[$type];
        }

        return $this->counts;
    }

    /**
     * Flushes inserts
     *
     * @param string $table The table to flush
     * @throws coding_exception
     * @return void
     */
    protected function _flush_inserts($table) {
        global $CFG, $DB;

        if (empty($this->inserts[$table])) {
            return;
        }

        $columns = $this->inserts[$table]['columns'];
        $records = $this->inserts[$table]['records'];

        // We clear the queue now so if there is an error, we don't try to
        // clear again on the __destruct()
        unset($this->inserts[$table]);

        $mrtable     = new mr_db_table($table);
        $metacolumns = $mrtable->get_metacolumns();

        if (!empty($columns)) {
            $values = array();  // Holds our CSVs
            $params = array();
            $filler = array_fill(0, count($columns), '?');
            $filler = implode(',', $filler);
            foreach ($records as $record) {
                // Get the record values in order of our columns
                foreach ($columns as $column) {
                    if (!array_key_exists($column, $metacolumns)) {
                        throw new coding_exception("Using an non-existant column ($column) for table ($table)");

                    } else if (isset($record->$column)) {
                        $params[] = $this->normalise_value($mrtable, $metacolumns[$column], $record->$column);

                    } else {
                        // Not set, use field's default - lookup in meta data
                        $params[] = $mrtable->get_column_default($metacolumns[$column]);
                    }
                }
                $values[] = $filler;
            }
            if (!empty($values)) {
                $columns = implode(',', $columns);
                $values  = implode('),(', $values);

                $DB->execute("INSERT INTO {$CFG->prefix}$table ($columns) VALUES ($values)", $params);
            }
        }
    }

    /**
     * Flushes deletes
     *
     * @param string $table The table to flush
     * @throws coding_exception
     * @return void
     */
    protected function _flush_deletes($table) {
        global $DB;

        if (!empty($this->deletes[$table])) {
            $deletes = $this->deletes[$table];
            unset($this->deletes[$table]);  // Clear it before we send to DB

            if (!$DB->delete_records_select($table, 'id IN('.implode(',', $deletes).')')) {
                throw new coding_exception('Failed to perform bulk delete');
            }
        }
    }

    /**
     * Mostly copied from mysqli_native_moodle_database.  Main change
     * is that it defaults the numeric value to the table's default
     * value instead of zero
     *
     * @throws dml_write_exception
     * @param mr_db_table $table
     * @param $column
     * @param $value
     * @return int|null|string
     */
    protected function normalise_value(mr_db_table $table, $column, $value) {
        if (is_bool($value)) { // Always, convert boolean to int
            $value = (int)$value;

        } else if ($value === '') {
            if ($column->meta_type == 'I' or $column->meta_type == 'F' or $column->meta_type == 'N') {
                $value = $table->get_column_default($column); // prevent '' problems in numeric fields
            }
        // Any float value being stored in varchar or text field is converted to string to avoid
        // any implicit conversion by MySQL
        } else if (is_float($value) and ($column->meta_type == 'C' or $column->meta_type == 'X')) {
            $value = "$value";
        }
        // workaround for problem with wrong enums in mysql - TODO: Out in Moodle 2.1
        if (!empty($column->enums)) {
            if (is_null($value) and !$column->not_null) {
                // ok - nulls allowed
            } else {
                if (!in_array((string)$value, $column->enums)) {
                    throw new dml_write_exception('Enum value '.s($value).' not allowed in field '.$column->name.'.');
                }
            }
        }
        return $value;
    }
}