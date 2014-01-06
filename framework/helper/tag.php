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
 * @see mr_html_tag
 */
require_once($CFG->dirroot.'/local/mr/framework/html/tag.php');

/**
 * MR Tag Helper
 *
 * This helper is used for quick and easy access
 * to mr_html_tag.  Automatic setup of mr_html_tag and
 * access to all of mr_html_tag methods.
 *
 * @author Mark Nielsen
 * @package mr
 * @example controller/default.php See this in action
 */
class mr_helper_tag extends mr_helper_abstract {
    /**
     * HTML tag
     *
     * @var mr_html_tag
     */
    protected $tag;

    /**
     * Create new instance of mr_html_tag
     */
    public function __construct() {
        $this->tag = new mr_html_tag();
    }

    /**
     * Get an instance of mr_html_tag
     *
     * @return mr_html_tag
     */
    public function direct() {
        return $this->tag;
    }

    /**
     * Direct all other calls to mr_html_tag
     *
     * @param string $name A method in mr_html_tag
     * @param array $arguments The args to pass to the mr_html_tag method
     * @return mixed
     * @throws coding_exception
     */
    public function __call($name, $arguments) {
        return call_user_func_array(array($this->tag, $name), $arguments);
    }
}