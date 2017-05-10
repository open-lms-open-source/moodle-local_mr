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
 * @see mr_autoload
 */
require_once($CFG->dirroot.'/local/mr/framework/autoload.php');

/**
 * MR Bootstrap
 *
 * As of now, mr_bootstrap is very simple and
 * does all the environment setup for using
 * MR Framework.  In the future, mr_bootstrap may include
 * more methods/information regarding state
 * and environment.
 *
 * @package mr
 * @author Mark Nielsen
 * @example view.php See how to use this file
 */
class mr_bootstrap {

    /**
     * Flag for if startup is needed or not.
     *
     * @var boolean
     */
    protected static $init = false;

    /**
     * Flag for if Zend Framework has been
     * bootstrapped or not.
     *
     * @var boolean
     */
    protected static $zend = false;

    /**
     * Run startup routine
     *
     * @return void
     */
    public static function startup() {
        if (!self::$init) {
            // Autoload all mr_* classes
            mr_autoload::register();

            // Init done!
            self::$init = true;
        }
    }

    /**
     * Run shutdown routine
     *
     * Recommended to call this when you don't want the Moodlerooms
     * Framework to conflict with other code, EG: when using the
     * framework on the cron.
     *
     * @return void
     */
    public static function shutdown() {
        if (self::$init) {
            // Stop autoloading all mr_* classes
            mr_autoload::unregister();

            // Reset!
            self::$init = false;
        }
    }

    /**
     * Bootstrap Zend Framework
     *
     * Right now, this just sets a proper include path
     * so you can require_once(...) Zend files.
     *
     * @return void
     * @deprecated Zend Framework support has been deprecated, please use alternatives
     */
    public static function zend() {
        global $CFG;

        if (!self::$zend) {
            $path = $CFG->dirroot.'/local/mr/vendor/zend';

            if (is_dir($path)) {
                $includepaths = explode(PATH_SEPARATOR, get_include_path());

                // Add our path to the front.
                array_unshift($includepaths, $path);

                set_include_path(implode(PATH_SEPARATOR, $includepaths));
            }

            // Init done!
            self::$zend = true;
        }
    }

    /**
     * Bootstrap Redis
     *
     * This will create a Redis instance and
     * connect it to the Redis server.  The
     * code calling this is responsible for calling
     * the close() method to close the connection.
     *
     * @return Redis
     * @see https://github.com/owlient/phpredis
     * @throws RedisException On connection errors
     * @throws Exception On configuration/setup errors
     * @deprecated This should no longer be used.
     */
    public static function redis() {
        global $CFG;

        if (!class_exists('Redis')) {
            throw new Exception('Redis class not found, Redis PHP Extension is probably not installed');
        }
        if (empty($CFG->local_mr_redis_server)) {
            throw new Exception('Redis connection string is not configured in $CFG->local_mr_redis_server');
        }
        $redis = new Redis();
        $redis->connect($CFG->local_mr_redis_server);

        return $redis;
    }
}