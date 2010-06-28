<?php
/**
 * Filter Text
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/model/filter/abstract.php');

class block_reports_model_filter_text extends block_reports_model_filter_abstract {

    /**
     * Add text input
     */
    public function add_element($mform) {
        $mform->addElement('text', $this->name, $this->label, array('style' => 'width: 300px'));
        $mform->setType($this->name, PARAM_TEXT);
        $mform->setDefault($this->name, $this->preferences_get($this->name));

        if ($this->advanced) {
            $mform->setAdvanced($this->name);
        }

        return $this;
    }

    /**
     * Search by input value
     */
    public function sql() {
        $preference = $this->preferences_get($this->name);
        if (!empty($preference)) {
            return $this->field.' '.sql_ilike().' \'%'.addslashes($preference).'%\'';
        }
        return false;
    }
}