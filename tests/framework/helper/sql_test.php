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
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboardopenlms.com)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @package mr
 * @author Adam Olley
 */

require_once(dirname(dirname(__DIR__)).'/bootstrap.php');

/**
 * Test mr_helper_sql
 *
 * @package mr
 * @author Adam Olley
 */
class mr_helper_sql_test extends advanced_testcase {

    public function test_from_unixtime() {
        global $DB;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $course->timecreated = strtotime("2016-02-18 13:05:00");
        $DB->update_record('course', $course);

        $field = mr_helper_sql::from_unixtime('timecreated', 'Y d n m F');
        $result = $DB->get_field_sql("SELECT $field FROM {course} WHERE id = ?", [$course->id]);

        $this->assertEquals($result, "2016 18 2 02 February");
    }

}
