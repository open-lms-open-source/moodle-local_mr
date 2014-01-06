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
 * @see mr_readonly
 */
require_once($CFG->dirroot.'/local/mr/framework/readonly.php');

/**
 * MR HTML Paging
 *
 * Provides a paging bar and a results per page selector.
 *
 * @package mr
 * @author Mark Nielsen
 * @example controller/table.php See how to use this class
 */
class mr_html_paging extends mr_readonly implements renderable {
    /**
     * Page request param
     */
    public $REQUEST_PAGE = 'tpage';

    /**
     * Page request param
     */
    public $REQUEST_PERPAGE = 'tperpage';

    /**
     * The total number of entries available to be paged through
     *
     * @var int
     */
    protected $total = 0;

    /**
     * Current page number
     *
     * @var int
     */
    protected $page = 0;

    /**
     * The number of enteries per page
     *
     * @var int
     */
    protected $perpage = 50;

    /**
     * Per page options
     *
     * @var mixed
     */
    protected $perpageopts = false;

    /**
     * Base URL
     *
     * @var moodle_url
     */
    protected $url;

    /**
     * Preferences
     *
     * @var mr_preferences
     */
    protected $preferences;

    /**
     * Constructor
     *
     * @param mr_preferences $preferences Preferences to store paging information
     * @param moodle_url $url Current URL
     */
    public function __construct(mr_preferences $preferences, moodle_url $url) {
        $this->url         = $url;
        $this->preferences = $preferences;
        $defultperpage     = $this->preferences->get($this->REQUEST_PERPAGE, $this->perpage);
        $this->page        = optional_param($this->REQUEST_PAGE, 0, PARAM_INT);
        $this->perpage     = optional_param($this->REQUEST_PERPAGE, $defultperpage, PARAM_INT);

        // Save for later
        $this->preferences->set($this->REQUEST_PERPAGE, $this->perpage);
    }

    /**
     * Convert this table into a simple string
     *
     * @return string
     */
    public function __toString() {
        return "page{$this->page}perpage{$this->perpage}";
    }

    /**
     * Set total
     *
     * @param int $total The total
     * @return mr_html_paging
     */
    public function set_total($total) {
        $this->total = $total;
        return $this;
    }

    /**
     * Set the page
     *
     * @param int $page
     * @return mr_html_paging
     */
    public function set_page($page){
        $this->page = $page;
        return $this;
    }

    /**
     * Set perpage
     *
     * @param int $size Page size
     * @return mr_html_paging
     */
    public function set_perpage($size) {
        $this->perpage = $size;
        return $this;
    }

    /**
     * Perpage options
     *
     * @param mixed $options An array of options or false or NULL for default set of options
     * @return mr_html_paging
     */
    public function set_perpageopts($options = NULL) {
        if (is_null($options)) {
            $options = array(
                'all', 10, 25, 50, 100, 200, 500, 1000,
            );
        }
        $this->perpageopts = $options;
        return $this;
    }

    /**
     * Set the export instance
     *
     * This will set the limitfrom and limitnum
     * appropriately for exporting
     *
     * @param mr_file_export $export The export plugin
     * @return mr_html_paging
     */
    public function set_export($export) {
        if ($export->is_exporting()) {
            // Start from the beginning
            $this->page = 0;

            // Grab as many rows as the exporter can handle
            $this->perpage = $export->instance()->max_rows();
        }
        return $this;
    }

    /**
     * Get limitfrom SQL value
     *
     * @return int
     */
    public function get_limitfrom() {
        return $this->page * $this->perpage;
    }

    /**
     * Get limitnum SQL value
     *
     * @return mixed
     */
    public function get_limitnum() {
        if (empty($this->perpage)) {
            return '';
        }
        return $this->perpage;
    }
}