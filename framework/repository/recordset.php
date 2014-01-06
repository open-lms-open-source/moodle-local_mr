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
 * MR Repository Record Set
 *
 * This is returned by repository mappers instead of
 * a regular moodle_recordset.  Instead of iterating over
 * plain objects, this will iterate over your models.
 *
 * All other moodle_recordset behavior is exactly the same.
 *
 * @author Mark Nielsen
 * @package mr
 * @example controller/model.php See this class in action
 */
class mr_repository_recordset extends moodle_recordset {
    /**
     * @var mr_repository_abstract
     */
    protected $repo;

    /**
     * @var moodle_recordset
     */
    protected $rs;

    /**
     * @param mr_repository_abstract $repo
     * @param moodle_recordset $rs
     */
    public function __construct(mr_repository_abstract $repo, moodle_recordset $rs) {
        $this->repo = $repo;
        $this->rs   = $rs;
    }

    /**
     * Our only real magic here, we are converting the record
     * to our model.
     *
     * @return mr_model_record_abstract
     */
    public function current() {
        return $this->repo->record_to_model(
            $this->rs->current()
        );
    }

    public function next() {
        $this->rs->next();
    }

    public function key() {
        return $this->rs->key();
    }

    public function valid() {
        return $this->rs->valid();
    }

    public function close() {
        $this->rs->close();
    }
}