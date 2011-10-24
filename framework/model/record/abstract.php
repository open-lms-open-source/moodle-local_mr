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
 * @see mr_model_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/model/abstract.php');

/**
 * MR Model Record Abstract
 *
 * A model that associates itself with a database table record.
 *
 * Note: a model doesn't have to directly map to a single record
 * and a record doesn't have to map to a single model.  For example,
 * a model may contain data from multiple tables.
 *
 * @throws coding_exception
 * @author Mark Nielsen
 * @package mr
 * @example controller/model.php See this class in action
 */
abstract class mr_model_record_abstract extends mr_model_abstract {
    /**
     * @var int|null
     */
    protected $id = null;

    /**
     * Get the ID
     *
     * Might be null, meaning it's not set.
     *
     * @return int|null
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Set the ID to null or to a positive whole number
     *
     * @throws coding_exception
     * @param int|null $id
     * @return mr_model_record_abstract
     */
    public function set_id($id) {
        if (!is_number($id) and !is_null($id)) {
            throw new coding_exception('ID must be a number or NULL');
        }
        if (!is_null($id) and $id < 1) {
            throw new coding_exception('ID must be a positive, non-zero number');
        }
        $this->id = $id;
        return $this;
    }
}