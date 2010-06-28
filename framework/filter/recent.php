<?php
/**
 * Filter Recent
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/model/filter/abstract.php');

class block_reports_model_filter_recent extends block_reports_model_filter_abstract {

    /**
     * Default is zero
     */
    public function preferences_defaults() {
        return array($this->name => 0);
    }

    /**
     * Add select input
     */
    public function add_element($mform) {
        $options = array(
            0          => get_string('nolimit', 'block_reports'),
            '-1 day'   => get_string('oneday', 'block_reports'),
            '-1 week'  => get_string('oneweek', 'block_reports'),
            '-1 month' => get_string('onemonth', 'block_reports'),
            '-2 month' => get_string('xmonths', 'block_reports', 2),
            '-3 month' => get_string('xmonths', 'block_reports', 3),
            '-6 month' => get_string('xmonths', 'block_reports', 6),
            '-1 year'  => get_string('oneyear', 'block_reports')
        );

        $mform->addElement('select', $this->name, $this->label, $options);
        $mform->setType($this->name, PARAM_TEXT);
        $mform->setDefault($this->name, $this->preferences_get($this->name));

        if ($this->advanced) {
            $mform->setAdvanced($this->name);
        }
        return $this;
    }

    /**
     * Limit a field to being greater than our recent time
     */
    public function sql() {
        $preference = $this->preferences_get($this->name);

        if (!empty($preference) and ($time = strtotime($preference)) !== false) {
            return "$this->field >= $time";
        }
        return false;
    }
}