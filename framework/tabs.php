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

/**
 * MR Tabs
 *
 * Manage tabs and their sub tabs.
 *
 * @package mr
 * @author Mark Nielsen
 * @example controller/default.php
 */
class mr_tabs implements renderable {
    /**
     * An array of tabobjects
     *
     * @var array
     */
    protected $tabs = array('__parents__' => array());

    /**
     * Base URL for tabs
     *
     * @var moodle_url
     */
    protected $url;

    /**
     * Get string component key
     *
     * @var string
     */
    protected $component = '';

    /**
     * Currently selected tab index
     *
     * @var string
     */
    protected $current = '';

    /**
     * Constructor
     *
     * @param moodle_url $url Base URL for the tabs
     * @param string $component Default get string component key
     */
    public function __construct($url = NULL, $component = '') {
        $this->url       = $url;
        $this->component = $component;
    }

    /**
     * Set the current tab
     *
     * @param string $tabindex The tab index key
     * @return mr_tab
     */
    public function set($tabindex) {
        $this->current = $tabindex;
        return $this;
    }

    /**
     * Get the current tab
     *
     * @return string
     */
    public function get_current() {
        return $this->current;
    }

    /**
     * Get the defined tabs
     *
     * @return array
     */
    public function get_tabs() {
        return $this->tabs;
    }

    /**
     * Add a top level tab
     *
     * @param string $id Tab ID, must be unique
     * @param mixed $url Either a full string URL or an array of paramaters to pass to $this->url
     * @param string $name The tab display name
     * @param int $weight The weight, tabs will be sorted by this
     * @param boolean $visible If the tab is visible, EG: pass result of a has_capability() call here
     * @param string $title The alt text of the tab
     * @param boolean $linkedwhenselected Keep the tab clickable when selected
     * @return mr_tabs
     */
    public function add($id, $url, $name = NULL, $weight = 0, $visible = true, $title = '', $linkedwhenselected = true) {
        return $this->add_subtab('__parents__', $id, $url, $name, $weight, $visible, $title, $linkedwhenselected);
    }

    /**
     * Add a sub tab
     *
     * @param string $parentid The top level tab ID that this sub tab belongs
     * @param string $id Tab ID, must be unique
     * @param mixed $url Either a full string URL or an array of paramaters to pass to $this->url
     * @param string $name The tab display name.
     * @param int $weight The weight, tabs will be sorted by this
     * @param boolean $visible If the tab is visible, EG: pass result of a has_capability() call here
     * @param string $title The alt text of the tab
     * @param boolean $linkedwhenselected Keep the tab clickable when selected
     * @return mr_tabs
     * @throws coding_exception
     */
    public function add_subtab($parentid, $id, $url, $name = NULL, $weight = 0, $visible = true, $title = '', $linkedwhenselected = true) {
        if ($visible) {
            if (is_array($url)) {
                if (is_null($this->url)) {
                    throw new coding_exception('Must pass a moodle_url to constructor to pass an array of URL params');
                }
                $url = $this->url->out(false, $url);
            }
            if (empty($name)) {
                $name = get_string("{$id}tab", $this->component);
            }

            $this->tabs[$parentid][$weight][$id] = new tabobject($id, $url, $name, $title, $linkedwhenselected);
            ksort($this->tabs[$parentid]);
        }
        return $this;
    }
}