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
 * @see mr_html_notify
 */
require_once($CFG->dirroot.'/local/mr/framework/html/notify.php');

class mr_html_notify_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/html/notify.php');

    public function setUp() {
        global $SESSION;

        unset($SESSION->messages);
    }

    public function tearDown() {
        global $SESSION;

        unset($SESSION->messages);
    }

    public function test_messages() {
        global $SESSION;

        $notify = new mr_html_notify();
        $notify->set_component('local_mr');
        $notify->set_align('right');
        $notify->good('mrframework');
        $notify->bad('mrframework');

        $expected = array(
            array(
                get_string('mrframework', 'local_mr'),
                'notifysuccess',
                'right',
            ),
            array(
                get_string('mrframework', 'local_mr'),
                'notifyproblem',
                'right',
            ),
        );
        $this->assertIdentical($SESSION->messages, $expected);
        $this->assertIdentical($notify->get_messages(), $expected);
        $this->assertTrue(empty($SESSION->messages));
    }
}