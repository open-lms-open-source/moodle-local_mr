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
 * @see mr_helper
 */
require_once($CFG->dirroot.'/local/mr/framework/helper.php');

/**
 * MR Lock
 *
 * This class is used to get a process safe lock.
 * Basic use case for this is to prevent code
 * from running at the same time in different requests,
 * like running a cron on top of itself.
 *
 * Example usage:
 * <code>
 * <?php
 *     $lock = new mr_lock('admin_cron');
 *     if ($lock->get()) {
 *         // Do work here that requires a lock
 *
 *         // Release the lock when done
 *         $lock->release();
 *     }
 * ?>
 * </code>
 * @package mr
 * @author Mark Nielsen
 */
class mr_lock {
    /**
     * The mechanism used for acquiring the lock
     *
     * @var mr_lock_abstract
     */
    protected $backend;

    /**
     * Lock setup
     *
     * @param string $uniquekey This key is used to generate the key for the lock.
      *                         Example values: mod_quiz_cron, admin_cron, etc.
     * @param int $timetolive The number of seconds until the lock expires completely.  Default is 8 hours.
     * @param string $backend The backend to use for the locking mechanism.  Generally, don't pass this.
     */
    public function __construct($uniquekey, $timetolive = NULL, $backend = NULL) {
        global $CFG;

        if (is_null($backend)) {
            if (!empty($CFG->local_mr_lock_default_backend)) {
                $backend = $CFG->local_mr_lock_default_backend;
            } else {
                $backend = 'redis';
            }
        }
        $this->backend = mr_helper::get()->load(
            "lock/$backend",
            array($uniquekey, $timetolive)
        );
    }

    /**
     * Release the lock on shutdown.
     *
     * @return void
     */
    public function shutdown() {
        $this->backend->__destruct();
    }

    /**
     * Try to acquire the lock
     *
     * @return boolean
     */
    public function get() {
        if ($this->backend->has_lock()) {
            return true; // Don't attempt to re-acquire
        }
        $result = $this->backend->get();
        if ($result) {
            core_shutdown_manager::register_function(array($this, 'shutdown'));
        }
        return $result;
    }

    /**
     * Release the lock
     *
     * @return boolean
     */
    public function release() {
        return $this->backend->release();
    }
}