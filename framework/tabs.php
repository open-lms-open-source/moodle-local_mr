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
class mr_tabs {
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
     * Get string module key
     *
     * @var string
     */
    protected $module = '';

    /**
     * Constructor
     *
     * @param moodle_url $url Base URL for the tabs
     * @param string $module Default get string module key
     */
    public function __construct($url = NULL, $module = '') {
        $this->url    = $url;
        $this->module = $module;
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
                $name = get_string("{$id}tab", $this->module);
            }

            $this->tabs[$parentid][$weight][$id] = new tabobject($id, $url, $name, $title, $linkedwhenselected);
        }
        return $this;
    }

    /**
     * Display the tabs
     *
     * @param string $tab The current tab
     * @param string $subtab (Optional) The sub tab that is selected
     * @return string
     */
    public function display($tab, $subtab = NULL) {
        if (!empty($tab) and !empty($this->tabs['__parents__'])) {
            // Default
            $currenttab = $tab;

            $tabs = $row = $inactive = $active = array();

            ksort($this->tabs['__parents__']);
            foreach ($this->tabs['__parents__'] as $parents) {
                $row = array_merge($row, $parents);
            }
            $tabs[] = $row;

            if (!empty($subtab) and !empty($this->tabs[$tab])) {
                $active[]   = $tab;
                $currenttab = $subtab;
                $row        = array();
                ksort($this->tabs[$tab]);
                foreach ($this->tabs[$tab] as $children) {
                    $row = array_merge($row, $children);
                }
                $tabs[] = $row;
            }
            return print_tabs($tabs, $currenttab, $inactive, $active, true);
        }
        return '';
    }
}