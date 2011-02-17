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
 * MR Lock Abstract
 *
 * This is the abstract class for classes
 * that actually implement the locking
 * mechanisms.
 *
 * @package mr
 * @author Mark Nielsen
 */
abstract class mr_lock_abstract {
    /**
     * The unique key used to identify the lock
     *
     * @var string
     */
    protected $uniquekey;

    /**
     * Lock's time to live
     *
     * @var int
     */
    protected $timetolive;

    /**
     * Lock setup
     *
     * @param string $uniquekey This key is used to generate the key for the lock.
      *                         Example values: mod_quiz_cron, admin_cron, etc.
     * @param int $timetolive The number of seconds until the lock expires completely.  Default is 8 hours.
     */
    public function __construct($uniquekey, $timetolive = NULL) {
        global $CFG;

        $uniquekey  = clean_param($uniquekey, PARAM_ALPHAEXT);
        $uniquekey  = clean_param($uniquekey, PARAM_CLEANFILE);
        $timetolive = clean_param($timetolive, PARAM_INT);

        if (empty($uniquekey)) {
            throw new coding_exception('Passed unique key is empty (after cleaning)');
        }
        if (empty($timetolive)) {
            $this->timetolive = (HOURSECS * 8);
        } else {
            $this->timetolive = $timetolive;
        }
        if (!empty($CFG->MR_SHORT_NAME)) {
            $this->uniquekey = $CFG->MR_SHORT_NAME.'_'.$uniquekey;
        } else {
            $this->uniquekey = $CFG->dbname.'_'.$uniquekey;
        }
    }

    /**
     * Release the lock on destruct.
     *
     * May need to override to check if dependent resources
     * are still available and have not already been destroyed.
     *
     * @return void
     */
    public function __destruct() {
        global $CFG, $DB;

        if (!empty($CFG) and $DB instanceof moodle_database) {
            $this->release();
        }
    }

    /**
     * Try to aquire the lock
     *
     * @return boolean
     */
    abstract public function get();

    /**
     * Release the lock
     *
     * @return boolean
     */
    abstract public function release();
}