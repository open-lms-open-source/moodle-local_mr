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
            echo $this->markup;
        }
    }

    /**
     * Document a web service along with its response
     *
     * @param string $claassname The service class name
     * @param string $methodname The service method name
     * @param string $response The server response
     * @return void
     * @author Mark Nielsen
     */
    public function document($claassname, $methodname, $response) {
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
                $markup .= $dom->saveXML();
                $markup .= "\n{code}\n";
            } else if (($json = json_decode($response)) !== NULL) {
                $markup .= "{noformat}\n";
                $markup .= $response;
                $markup .= "\n{noformat}\n\n";
                $markup .= "*Example Response (decoded JSON):*\n";
                $markup .= "{noformat}\n";
                $markup .= print_r($json, true);
                $markup .= "\n{noformat}\n";
            } else {
                $markup .= "{noformat}\n";
                $markup .= $response;
                $markup .= "\n{noformat}\n";
            }
            $markup = $this->generalize_text($markup);

            // Add to overall markup string
            $this->markup .= $markup;
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
        global $OUTPUT;

        echo $OUTPUT->box_start();
        echo '<span class="notice">Service class:</span> '.$serviceclass.'<br />';
        echo '<span class="notice">Service method:</span> '.$servicemethod.'<br />';
        echo '<span class="notice">Request params:</span><br />';
        print_object($requestparams);
        echo '<span class="notice">Server response:</span><br />';
        echo $this->format_response($response);
        echo $OUTPUT->box_end();
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
            return '<pre class="notifytiny">'.s($dom->saveXML()).'</pre>';
        } else if (($json = json_decode($response)) !== NULL) {
            return '<pre class="notifytiny">'.print_r($json, true).'</pre>';
        } else {
            return '<pre class="notifytiny">'.s($response).'</pre>';
        }
    }
}