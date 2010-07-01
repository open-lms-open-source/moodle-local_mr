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
 * MR HTML Filter
 *
 * This controls the setup, interaction and usage
 * of a moodleform class and mr_html_filter_*
 * classes.
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_html_filter {
    /**
     * Added filters
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Exclude filter SQL of filters defined in here
     *
     * @var array
     */
    protected $excludesql = array();

    /**
     * User preferences
     *
     * @var mr_preferences
     */
    protected $preferences;

    /**
     * Base URL
     *
     * @var moodle_url
     */
    protected $url;

    /**
     * The path to the form class
     *
     * @var string
     */
    protected $formpath;

    /**
     * Filter form
     *
     * @var moodleform
     */
    protected $mform;

    /**
     * Helper model
     *
     * @var mr_helper
     */
    protected $helper;

    /**
     * Construct
     *
     * @param mr_preferences Preferences model
     * @param moodle_url $url Base URL
     * @param string $formpath The patch to the form class, passed to mr_helper_load
     */
    public function __construct($preferences, $url, $formpath = 'local/mr/form/filter') {
        $this->url         = $url;
        $this->helper      = new mr_helper();
        $this->formpath    = $formpath;
        $this->preferences = $preferences;
    }

    /**
     * After filters have been added, you can
     * initialze the form and handle submitted data
     *
     * @return mr_html_filter
     * @throws coding_exception
     */
    public function init() {
        global $CFG;

        if (empty($this->filters)) {
            throw new coding_exception('Must add filters');
        }
        if (empty($this->mform)) {
            $this->mform = $this->helper->load($this->formpath, array($this->url, $this->filters), false);

            if ($data = $this->mform->get_data()) {
                if (!empty($data->resetbutton)) {
                    foreach ($this->filters as $filter) {
                        $filter->preferences_delete();
                    }
                } else {
                    foreach ($this->filters as $filter) {
                        $filter->preferences_update($data);
                    }
                }
                redirect($this->url);
            }
        }
        return $this;
    }

    /**
     * Return filter SQL
     *
     * @return string
     * @todo Return prepared statement and values?
     */
    public function sql() {
        $this->init();

        $sqlands = array();
        foreach ($this->filters as $filter) {
            if (in_array($filter->get_name(), $this->excludesql)) {
                continue;
            }
            if (($filter->get_field() != '') && ($sql = $filter->sql())) {
                $sqlands[] = $sql;
            }
        }
        if (!empty($sqlands)) {
            return ' AND '.implode(' AND ', $sqlands);
        }
        return '';
    }

    /**
     * Pass filter names to this method to exclude their
     * SQL from the mr_html_filter::sql() method.
     *
     * @param string $param Keep passing filter names to exclude
     * @return mr_html_filter
     * @throws coding_exception
     */
    public function exclude_sql() {
        $args = func_get_args();
        foreach ($args as $arg) {
            if (!is_string($arg)) {
                throw new coding_exception('Can only pass strings to exclude_sql()');
            }
            $this->excludesql[$arg] = $arg;
        }
        return $this;
    }

    /**
     * Display the form
     *
     * @return void
     * @todo Remove this? Implement renderable?
     */
    public function display() {
        foreach ($this->filters as $filter) {
            if (!($filter instanceof mr_html_filter_hidden)) {
                $this->init();
                $this->mform->display();
                break;
            }
        }
    }

    /**
     * Add a filter
     *
     * @param mr_html_filter_abstract $filter A filter instance
     * @return mr_html_filter
     */
    public function add(mr_html_filter_abstract $filter) {
        $filter->preferences_init($this->preferences);
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Use this to add new filters to the filter model
     *
     * Example:
     *    ->new_text(...args...);
     *
     * @param string $name The name of the filter
     * @param array $arguments Filter args
     * @return mr_html_filter
     * @throws coding_exception
     */
    public function __call($name, $arguments) {
        $parts = explode('_', $name);

        if (count($parts) == 2) {
            switch ($parts[0]) {
                case 'new':
                    return $this->add($this->helper->load('html/filter/'.$parts[1], $arguments));
                    break;
            }
        }
        throw new coding_exception('Invalid call to mr_html_filter');
    }

    /**
     * Convert this filter into a simple string
     *
     * @return string
     */
    public function __toString() {
        $string = '';
        foreach ($this->filters as $filter) {
            $string .= (string) $filter;
        }
        return $string;
    }
}