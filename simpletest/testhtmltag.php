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
 * @see mr_html_tag
 */
require_once($CFG->dirroot.'/local/mr/framework/html/tag.php');

class mr_html_tag_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/html/tag.php');

    public function test_tag() {
        $tag = new mr_html_tag();

        $this->assertEqual($tag->strong('Text')->foo('bar')->close(), '<strong foo="bar">Text</strong>');
        $this->assertEqual($tag->strong()->foo('bar')->close('Text'), '<strong foo="bar">Text</strong>');
        $this->assertEqual($tag->strong('Text')->foo('bar')->close('Text'), '<strong foo="bar">TextText</strong>');
        $this->assertEqual($tag->input()->foo('bar')->close(), '<input foo="bar" />');
        $this->assertEqual($tag->p()->close(), '<p />');
    }

    public function test_to_string() {
        $tag  = new mr_html_tag();
        $html = (string) $tag->input()->foo('bar');
        $this->assertEqual($html, '<input foo="bar" />');
    }

    public function test_attributes() {
        $tag = new mr_html_tag();

        $link = $tag->a('Click me!')
                    ->title('This " should be encoded')
                    ->href('http://google.com')
                    ->class('foo');

        $link->prepend_class('bar')
             ->append_class('baz')
             ->remove_title();

        $this->assertEqual($link->close(), '<a href="http://google.com" class="bar foo baz">Click me!</a>');

        $strong = $tag->strong()->foo('bar');

        $strong->append_attributes(array('foo' => 'har', 'bat' => 'baz'));
        $strong->prepend_attributes(array('foo' => 'tar', 'bat' => 'haz', 'foo2' => 'bar2'));
        $strong->add_attributes(array('dingo' => '8', 'jet' => 'jaz'));
        $this->assertEqual($strong->get_foo(), 'tar bar har');
        $this->assertEqual($strong->get_bat(), 'haz baz');
        $this->assertEqual($strong->get_dingo(), '8');
        $this->assertEqual($strong->get_jet(), 'jaz');
        $this->assertEqual($strong->get_foo2(), 'bar2');

        $strong->remove_attributes(array('foo', 'bat'));
        $this->assertFalse($strong->get_foo());
        $this->assertFalse($strong->get_bat());
    }

    public function test_static() {
        $this->assertEqual(mr_html_tag::open()->strong('Text')->foo('bar')->close(), '<strong foo="bar">Text</strong>');
    }

    public function test_bad_close() {
        $this->expectException('coding_exception');
        mr_html_tag::open()->close();
    }

    public function test_bad_no_attribute_params() {
        $this->expectException('coding_exception');
        mr_html_tag::open()->strong()->foo();
    }

    public function test_bad_no_attribute_manipulation_params() {
        $this->expectException('coding_exception');
        mr_html_tag::open()->strong()->append_class();
    }

    public function test_nonexistent_method() {
        $this->expectException('coding_exception');
        mr_html_tag::open()->strong()->something_crazy();
    }
}