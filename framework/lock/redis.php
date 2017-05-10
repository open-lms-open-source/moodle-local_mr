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
 * @see mr_bootstrap
 */
require_once($CFG->dirroot.'/local/mr/framework/bootstrap.php');

/**
 * @see mr_lock_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/lock/abstract.php');

/**
 * MR Lock Redis
 *
 * This lock uses Redis to provide a distributed
 * locking mechanism.
 *
 * @package mr
 * @author Mark Nielsen
 * @deprecated Use core's built in locking API instead
 */
class mr_lock_redis extends mr_lock_abstract {

    public function get() {
        if ($this->has_lock()) {
            return true; // Don't attempt to re-acquire
        }
        try {
            $redis = mr_bootstrap::redis();

            // Attempt to obtain lock
            if ($redis->setnx($this->uniquekey, $this->get_lock_value())) {
                $this->set_lockacquired(true);

            // If we have an expire time, then see if it has expired or is invalid
            } else if (!empty($this->timetolive) and $value = $redis->get($this->uniquekey)) {
                if ($this->parse_timetolive($value) < time()) {
                    $replaced = $redis->getset($this->uniquekey, $this->get_lock_value());

                    // If this is not equal, it means another process beat us to the getset
                    if ($replaced == $value) {
                        $this->set_lockacquired(true);
                    }
                }
            }
            $redis->close();
        } catch (RedisException $e) {
            debugging("RedisException caught on host {$this->get_hostname()} with message: {$e->getMessage()}");
        } catch (Exception $e) {
            debugging("Redis lock denied on host {$this->get_hostname()}, Redis locking disabled because {$e->getMessage()}.");

            if (!PHPUNIT_TEST and isset($_SERVER['HTTP_HOST'])) {
                if (empty($_SERVER['HTTP_X_FORWARDED_FOR']) or !preg_match("/^10\./", $_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    mtrace('Cron is unable to begin running at this time. Please try again in a few minutes. If this message persists, please contact Support through the support portal.');
                    die;
                }
            }
        }
        return $this->has_lock();
    }

    public function release() {

        $result = 1;

        try {
            if ($this->has_lock()) {
                $redis = mr_bootstrap::redis();

                if (empty($this->timetolive)) {
                    $result = $redis->delete($this->uniquekey);
                } else {
                    $timetolive = $this->parse_timetolive(
                        $redis->get($this->uniquekey)
                    );

                    // We check to value to make sure the key hasn't expired and been re-acquired by another process
                    if ($timetolive == $this->timetolive and $timetolive > time()) {
                        $result = $redis->delete($this->uniquekey);
                    }
                }
                $redis->close();
            }
        } catch (RedisException $e) {
            debugging("RedisException caught on host {$this->get_hostname()} with message: {$e->getMessage()}");
        } catch (Exception $e) {
            debugging("Exception caught on host {$this->get_hostname()} with message: {$e->getMessage()}");
        }
        $this->set_lockacquired(false);

        return ($result == 1);
    }

    /**
     * Get the server host name
     *
     * @return string
     */
    protected function get_hostname() {
        if (($hostname = gethostname()) === false) {
            $hostname = 'UNKOWN';
        }
        return $hostname;
    }
}