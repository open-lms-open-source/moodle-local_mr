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
 * MR Server Service Abstract
 *
 * WARNING: Any public method will be accessible to the web service!
 *
 * This class is responsible for defining the available
 * services for a particular web service endpoint.  The
 * public class methods are executed by the mr_server_abstract.
 *
 * @author Mark Nielsen
 * @package mr
 * @example webservices.php Example usage with the server
 * @example lib/server/service.php Example class definition
 * @deprecated Use core built in web service API instead
 */
abstract class mr_server_service_abstract {
    /**
     * The current server model
     *
     * @var mr_server_abstract
     */
    protected $server;

    /**
     * Response handler
     *
     * @var mr_server_response_abstract
     */
    protected $response;

    /**
     * Constructor
     *
     * @param mr_server_abstract $server The current server model
     * @param object $response Response handling object
     * @deprecated Use core built in web service API instead
     */
    public function __construct($server, $response) {
        $this->server   = $server;
        $this->response = $response;

        $this->init();
    }

    /**
     * Constructor hook
     *
     * @return void
     * @deprecated Use core built in web service API instead
     */
    protected function init() {
    }
}