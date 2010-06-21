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
     * Flag for if startup is needed or not
     *
     * @var boolean
     */
    protected static $init = false;

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
     * This method is NOT automatically called.  Recommended to call
     * this when you don't want the Moodlerooms Framework to
     * conflict with other code, EG: when using the framework on
     * the cron.
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
}