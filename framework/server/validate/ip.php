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
 * MR Server Validate IP
 *
 * @author Mark Nielsen
 * @package mr
 * @deprecated Use core built in web service API instead
 */
class mr_server_validate_ip extends Zend_Validate_Abstract {
    /**
     * Error constants
     */
    const NOT_FOUND = 'notFound';
    const NOT_VALID = 'notValid';

    /**
     * Error message templates
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_FOUND => "IP address validation required and unable to retrieve remote IP address. Received: %value%",
        self::NOT_VALID => "Remote IP address of %value% failed to validate.  If remote IP address is correct, then validate IP Addresses setting.",
    );

    /**
     * IP Address schema to validate against
     *
     * @var string
     */
    protected $_ipAddresses;

    /**
     * Constructor
     *
     * @param string $ipaddresses IP Address schema to validate against
     * @deprecated Use core built in web service API instead
     */
    public function __construct($ipaddresses) {
        $this->_ipAddresses = $ipaddresses;
    }

    /**
     * See if the request has the proper remote address
     *
     * @param  Zend_Controller_Request_Http $request The request to check
     * @return boolean
     * @deprecated Use core built in web service API instead
     */
    public function isValid($request) {
        if (!empty($this->_ipAddresses)) {
            $remoteaddr = getremoteaddr();

            // Check for localhost IPv6
            if (empty($remoteaddr) and $request->getServer('REMOTE_ADDR') == '::1') {
                $remoteaddr = '127.0.0.1';
            }

            // Can get get the remote address ?
            if (empty($remoteaddr)) {
                $this->_setValue($request->getServer('REMOTE_ADDR'));
                $this->_error(self::NOT_FOUND);
                return false;
            }

            // Address valid ?
            if (!address_in_subnet($remoteaddr, $this->_ipAddresses)) {
                $this->_setValue($remoteaddr);
                $this->_error(self::NOT_VALID);
                return false;
            }
        }

        return true;
    }
}