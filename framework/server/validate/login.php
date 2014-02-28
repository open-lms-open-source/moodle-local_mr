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
 * MR Server Validate Login
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_server_validate_login extends Zend_Validate_Abstract {
    /**
     * Error constants
     */
    const LOGIN_FAIL = 'loginFail';
    const LOGIN_MISSING = 'loginMissing';
    const LOGIN_COOKIE = 'loginCookie';

    /**
     * Error message templates
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::LOGIN_FAIL => "Wrong username or password",
        self::LOGIN_MISSING => "Wrong username or password",
        self::LOGIN_COOKIE => 'Moodle cookies should not be enabled, this must be fixed by a programmer',
    );

    /**
     * Username parameter name
     *
     * @var string
     */
    protected $_paramusername;

    /**
     * Password parameter name
     *
     * @var string
     */
    protected $_parampassword;

    /**
     * Constructor
     *
     * @param string $paramusername Set parameter name for username
     * @param string $parampassword Set parameter name for password
     */
    public function __construct($paramusername = 'wsusername', $parampassword = 'wspassword') {
        $this->_paramusername = $paramusername;
        $this->_parampassword = $parampassword;
    }

    /**
     * See if the request contains a proper username/password for login
     *
     * @param  Zend_Controller_Request_Http $request The request to check
     * @return boolean
     */
    public function isValid($request) {
        // No cookies !
        if (!PHPUNIT_TEST) {
            if (!defined('NO_MOODLE_COOKIES') or !NO_MOODLE_COOKIES) {
                $this->_error(self::LOGIN_COOKIE);
                return false;
            }
        }

        $wsusername = $request->getParam($this->_paramusername, '');
        $wsusername = clean_param($wsusername, PARAM_RAW);

        $wspassword = $request->getParam($this->_parampassword, '');
        $wspassword = clean_param($wspassword, PARAM_RAW);

        // Are they empty ?
        if (empty($wsusername) or empty($wspassword)) {
            $this->_error(self::LOGIN_MISSING);
            return false;
        }

        // Can we login ?
        if (!$user = authenticate_user_login($wsusername, $wspassword)) {
            $this->_error(self::LOGIN_FAIL);
            return false;
        }

        // Set the user to the session
        enrol_check_plugins($user);
        \core\session\manager::set_user($user);

        return true;
    }
}
