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
 * @see mr_boostrap
 */
require_once($CFG->dirroot.'/local/mr/framework/bootstrap.php');

/**
 * Setup Zend
 */
mr_bootstrap::zend();

/**
 * @see Zend_Validate
 */
require_once 'Zend/Validate.php';

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * MR Server Validate Test
 *
 * Only use this for unit testing.
 *
 * @author Mark Nielsen
 * @package mr
 * @deprecated Use core built in web service API instead
 */
class mr_server_validate_test extends Zend_Validate_Abstract {
    /**
     * Error constants
     */
    const UNITTEST = 'unittest';

    /**
     * Error message templates
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::UNITTEST => 'This validator can only be used for unit tests',
    );

    /**
     * Make sure we are unit testing
     *
     * @param  Zend_Controller_Request_Http $request The request to check
     * @return boolean
     * @deprecated Use core built in web service API instead
     */
    public function isValid($request) {
        if (!PHPUNIT_TEST) {
            $this->_error(self::UNITTEST);
            return false;
        }
        return true;
    }
}