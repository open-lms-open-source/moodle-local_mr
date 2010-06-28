<?php
/**
 * Filter Model: This controls the setup, interaction and usage
 * of block_reports_form_filter and block_reports_model_filter_*
 * classes.
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->libdir.'/mr/bootstrap.php');
require_once($CFG->dirroot.'/blocks/reports/exception.php');

class block_reports_model_filter {
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
     * @var block_reports_model_preferences
     */
    protected $preferences;

    /**
     * Base URL
     *
     * @var moodle_url
     */
    protected $url;

    /**
     * Filter form
     *
     * @var block_reports_form_filter
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
     * @param block_reports_model_preferences Preferences model
     * @param moodle_url $url Base URL
     */
    public function __construct($preferences, $url) {
        $this->url         = $url;
        $this->helper      = new mr_helper('blocks/reports');
        $this->preferences = $preferences;
    }

    /**
     * After filters have been added, you can
     * initialze the form and handle submitted data
     *
     * @return block_reports_model_filter
     */
    public function init() {
        global $CFG;

        if (empty($this->filters)) {
            throw new block_reports_exception('Must add filters');
        }
        if (empty($this->mform)) {
            require_once($CFG->dirroot.'/blocks/reports/form/filter.php');

            $this->mform = new block_reports_form_filter($this->preferences->get_plugin(), $this->url, $this->filters);

            if ($data = $this->mform->get_data()) {
                if (stripslashes($data->submitbutton) == get_string('reset', 'block_reports')) {
                    foreach ($this->filters as $filter) {
                        $filter->preferences_delete();
                    }
                } else {
                    foreach ($this->filters as $filter) {
                        $filter->preferences_update($data);
                    }
                }
                $params = array();
                if ($data->chart != 'none') {
                    $params['chart'] = $data->chart;
                }
                redirect($this->url->out(false, $params));
            }
        }
        return $this;
    }

    /**
     * Return filter SQL
     *
     * @return string
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
     * SQL from the block_reports_model_filter::sql() method.
     *
     * @param string $param Keep passing filter names to exclude
     * @return block_reports_model_filter
     */
    public function exclude_sql() {
        $args = func_get_args();
        foreach ($args as $arg) {
            if (!is_string($arg)) {
                throw new block_reports_exception('Can only pass strings to exclude_sql()');
            }
            $this->excludesql[$arg] = $arg;
        }
        return $this;
    }

    /**
     * Display the form
     *
     * @return void
     */
    public function display() {
        foreach ($this->filters as $filter) {
            if (!($filter instanceof block_reports_model_filter_hidden)) {
                $this->init();
                $this->mform->display();
                break;
            }
        }
    }

    /**
     * Use this to add new filters to the filter model
     *
     * Example:
     *    ->new_text(...args...);
     *
     * @param string $name The name of the filter
     * @param array $arguments Filter args
     * @return block_reports_model_filter
     */
    public function __call($name, $arguments) {
        $parts = explode('_', $name);

        if (count($parts) == 2) {
            switch ($parts[0]) {
                case 'new':
                    $filter = $this->helper->load('model/filter/'.$parts[1], $arguments);
                    $filter->preferences_init($this->preferences);

                    $this->filters[] = $filter;
                    return $this;
                    break;
            }
        }
        throw new block_reports_exception('Invalid call to block_reports_model_filter');
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