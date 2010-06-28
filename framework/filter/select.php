<?php
/**
 * Filter Select
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/model/filter/abstract.php');

class block_reports_model_filter_select extends block_reports_model_filter_abstract {
    /**
     * Select options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Adding an options param for the select options
     */
    public function __construct($name, $label, $options, $advanced = false, $field = NULL) {
        parent::__construct($name, $label, $advanced, $field);
        $this->options = $options;
    }

    /**
     * First option is default
     */
    public function preferences_defaults() {
        foreach ($this->options as $value => $option) {
            return array($this->name => $value);
        }
    }

    /**
     * Add select input
     */
    public function add_element($mform) {
        $mform->addElement('select', $this->name, $this->label, $this->options);
        $mform->setDefault($this->name, $this->preferences_get($this->name));

        if ($this->advanced) {
            $mform->setAdvanced($this->name);
        }

        return $this;
    }

    /**
     * Limit by input value
     */
    public function sql() {
        global $db;

        $preference = $this->preferences_get($this->name);
        if (!empty($preference)) {
            if (is_numeric($preference)) {
                return "$this->field = $preference";
            }
            return $this->field.' = '.$db->quote($preference);
        }
        return false;
    }
}