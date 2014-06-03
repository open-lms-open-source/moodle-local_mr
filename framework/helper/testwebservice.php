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
 * @see mr_helper_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/helper/abstract.php');

/**
 * @see mr_bootstrap
 */
require_once($CFG->dirroot.'/local/mr/framework/bootstrap.php');

/**
 * MR Helper Test Web Service
 *
 * This helper is primarily for mr_server_abstract
 *
 * @package mr
 * @author Mark Nielsen
 */
class mr_helper_testwebservice extends mr_helper_abstract {
    /**
     * Wiki Markup
     *
     * @var string
     */
    protected $markup = '';

    /**
     * Print final markup string
     *
     * @return void
     * @todo Some other way to do this?
     */
    public function __destruct() {
        if (!empty($this->markup)) {
            echo trim($this->markup);
        }
    }

    /**
     * Document a web service along with its response
     *
     * @param string $classname The service class name
     * @param string $methodname The service method name
     * @param string $response The server response
     * @return string
     * @author Mark Nielsen
     */
    public function document($classname, $methodname, $response) {
        $markup = '';

        if (!empty($methodname)) {
            mr_bootstrap::zend();

            require_once('Zend/Reflection/Class.php');

            $class  = new Zend_Reflection_Class($classname);
            $method = $class->getMethod($methodname);
            $phpdoc = $method->getDocblock();
            $params = $method->getParameters();

            $description = $phpdoc->getShortDescription();
            $longdesc    = $phpdoc->getLongDescription();
            if (!empty($longdesc)) {
                $description .= "\n$longdesc";
            }

            $markup .= "h2. $methodname\n";
            $markup .= "*Description:*\n$description\n\n";
            $markup .= "*Parameters:*\n";
            $markup .= "* _string_ *method*: (Required) Must be set to '$methodname'\n";

            /** @var $params Zend_Reflection_Parameter[] */
            foreach ($params as $param) {
                $name = $param->getName();
                $tags = $phpdoc->getTags('param');

                if (isset($tags[$param->getPosition()])) {
                    $tag = $tags[$param->getPosition()];
                    $typestr = '_'.$tag->getType().'_ ';
                    $descstr = $tag->getDescription();
                } else {
                    $typestr = $descstr = '';
                }
                if ($param->isOptional() and $param->isDefaultValueAvailable()) {
                    $default = $param->getDefaultValue();

                    if (is_null($default)) {
                        $default = 'NULL';
                    } else if (!is_numeric($default) and is_string($default)) {
                        $default = "'$default'";
                    }
                    $descstr = "(Optional, default = $default) $descstr";
                } else if ($param->isOptional()) {
                    $descstr = "(Optional) $descstr";
                } else {
                    $descstr = "(Required) $descstr";
                }

                $markup .= "* $typestr*$name*: $descstr\n";
            }

            $markup .= "\n*Example Response:*\n";

            if ($simplexml = @simplexml_load_string($response)) {
                $dom = dom_import_simplexml($simplexml)->ownerDocument;
                $dom->formatOutput = true;
                $markup .= "{code:xml}\n";
                $markup .= trim($dom->saveXML());
                $markup .= "\n{code}\n";
            } else if (($json = json_decode($response)) !== NULL) {
                $markup .= "{noformat}\n";
                $markup .= $response;
                $markup .= "\n{noformat}\n\n";
                $markup .= "*Example Response (decoded JSON):*\n";
                $markup .= "{noformat}\n";
                $markup .= trim(print_r($json, true));
                $markup .= "\n{noformat}\n";
            } else {
                $markup .= "{noformat}\n";
                $markup .= trim($response);
                $markup .= "\n{noformat}\n";
            }
            $markup = $this->generalize_text($markup);

            // Add to overall markup string
            $this->markup .= "$markup\n\n";
        }
        return $markup;
    }

    /**
     * Generate nice output for simpletest regarding a request
     *
     * @param string $serviceclass The service class name
     * @param string $servicemethod The service class method name
     * @param array $requestparams The request parameters
     * @param string $response The server response
     * @return void
     */
    public function simpletest_report($serviceclass, $servicemethod, $requestparams, $response) {
        echo "Service class:  $serviceclass\n";
        echo "Service method: $servicemethod\n";
        echo "Request params:\n";
        var_export($requestparams);
        echo "\nServer response:\n";
        echo $this->format_response($response);
    }

    /**
     * Strip out site specific information from text
     *
     * @param string $string The string to strip
     * @return string
     */
    public function generalize_text($string) {
        global $CFG;

        $searches = array(
            $CFG->wwwroot,
            str_replace('/', '\/', $CFG->wwwroot),
            str_replace('http://', '', $CFG->wwwroot),
            str_replace('/', '\/', str_replace('http://', '', $CFG->wwwroot)),
            'CISCO',
            'mark@moodlerooms.com',
        );

        $replaces = array(
            'http://example.com',
            'http://example.com',
            'example.com',
            'example.com',
            'Site',
            'email@example.com',
        );
        return trim(str_replace($searches, $replaces, $string));
    }

    /**
     * Given a server response, generate output for HTML
     *
     * @param string $response The server response
     * @return string
     */
    public function format_response($response) {
        if ($simplexml = @simplexml_load_string($response)) {
            $dom = dom_import_simplexml($simplexml)->ownerDocument;
            $dom->formatOutput = true;
            return $dom->saveXML();
        } else if (($json = json_decode($response)) !== NULL) {
            return var_export($json, true);
        } else {
            return $response;
        }
    }

    /**
     * Generate debugging information for a web service request
     *
     * This can be handy to throw into exceptions.
     *
     * @param string $request The last request
     * @param Zend_Http_Response $response The last response
     * @throws coding_exception
     * @return string
     */
    public function debuginfo($request, $response) {
        if (!$response instanceof Zend_Http_Response) {
            throw new coding_exception('Passed invalid response');
        }
        $output  = "LAST REQUEST\n\n$request\n\n";
        $output .= "LAST RESPONSE\n\n";

        if ($simplexml = @simplexml_load_string($response->getBody())) {
            $dom = dom_import_simplexml($simplexml)->ownerDocument;
            $dom->formatOutput = true;
            $output .= $dom->saveXML();
        } else if (($json = json_decode($response->getBody())) !== NULL) {
            $output .= print_r($json, true);
        } else {
            $output .= $response->getBody();
        }
        return $output;
    }
}