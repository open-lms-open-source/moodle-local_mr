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
 * MR HTML Filter
 *
 * This controls the setup, interaction and usage
 * of a moodleform class and mr_html_filter_*
 * classes.
 *
 * @author Mark Nielsen
 * @package mr
 * @example controller/filter.php See how to use this class
 */
class mr_html_filter extends mr_readonly implements renderable {
    /**
     * Added filters
     *
     * @var mr_html_filter_abstract[]
     */
    protected $filters = array();

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
     * Report
     *
     * @var mr_report_abstract
     */
    protected $report;

    /**
     * Construct
     *
     * @param mr_preferences $preferences Preferences model
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
        if (empty($this->filters)) {
            throw new coding_exception('Must add filters');
        }
        if (empty($this->mform)) {
            $this->mform = $this->helper->load($this->formpath, array($this->url, $this), false);

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
     * Return filter SQL and params
     *
     * @return array
     */
    public function sql() {
        $this->init();

        $sqlands = array();
        $params  = array();
        foreach ($this->filters as $filter) {
            $field = $filter->get_field();
            if (!empty($field) and ($sql = $filter->sql())) {
                $sqlands[] = $sql[0];

                if (is_array($sql[1])) {
                    $params = array_merge($params, $sql[1]);
                } else {
                    $params[] = $sql[1];
                }
            }
        }
        if (!empty($sqlands)) {
            return array(implode(' AND ', $sqlands), $params);
        }
        return array('1 = 1', array());
    }

    /**
     * Add a filter
     *
     * @param mr_html_filter_abstract $filter A filter instance
     * @return mr_html_filter
     */
    public function add(mr_html_filter_abstract $filter) {
        $filter->preferences_init($this->preferences);
        $this->filters[$filter->get_name()] = $filter;
        return $this;
    }

    /**
     * Add a help button to a filter
     *
     * @param string $filtername The filter's name
     * @param string $identifier Help button text identifier
     * @param string $component The plugin component
     * @return mr_html_filter
     * @throws coding_exception
     */
    public function add_helpbutton($filtername, $identifier, $component = 'moodle') {
        if (!array_key_exists($filtername, $this->filters)) {
            throw new coding_exception("Cannot add filter help button because filter '$filtername' does not exist");
        }
        $this->filters[$filtername]->add_helpbutton($identifier, $component);
        return $this;
    }

    /**
     * Add disabledIf to a filter
     *
     * @param string $filtername The filter's name
     * @param string $dependenton The name of the element whose state will be checked for condition
     * @param string $condition The condition to check
     * @param string $value Used in conjunction with condition.
     * @return mr_html_filter
     * @throws coding_exception
     */
    public function add_disabledif($filtername, $dependenton, $condition = 'notchecked', $value = '1') {
        if (!array_key_exists($filtername, $this->filters)) {
            throw new coding_exception("Cannot add filter disabled if because filter '$filtername' does not exist");
        }
        $this->filters[$filtername]->add_disabledif($dependenton, $condition, $value);
        return $this;
    }

    /**
     * Hook from
     * @param MoodleQuickForm $mform
     * @return mr_html_filter
     */
    public function mform_hook(MoodleQuickForm &$mform) {
        if ($this->report instanceof mr_report_abstract) {
            $this->report->filter_definition_hook($mform);
        }
        return $this;
    }

    /**
     * Set a report to the filters
     * @param mr_report_abstract $report
     */
    public function set_report(mr_report_abstract $report) {
        $this->report = $report;
    }

    /**
     * Use this to add new filters to the filter model
     *
     * Example:
     *    ->new_text(...args...);
     *
     * @param string $name The name of the filter
     * @param array $arguments Filter args
     * @return mixed|mr_html_filter
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
        return parent::__call($name, $arguments);
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