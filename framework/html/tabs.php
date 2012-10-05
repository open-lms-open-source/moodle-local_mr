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
 * MR HTML Tabs
 *
 * Manage tabs and their sub tabs.
 *
 * @package mr
 * @author Mark Nielsen
 * @example controller/default.php
 */
class mr_html_tabs implements renderable {
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
     * Currently selected toptab index
     *
     * @var string
     */
    public $toptab = '';

    /**
     * Currently selected subtab index
     *
     * @var string
     */
    public $subtab = '';

    /**
     * The last toptab set
     *
     * @var string
     */
    protected $lasttoptabid = '';

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
     * @param string $toptab The toptab index key
     * @param string $subtab The subtab index key
     * @return mr_html_tabs
     */
    public function set($toptab = '', $subtab = '') {
        $this->toptab = $toptab;
        $this->subtab = $subtab;
        return $this;
    }

    /**
     * Based on the current $tobtab, return
     * the tab row or rows.
     *
     * @return array
     */
    public function get_rows() {
        $rows = $toptabs = $subtabs = array();

        if (!empty($this->toptab) and !empty($this->tabs['__parents__'])) {
            foreach ($this->tabs['__parents__'] as $parents) {
                foreach ($parents as $key => $parent) {
                    if ($parent instanceof tabobject) {
                        $toptabs[$key] = $parent;
                    }
                }
            }
            if (!empty($this->tabs[$this->toptab])) {
                foreach ($this->tabs[$this->toptab] as $children) {
                    foreach ($children as $key => $child) {
                        if ($child instanceof tabobject) {
                            $subtabs[$key] = $child;
                        }
                    }
                }
            }

            $rows[] = $toptabs;
            if (!empty($subtabs)) {
                $rows[] = $subtabs;
            }
        }
        return $rows;
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
     * @return mr_html_tabs
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
     * @return mr_html_tabs
     * @throws coding_exception
     */
    public function add_subtab($parentid, $id, $url, $name = NULL, $weight = 0, $visible = true, $title = '', $linkedwhenselected = true) {
        if ($parentid == '__parents__') {
            $this->lasttoptabid = $id;
        }
        if (is_array($url)) {
            if (is_null($this->url)) {
                throw new coding_exception('Must pass a moodle_url to constructor to pass an array of URL params');
            }
            $url = $this->url->out(false, $url);
        }
        if (empty($name)) {
            if ($parentid != '__parents__') {
                $prefix = $parentid;
            } else {
                $prefix = '';
            }
            $name = get_string("$prefix{$id}tab", $this->component);
        }

        $this->tabs[$parentid][$weight][$id] = ($visible ? new tabobject($id, $url, $name, $title, $linkedwhenselected) : false);
        ksort($this->tabs[$parentid]);
        return $this;
    }

    /**
     * Simple interface: Adds a top tab
     *
     * @param string $id The unique top tab ID
     * @param mixed $url moodle_url or an array of params
     * @param boolean $visible If the tab is visible to the user or not
     * @return mr_html_tabs
     * @throws coding_exception
     */
    public function toptab($id, $url = array(), $visible = true) {
        return $this->add($id, $url, NULL, 0, $visible);
    }

    /**
     * Simple interface: Adds a sub tab
     *
     * @param string $id The unique sub tab ID
     * @param mixed $url moodle_url or an array of params
     * @param boolean $visible If the tab is visible to the user or not
     * @param string $toptabid The top tab's ID that the sub tab belongs to (defaults to the last top tab's ID)
     * @return mr_html_tabs
     * @throws coding_exception
     */
    public function subtab($id, $url = array(), $visible = true, $toptabid = NULL) {
        if ($toptabid == NULL) {
            if (empty($this->lasttoptabid)) {
                throw new coding_exception('No toptabs have been added, create one before creating subtabs');
            }
            $toptabid = $this->lasttoptabid;
        }
        return $this->add_subtab($toptabid, $id, $url, NULL, 0, $visible);
    }
}