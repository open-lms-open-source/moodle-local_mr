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
 * MR File Lock
 *
 * This class is used to get an exclusive file
 * lock on a specific file.  Classic use
 * case for using this is to prevent two
 * actions from happening at the same time,
 * like running a cron ontop of itself.
 *
 * Example usage:
 * <code>
 * <?php
 *     $lock = new mr_file_lock('block_uib_cron');
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
class mr_file_lock {

    /**
     * File handle
     *
     * @var resource
     */
    protected $handle = false;

    /**
     * File that gets locked
     *
     * @var string
     */
    protected $file;

    /**
     * Constructor
     *
     * @param string $uniquekey This key is used to generate the file name for the file lock.
     *                          Example values: block_uib_cron, admin_cron, etc.
     */
    public function __construct($uniquekey) {
        global $CFG;

        $uniquekey = clean_param($uniquekey, PARAM_ALPHAEXT);
        $uniquekey = clean_param($uniquekey, PARAM_CLEANFILE);

        if (empty($uniquekey)) {
            throw new coding_exception('Passed unique key is empty (after cleaning)');
        }
        $this->file = "$CFG->dataroot/{$uniquekey}_lock.txt";
    }

    /**
     * Release file lock on deconstruct
     *
     * This is done to prevent script execution
     * errors from releasing the lock.
     *
     * @return void
     */
    public function __destruct() {
        $this->release();
    }

    /**
     * Get a file lock
     *
     * Only one lock can ever be active.
     * The current process ID is written
     * to the file to help with killing
     * hanging processes.
     *
     * @return boolean
     */
    public function get() {
        $this->handle = fopen($this->file, 'w');

        if (is_resource($this->handle)) {
            if (flock($this->handle, LOCK_EX | LOCK_NB)) {
                ftruncate($this->handle, 0);
                fwrite($this->handle, getmypid());

                return true;
            }
        }
        // Failed to get lock, release any resources
        $this->release();

        return false;
    }

    /**
     * Release a file lock
     *
     * Safe to call anytime.
     *
     * @return void
     */
    public function release() {
        if (is_resource($this->handle)) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
            $this->handle = false;
        }
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }
}