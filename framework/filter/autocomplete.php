<?php
/**
 * Filter Autocomplete
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/model/filter/abstract.php');

class block_reports_model_filter_autocomplete extends block_reports_model_filter_abstract {

    /**
     * Autocomplete options
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
     * Add text input
     */
    public function add_element($mform) {
        global $CFG;

        require_once($CFG->libdir.'/mr/autoload.php');

        $helper = new mr_helper('blocks/reports');

        $mform->addElement('text', $this->name, $this->label);
        $mform->setType($this->name, PARAM_TEXT);
        $mform->setDefault($this->name, $this->preferences_get($this->name));

        $helper->html->mform_autocomplete($mform, $this->options, $this->name);

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