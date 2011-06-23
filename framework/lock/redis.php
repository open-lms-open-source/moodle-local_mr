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
 */
class mr_lock_redis extends mr_lock_abstract {

    public function get() {
        global $UNITTEST;

        try {
            $redis = mr_bootstrap::redis();

            // Attempt to obtain lock
            if ($redis->setnx($this->uniquekey, $this->get_lock_value())) {
                $this->set_lockaquired(true);

            // If we have an expire time, then see if it has expired or is invalid
            } else if (!empty($this->timetolive) and $value = $redis->get($this->uniquekey)) {
                if ($this->parse_timetolive($value) < time()) {
                    $replaced = $redis->getset($this->uniquekey, $this->get_lock_value());

                    // If this is not equal, it means another process beat us to the getset
                    if ($replaced == $value) {
                        $this->set_lockaquired(true);
                    }
                }
            }
            $redis->close();
        } catch (RedisException $e) {
            debugging("RedisException caught with message: {$e->getMessage()}", DEBUG_DEVELOPER);
        } catch (Exception $e) {
            if (empty($UNITTEST->running) and isset($_SERVER['HTTP_HOST'])) {
                if (empty($_SERVER['HTTP_X_FORWARDED_FOR']) or !preg_match("/^10\./", $_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    mtrace('Running the cron via the browser has been temporarily disabled.  It will be re-enabled in the near future. Please send an email to support@moodlerooms.com with this message if you are having an issue.');
                    die;
                }
            }
            debugging("Redis lock acquire granted, Redis locking disabled because {$e->getMessage()}.", DEBUG_DEVELOPER);
            $this->set_lockaquired(true);
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
            debugging("RedisException caught with message: {$e->getMessage()}", DEBUG_DEVELOPER);
        } catch (Exception $e) {
            debugging("Exception caught with message: {$e->getMessage()}", DEBUG_DEVELOPER);
        }
        $this->set_lockaquired(false);

        return ($result == 1);
    }
}