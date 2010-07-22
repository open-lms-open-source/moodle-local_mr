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
        $this->flush();
    }

    /**
     * Add record(s)
     *
     * @param mixed $records Can be a single record or an array of records
     *                       Records must be of type mr_db_record
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
                // Its an update, just save it
                $record->save(true);

            } else if ($record->is_insert()) {
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
     * Flushes inserts
     *
     * @param string $table The table to flush
     * @return void
     */
    protected function _flush_inserts($table) {
        global $CFG, $DB;

        if (empty($this->inserts[$table])) {
            return;
        }

        $columns     = $this->inserts[$table]['columns'];
        $mrtable     = new mr_db_table($table);
        $metacolumns = $mrtable->get_columns();

        if (!empty($columns)) {
            $values = array();  // Holds our CSVs
            $params = array();
            $filler = array_fill(0, count($columns), '?');
            $filler = implode(',', $filler);
            foreach ($this->inserts[$table]['records'] as $record) {
                // Get the record values in order of our columns
                foreach ($columns as $column) {
                    if (!array_key_exists($column, $metacolumns)) {
                        throw new coding_exception("Using an non-existant column ($column) for table ($table)");

                    } else if (isset($record->$column)) {
                        $params[] = $record->$column;

                    } else {
                        // Not set, use field's default - lookup in meta data
                        $meta = $metacolumns[$column];

                        if (!empty($meta->has_default)) {
                            $params[] = $meta->default_value;
                        } else if (empty($meta->not_null)) {
                            $params[] = NULL;
                        } else {
                            throw new coding_exception('Default not handled');
                        }
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
        // Clear the queue
        unset($this->inserts[$table]);
    }

    /**
     * Flushes deletes
     *
     * @param string $table The table to flush
     * @return void
     */
    protected function _flush_deletes($table) {
        global $DB;

        if (!empty($this->deletes[$table])) {
            if (!$DB->delete_records_select($table, 'id IN('.implode(',', $this->deletes[$table]).')')) {
                throw new coding_exception('Failed to perform bulk delete');
            }
            // Clear the queue
            unset($this->deletes[$table]);
        }
    }
}