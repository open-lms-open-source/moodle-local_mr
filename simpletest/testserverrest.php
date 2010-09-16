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
 * @see mr_server_rest
 */
require_once($CFG->dirroot.'/local/mr/framework/server/rest.php');

/**
 * @see mr_server_service_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/server/service/abstract.php');

/**
 * @see mr_server_response_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/server/response/abstract.php');

/**
 * @see mr_server_validate_test
 */
require_once($CFG->dirroot.'/local/mr/framework/server/validate/test.php');

/**
 * @see Zend_Validate
 */
require_once 'Zend/Validate.php';

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

class mr_server_rest_test extends UnitTestCase {

    public static $includecoverage = array(
        'local/mr/framework/server/abstract.php',
        'local/mr/framework/server/rest.php',
        'local/mr/framework/server/service/abstract.php',
        'local/mr/framework/server/response/abstract.php',
    );

    public function test_server() {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mr_server_service_test generator="zend" version="1.0"><foo><response>Hello World!</response><status>success</status></foo></mr_server_service_test>

XML;

        $validator = new Zend_Validate();
        $validator->addValidator(new mr_server_validate_test());

        $server   = new mr_server_rest('mr_server_service_test', 'mr_server_response_test', $validator);
        $response = $server->handle(array('method' => 'foo', 'bar' => 'Hello World!'), true);
        $this->assertEqual($response, $expected);
    }

    public function test_server_validation() {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rest generator="zend" version="1.0"><response><message>Fail!</message></response><status>failed</status></rest>

XML;

        $validator = new Zend_Validate();
        $validator->addValidator(new mr_server_validate_test_fail());

        $server   = new mr_server_rest('mr_server_service_test', 'mr_server_response_test', $validator);
        $response = $server->handle(array('method' => 'foo', 'bar' => 'Hello World!'), true);
        $this->assertEqual($response, $expected);
    }

    public function test_server_array_response() {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mr_server_service_test generator="zend" version="1.0"><testarray><params><param>Hello World!</param></params><status>success</status></testarray></mr_server_service_test>

XML;

        $validator = new Zend_Validate();
        $validator->addValidator(new mr_server_validate_test());

        $server   = new mr_server_rest('mr_server_service_test', 'mr_server_response_test', $validator);
        $response = $server->handle(array('method' => 'testarray', 'bar' => 'Hello World!'), true);
        $this->assertEqual($response, $expected);
    }

    public function test_server_bad_response_call() {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mr_server_service_test generator="zend" version="1.0"><badresponsecall><response><message>Coding error detected, it must be fixed by a programmer: Call to undefined response: class = mr_server_response_test method = test_badresponsecall</message></response><status>failed</status></badresponsecall></mr_server_service_test>

XML;

        $validator = new Zend_Validate();
        $validator->addValidator(new mr_server_validate_test());

        $server   = new mr_server_rest('mr_server_service_test', 'mr_server_response_test', $validator);
        $response = $server->handle(array('method' => 'badresponsecall', 'bar' => 'Hello World!'), true);
        $this->assertEqual($response, $expected);
    }
}

/**
 * Test Classes
 */

class mr_server_service_test extends mr_server_service_abstract {
    public function foo($bar) {
        return $this->response->test_foo($bar);
    }
    public function testarray($bar) {
        return $this->response->test_testarray($bar);
    }
    public function badresponsecall($bar) {
        return $this->response->test_badresponsecall($bar);
    }
}

class mr_server_response_test extends mr_server_response_abstract {
    public function test_foo($bar) {
        return $this->standard($bar);
    }
    public function test_testarray($bar) {
        $response = array(
            'params' => array(
                array('param' => $bar),
            )
        );
        return $this->standard($response);
    }
}

class mr_server_validate_test_fail extends Zend_Validate_Abstract {
    const FAIL = 'fail';
    protected $_messageTemplates = array(self::FAIL => "Fail!");

    public function isValid($request) {
        $this->_error(self::FAIL);
        return false;
    }
}