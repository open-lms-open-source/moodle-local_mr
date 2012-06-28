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
 * @see mr_repository_recordset
 */
require_once($CFG->dirroot.'/local/mr/framework/repository/recordset.php');

/**
 * @see mr_model_record_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/model/record/abstract.php');

/**
 * MR Repository Abstract
 *
 * Most basic repository mapper that maps data
 * from the database to an application's models
 * and also from the models to the database.
 *
 * A repository mapper DOES NOT need to extend
 * this class as this class might be too simplistic
 * for your needs.
 *
 * In the end, all database interactions that take
 * place between your models and the database should be
 * stored in this class.
 *
 * @author Mark Nielsen
 * @package mr
 * @example controller/model.php See this class in action
 * @see http://martinfowler.com/eaaCatalog/dataMapper.html
 * @throws coding_exception
 */
abstract class mr_repository_abstract {
    /**
     * The table that this repository mapper
     * is primarily associated with
     *
     * @var mr_db_table
     */
    protected $table;

    /**
     * Get a new instance of the model that the repository uses
     *
     * @abstract
     * @return mr_model_record_abstract
     */
    abstract public function get_new_model();

    /**
     * The name of the table that the repository uses
     *
     * @abstract
     * @return string
     */
    abstract public function get_table_name();

    /**
     * Set the table that this repository mapper
     * uses.  Generally you do not want to use this.
     *
     * @param mr_db_table|string $table
     * @return mr_repository_abstract
     * @throws coding_exception
     */
    public function set_table($table) {
        if (is_string($table)) {
            $this->table = new mr_db_table($table);
        } else if ($table instanceof mr_db_table) {
            $this->table = $table;
        } else {
            throw new coding_exception('Invalid: table must be a string or an instance of mr_db_table');
        }
        return $this;
    }

    /**
     * Get the table that this repository mapper uses
     *
     * Warning: this method WILL set the table property
     * if it's not set already.
     *
     * @return mr_db_table
     */
    public function get_table() {
        if (!$this->table instanceof mr_db_table) {
            $this->set_table($this->get_table_name());
        }
        return $this->table;
    }

    /**
     * Convert the model to a record
     *
     * If the default implementation doesn't work,
     * it's 100% acceptable to override this method.
     *
     * @param mr_model_record_abstract $model
     * @return stdClass;
     */
    public function model_to_record(mr_model_record_abstract $model) {
        $record = new stdClass;
        foreach ($this->get_table()->get_metacolumns() as $name => $meta) {
            if ($name == 'id') {
                $id = $model->get_id();
                if (!empty($id)) {
                    $record->id = $id;
                }
            } else {
                $method = "get_$name";
                if (method_exists($model, $method)) {
                    $record->$name = $model->$method();
                }
            }
        }
        return $record;
    }

    /**
     * Convert a record to a model
     *
     * If the default implementation doesn't work,
     * it's 100% acceptable to override this method.
     *
     * @param stdClass $record
     * @return mr_model_record_abstract
     */
    public function record_to_model(stdClass $record) {
        $model = $this->get_new_model();
        $model->set_options($record);
        return $model;
    }

    /**
     * Get a model based on conditions
     *
     * @param array $conditions Where conditions for get_record()
     * @param int $strictness IGNORE_MISSING means compatible mode, false returned if record not found, debug message if more found;
     *                        IGNORE_MULTIPLE means return first, ignore multiple records found(not recommended);
     *                        MUST_EXIST means throw exception if no record or multiple records found
     * @return boolean|mr_model_record_abstract
     */
    public function get(array $conditions, $strictness = MUST_EXIST) {
        global $DB;

        if (!$record = $DB->get_record($this->get_table()->get_name(), $conditions, '*', $strictness)) {
            return false;
        }
        return $this->record_to_model($record);
    }

    /**
     * Get a mr_repository_recordset based on conditions, etc
     *
     * Use this to iterate over your models.
     *
     * @param null $conditions Where conditions for get_recordset()
     * @param string $sort Sorting
     * @param int $limitfrom Limit from
     * @param int $limitnum Limit number
     * @return mr_repository_recordset
     */
    public function get_recordset($conditions = null, $sort = '', $limitfrom = 0, $limitnum = 0) {
        global $DB;

        $rs = $DB->get_recordset($this->get_table()->get_name(), $conditions, $sort, '*', $limitfrom, $limitnum);

        return new mr_repository_recordset($this, $rs);
    }

    /**
     * Save a model
     *
     * Common reason for needing to override
     * this method is perhaps to update a time
     * modified timestamp and then calling
     * this parent method.
     *
     * @param mr_model_record_abstract $model
     * @param bool $bulk
     * @return mr_repository_abstract
     */
    public function save(mr_model_record_abstract $model, $bulk = false) {
        global $DB;

        $record = $this->model_to_record($model);

        if (isset($record->id)) {
            $DB->update_record($this->get_table()->get_name(), $record, $bulk);
        } else {
            $model->set_id(
                $DB->insert_record($this->get_table()->get_name(), $record, true, $bulk)
            );
        }
        return $this;
    }

    /**
     * Delete a model
     *
     * The model's ID property is required, but after
     * the delete, the ID property will be set to null.
     *
     * @throws coding_exception
     * @param mr_model_record_abstract $model
     * @return mr_repository_abstract
     */
    public function delete(mr_model_record_abstract $model) {
        global $DB;

        $id = $model->get_id();
        if (empty($id)) {
            throw new coding_exception('The passed model cannot be deleted because it doesn\'t have an ID');
        }
        $DB->delete_records($this->get_table()->get_name(), array('id' => $id));

        // Clear out ID
        $model->set_id(null);

        return $this;
    }
}