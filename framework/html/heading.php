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
 * MR HTML Heading
 *
 * Simple renderable object for
 * headings.  Can include help
 * text and icon as well.
 *
 * @package mr
 * @author Mark Nielsen
 */
class mr_html_heading implements renderable {
    /**
     * Heading display text
     *
     * @var string
     */
    public $text = '';

    /**
     * Size of heading
     *
     * @var int
     */
    public $level = 2;

    /**
     * Heading classes
     *
     * @var string
     */
    public $classes = 'main';

    /**
     * Heading ID
     *
     * @var string
     */
    public $id = NULL;

    /**
     * Header icon
     *
     * @var string|moodle_url
     */
    public $icon = '';

    /**
     * Header icon alt text
     *
     * @var string
     */
    public $iconalt = '';

    /**
     * Get string component
     *
     * @var string
     */
    public $component;

    /**
     * Constructor
     *
     * @param string $component Get string component
     */
    public function __construct($component) {
        $this->component = $component;
    }

    /**
     * Set heading
     *
     * @param string $identifier String key to pass to get_string()
     * @param string $helpidentifier Help button identifier.  If you define "{$identifier}_help" then this will automatically be set for you.
     * @param string $a Additional variables to pass to get_string()
     * @return void
     */
    public function set($identifier, $helpidentifier = '', $a = NULL) {
        $this->text = get_string($identifier, $this->component, $a);
        $this->helpidentifier = $helpidentifier;

        if (empty($helpidentifier) and get_string_manager()->string_exists("{$identifier}_help", $this->component)) {
            $this->helpidentifier = $identifier;
        }
        if (!empty($helpidentifier) and strpos($this->classes, 'help') === false) {
            $this->classes .= ' help';
        }
    }
}