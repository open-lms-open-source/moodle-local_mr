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
 * MR Server Response Abstract
 *
 * This class is responsible for generating the
 * response body for a particular service.  The reason
 * to have a separate class to handle responses is so that
 * a single service could be paired with different response
 * classes at different endpoints.  So, same functioning service
 * but different customized responses.
 *
 * @author Mark Nielsen
 * @package mr
 * @example webservices.php Example usage with the server
 * @example model/response.php Example class definition
 */
abstract class mr_server_response_abstract {
    /**
     * The server
     *
     * @var mr_server_abstract
     */
    protected $server;

    /**
     * The service class name
     *
     * @var string
     */
    protected $serviceclass;

    /**
     * The service class method name
     *
     * @var string
     */
    protected $servicemethod;

    /**
     * Constructor
     *
     * @param mr_server_abstract $server The current server model
     * @param string $serviceclass The web service class
     */
    public function __construct($server, $serviceclass) {
        $this->server       = $server;
        $this->serviceclass = $serviceclass;

        $this->set_servicemethod(optional_param('method', 'unknown', PARAM_ALPHAEXT))
             ->init();
    }

    /**
     * Constructor hook
     *
     * @return void
     */
    protected function init() {
    }

    /**
     * Set the service method
     *
     * @param string $value The value to set
     * @return mr_server_response_abstract
     */
    public function set_servicemethod($value) {
        $value = clean_param($value, PARAM_ALPHAEXT);
        $value = eregi_replace('[^a-zA-Z_]', '', $value);

        if (empty($value)) {
            $value = 'unknown';
        }
        $this->servicemethod = $value;
        return $this;
    }

    /**
     * Generate default DOM structure
     *
     * @return DOMDocument
     */
    public function new_dom() {
        $dom = new DOMDocument('1.0', 'UTF-8');

        $root   = $dom->createElement($this->serviceclass);
        $method = $dom->createElement($this->servicemethod);

        $root->appendChild($method);
        $root->setAttribute('generator', 'zend');
        $root->setAttribute('version', '1.0');

        $dom->appendChild($root);

        return $dom;
    }

    /**
     * Server fault response (Return false to use Zend Server's)
     *
     * @param string $message Reason for the fault
     * @return mixed
     */
    public function fault($message) {
        return false;
    }

    /**
     * View the response returned by Zend Server
     *
     * This gives the response class to map any
     * Zend Server faults to its own.
     *
     * @param string $response Response XML
     * @return string
     */
    public function post_handle($response) {
        return $response;
    }

    /**
     * Standard response structure
     *
     * @param mixed $response An array or string of response data
     * @param boolean $status Web service request status
     * @return DOMDocument
     */
    public function standard($response = NULL, $status = true) {
        $dom    = $this->new_dom();
        $method = $dom->getElementsByTagName($this->servicemethod)->item(0);

        if (!empty($response)) {
            if (is_array($response)) {
                $this->array_to_dom($response, $dom, $method);
            } else if (is_string($response)) {
                $element = $dom->createElement('response');
                $element->appendChild($dom->createTextNode($response));
                $method->appendChild($element);
            }
        }
        if ($status) {
            $method->appendChild($dom->createElement('status', 'success'));
        } else {
            $method->appendChild($dom->createElement('status', 'failed'));
        }

        return $dom;
    }

    /**
     * Converts an array into DOMDocument
     *
     * @param array $array The array to convert
     * @param DOMDocument $dom The document
     * @param DOMElement $parent Parent element in the $dom
     * @return void
     */
    protected function array_to_dom($array, $dom, $parent) {
        foreach ($array as $key => $value) {
            if (is_array($value) || is_object($value)) {
                if (!ctype_digit((string) $key)) {
                    $element = $dom->createElement($key);
                    $parent->appendChild($element);
                } else {
                    $element = $parent;
                }
                $this->array_to_dom($value, $dom, $element);

            } else if (!ctype_digit((string) $key)) {
                if ($value === false) {
                    $value = 0;
                } elseif ($value === true) {
                    $value = 1;
                }
                $element = $dom->createElement($key);
                $element->appendChild($dom->createTextNode($value));
                $parent->appendChild($element);
            }
        }
    }

    /**
     * Handle undefined method calls
     *
     * @param string $name Method name
     * @param array $arguments Method args
     * @return DOMDocument
     */
    public function __call($name, $arguments) {
        $class = get_class($this);
        throw new coding_exception("Call to undefined response: class = $class method = $name");
    }
}