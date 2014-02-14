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
 * MR Server Abstract
 *
 * The server is responsible for security validation
 * and the routing of incoming requests to mr_server_service_abstract.
 *
 * @author Mark Nielsen
 * @package mr
 * @example webservices.php Example server usage
 * @example controller/server.php Example client
 */
abstract class mr_server_abstract {
    /**
     * The service class name
     *
     * @var string
     */
    protected $serviceclass;

    /**
     * The response class name
     *
     * @var string
     */
    protected $responseclass;

    /**
     * Server instance
     *
     * @var Zend_Server_Interface
     */
    protected $server;

    /**
     * Response class
     *
     * @var mr_server_response_abstract
     */
    protected $response;

    /**
     * Server request
     *
     * @var Zend_Controller_Request_Http
     */
    protected $request;

    /**
     * Validator chain to validate the incoming request
     *
     * @var Zend_Validate
     */
    protected $validator;

    /**
     * Constructor
     *
     * @param string $serviceclass The service class name to be used by the server
     * @param string $responseclass The response class name to use
     * @param Zend_Validate $validator A vaidator chain used to validate the request
     */
    public function __construct($serviceclass, $responseclass, $validator) {
        $this->validator     = $validator;
        $this->serviceclass  = $serviceclass;
        $this->responseclass = $responseclass;
        $this->response      = $responseclass;
        $this->server        = $this->new_server();
    }

    /**
     * Create a new Zend Server instance
     *
     * @return object
     */
    abstract protected function new_server();

    /**
     * Was the last handle() successful?
     *
     * @return boolean
     */
    public function is_successful() {
        foreach ($this->server->getHeaders() as $header) {
            if (strpos($header, ' 400 ') !== false or strpos($header, ' 404 ') !== false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Document the service and its response
     *
     * @param string $response The server response
     * @return mr_server_abstract
     */
    public function document($response) {
        global $CFG;

        if (PHPUNIT_TEST and defined('LOCAL_MR_PHPUNIT_WEBSERVICE_PRINT')) {
            require_once($CFG->dirroot.'/local/mr/framework/helper.php');

            $helper = new mr_helper();
            $helper->testwebservice->document($this->serviceclass, $this->get_request()->getParam('method'), $response);
        }
        return $this;
    }

    /**
     * Output something nice when running simpletest
     *
     * @param string $response The server response
     * @return mr_server_abstract
     */
    public function simpletest_report($response) {
        global $CFG;

        if (PHPUNIT_TEST and defined('LOCAL_MR_PHPUNIT_WEBSERVICE_PRINT')) {
            require_once($CFG->dirroot.'/local/mr/framework/helper.php');

            $helper = new mr_helper();
            $helper->testwebservice->simpletest_report(
                $this->serviceclass,
                $this->get_request()->getParam('method'),
                $this->get_request()->getParams(),
                $response
            );
        }
        return $this;
    }

    /**
     * Get the HTTP request
     *
     * @return Zend_Controller_Request_Http
     */
    public function get_request() {
        if (!$this->request instanceof Zend_Controller_Request_Http) {
            require_once('Zend/Controller/Request/Http.php');
            $this->request = new Zend_Controller_Request_Http();
        }
        return $this->request;
    }

    /**
     * Get the response instance
     *
     * @return mr_server_response_abstract
     */
    protected function get_response() {
        if (!$this->response instanceof mr_server_response_abstract) {
            $reflection     = new reflectionClass($this->responseclass);
            $this->response = $reflection->newInstance($this, $this->serviceclass);
        }
        return $this->response;
    }

    /**
     * Security checks
     *
     * @throws Exception
     * @return void
     */
    public function security() {
        if (!$this->validator->isValid($this->get_request())) {
            foreach ($this->validator->getMessages() as $message) {
                throw new Exception($message);
            }
        }
    }

    /**
     * Generate server fault XML
     *
     * @param string $message Reason for the fault
     * @param int $code Error code
     * @return string
     */
    public function fault($message, $code = NULL) {
        // Call the server's fault to set headers and the like
        $dom = $this->server->fault(new Exception($message), $code);

        // Allow response to override fault DOM
        if ($faultdom = $this->get_response()->fault($message)) {
            $dom = $faultdom;
        }
        return $dom->saveXML();
    }

    /**
     * Send server headers
     *
     * @return void
     */
    protected function send_headers() {
        $this->get_response()->send_headers($this->server);
    }

    /**
     * Handle web service request
     *
     * @param array|bool $request The request (Really only used for testing)
     * @param boolean $return Return the response or not (Really only used for testing)
     * @return void|string
     */
    public function handle($request = false, $return = false) {
        try {
            // Set the request to our server's request
            if (is_array($request)) {
                $this->get_request()->setParams($request);
            }

            // Security checks
            $this->security();

            // Server setup
            $this->server->setClass($this->serviceclass, '', array($this, $this->get_response()));
            $this->server->returnResponse(true);

            // Output buffer when not testing (ensures clean response)
            if (!PHPUNIT_TEST) {
                ob_start();
            }
            // Run the server
            $response = $this->server->handle($request);

            // Close output buffer if needed
            if (!PHPUNIT_TEST) {
                ob_end_clean();
            }

            // Allow response class to look at the response
            $response = $this->get_response()->post_handle($response);

        } catch (Exception $e) {
            $response = $this->fault($e->getMessage());
        }
        if ($return) {
            return $response;
        }
        $this->send_headers();
        echo $response;
        die;
    }
}