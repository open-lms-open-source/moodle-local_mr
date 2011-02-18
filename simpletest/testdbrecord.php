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
 * @see mr_db_record
 */
require_once($CFG->dirroot.'/local/mr/framework/db/record.php');

class mr_db_record_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/db/record.php');

    public function test_default_bad_id() {
        // ID should not be set to $record
        $record = new mr_db_record('user', array('id' => NULL, 'username' => 'simpletest'));
        $this->expectException('coding_exception');
        $record->id;
    }

    public function test_set_bad_id() {
        $record = new mr_db_record('user', array('username' => 'simpletest'));
        $this->expectException('coding_exception');
        $record->id = NULL;
    }
}