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
 * @deprecated Use core's built in locking API instead
 */
abstract class mr_lock_abstract {
    /**
     * The unique key used to identify the lock
     *
     * @var string
     */
    protected $uniquekey;

    /**
     * The timestamp of when the lock expires or zero for no expiration
     *
     * @var int
     */
    protected $timetolive = 0;

    /**
     * Flag for if we have acquired the lock or not
     *
     * @var bool
     */
    protected $lockacquired = false;

    /**
     * Lock setup
     *
     * @param string $uniquekey This key is used to generate the key for the lock.
     *                          Example values: mod_quiz_cron, admin_cron, etc.
     * @param int $timetolive The number of seconds until the lock expires completely.  Default is 8 hours.
     * @throws coding_exception
     */
    public function __construct($uniquekey, $timetolive = NULL) {
        global $CFG;

        $uniquekey  = clean_param($uniquekey, PARAM_ALPHAEXT);
        $uniquekey  = clean_param($uniquekey, PARAM_CLEANFILE);
        $timetolive = clean_param($timetolive, PARAM_INT);

        if (empty($uniquekey)) {
            throw new coding_exception('Passed unique key is empty (after cleaning)');
        }
        if (!empty($CFG->local_mr_lock_default_timetolive)) {
            if (empty($timetolive)) {
                $timetolive = $CFG->local_mr_lock_default_timetolive;
            }
            $this->timetolive = (time() + $timetolive + 1);
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
     * Set if the lock has been acquired or not
     *
     * @param boolean $lockacquired
     * @return mr_lock_abstract
     */
    protected function set_lockacquired($lockacquired) {
        $this->lockacquired = $lockacquired;
        return $this;
    }

    /**
     * Determine we currently have a lock or not
     *
     * @return boolean
     */
    public function has_lock() {
        return $this->lockacquired;
    }

    /**
     * Get the value that should be used for the lock
     *
     * @return string
     */
    public function get_lock_value() {
        return http_build_query(array(
            'timetolive' => $this->timetolive,
            'hostname' => gethostname(),
            'processid' => getmypid(),
        ), null, '&');
    }

    /**
     * Parse the lock value and return the time to live timestamp
     *
     * @param mixed $lockvalue
     * @return int
     */
    public function parse_timetolive($lockvalue) {
        // Legacy check
        if (is_number($lockvalue)) {
            return (int) $lockvalue;
        }
        $params = array();
        parse_str($lockvalue, $params);

        if (array_key_exists('timetolive', $params)) {
            return (int) $params['timetolive'];
        }
        return 0; // AKA Invalid/No TTL
    }

    /**
     * Try to acquire the lock
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