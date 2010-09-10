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
     * Service class name
     *
     * @var string
     */
    protected $service;

    /**
     * Server instance
     *
     * @var Zend_*
     */
    protected $server;

    /**
     * Response class
     *
     * @var mr_server_response_abstract
     */
    protected $response;

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
        $this->validator = $validator;
        $this->class     = $serviceclass;
        $this->server    = $this->new_server();
        $this->response  = new $responseclass($this, $serviceclass);
    }

    /**
     * Create a new Zend Server instance
     *
     * @return object
     */
    abstract protected function new_server();

    /**
     * Security checks
     *
     * @return void
     */
    public function security() {
        require_once('Zend/Controller/Request/Http.php');

        $request = new Zend_Controller_Request_Http();
        if (!$this->validator->isValid($request)) {
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
        if ($faultdom = $this->response->fault($message)) {
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
        if (!headers_sent()) {
            $current = headers_list();
            $headers = $this->server->getHeaders();
            foreach ($headers as $header) {
                // Check to see if a header has already been set
                foreach ($current as $set) {
                    $set = explode(':', strtolower($set));
                    $new = explode(':', strtolower($header));
                    if (count($set) > 1 and count($new) > 1 and $set[0] == $new[0]) {
                        continue 2;
                    }
                }
                header($header);
            }
            // header("Response-service-class: $this->class");
            // header('Response-service-method: '. (!empty($_REQUEST['method']) ? $_REQUEST['method'] : ''));
        }
    }

    /**
     * Handle web service request
     *
     * @param array $request The requst (Really only used for testing)
     * @param boolean $return Return the response or not (Really only used for testing)
     * @return void
     */
    public function handle($request = false, $return = false) {
        try {
            // Security checks
            $this->security();

            // Response normally looks at HTTP request for method...
            if (is_array($request) and !empty($request['method'])) {
                $this->response->set_servicemethod($request['method']);
            }

            // Server setup
            $this->server->setClass($this->class, '', array($this, $this->response));
            $this->server->returnResponse(true);

            // Run the server (Run output buffer to capture any Moodle printing)
            ob_start();
            $response = $this->server->handle($request);
            // $debug    = ob_get_contents();  // Debug ONLY!
            ob_end_clean();

            // Allow response class to look at the response
            $response = $this->response->post_handle($response);

            // Debug ONLY!
            // if (isset($debug)) {
            //     $xml = simplexml_load_string($response);
            //     $xml->addChild('debug', $debug);
            //     $response = $xml->asXML();
            // }

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