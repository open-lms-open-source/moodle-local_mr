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
 * MR Server Validate Token
 *
 * @author Mark Nielsen
 * @package mr
 * @deprecated Use core built in web service API instead
 */
class mr_server_validate_token extends Zend_Validate_Abstract {
    /**
     * Error constants
     */
    const TOKEN_EMPTY = 'tokenEmpty';
    const TOKEN_NO_MATCH = 'tokenNoMatch';
    const TOKEN_NOT_PASSED = 'tokenNotPassed';

    /**
     * Error message templates
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::TOKEN_EMPTY => "No __token__ configured, web services disabled (The configured __token__ value is empty)",
        self::TOKEN_NO_MATCH => "Passed __token__ is invalid: %value%",
        self::TOKEN_NOT_PASSED => "Passed __token__ is invalid: NOT PASSED",
    );

    /**
     * The expected token
     *
     * @var string
     */
    protected $_token;

    /**
     * Token parameter name
     *
     * @var string
     */
    protected $_paramname;

    /**
     * Constructor
     *
     * @param string $token The expected token
     * @param string $paramname Override the token parameter name
     * @deprecated Use core built in web service API instead
     */
    public function __construct($token, $paramname = 'token') {
        $this->_token     = $token;
        $this->_paramname = $paramname;

        foreach ($this->_messageTemplates as $key => $template) {
            $this->_messageTemplates[$key] = str_replace('__token__', $paramname, $template);
        }
    }

    /**
     * See if the request contains a proper token
     *
     * @param  Zend_Controller_Request_Http $request The request to check
     * @return boolean
     * @deprecated Use core built in web service API instead
     */
    public function isValid($request) {
        $value = $request->getParam($this->_paramname);

        // Did the token get passed ?
        if (is_null($value)) {
            $this->_error(self::TOKEN_NOT_PASSED);
            return false;
        }

        // Clean it and set the value
        $value = clean_param($value, PARAM_RAW);
        $this->_setValue($value);

        // Is the token empty ?
        if (empty($this->_token)) {
            $this->_error(self::TOKEN_EMPTY);
            return false;
        }

        // Do the tokens match ?
        if ($this->_token !== $value) {
            $this->_error(self::TOKEN_NO_MATCH);
            return false;
        }

        return true;
    }
}