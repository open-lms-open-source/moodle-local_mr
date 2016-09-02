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
 * @see mr_server_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/server/abstract.php');

/**
 * MR Server Rest
 *
 * @author Mark Nielsen
 * @package mr
 * @example webservices.php Example server usage
 * @example controller/server.php Example client
 * @deprecated Use core built in web service API instead
 */
class mr_server_rest extends mr_server_abstract {
    /**
     * Use a Zend_Rest_Server
     *
     * @return object|\Zend_Rest_Server
     * @deprecated Use core built in web service API instead
     */
    public function new_server() {
        require_once('Zend/Rest/Server.php');
        return new Zend_Rest_Server();
    }
}