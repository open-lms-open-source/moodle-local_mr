<?php
/**
 * Filter Autocomplete with IDs
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/model/filter/abstract.php');

class block_reports_model_filter_autocompleteid extends block_reports_model_filter_abstract {

    /**
     * Autocomplete options
     *
     * Options must be: array(recordID => 'display text')
     *
     * @var array
     */
    protected $options = array();

    /**
     * Adding an options param for the select options
     *
     * Options must be: array(recordID => 'display text')
     */
    public function __construct($name, $label, $options, $advanced = false, $field = NULL) {
        parent::__construct($name, $label, $advanced, $field);
        $this->options = $options;
    }

    /**
     * Defaults for two fields
     */
    public function preferences_defaults() {
        return array($this->name => 0, $this->name.'_autocompletetext' => '');
    }

    /**
     * Add text input for autocomplete and hidden field to store ID
     */
    public function add_element($mform) {
        global $CFG;

        require_once($CFG->libdir.'/mr/autoload.php');

        $helper = new mr_helper('blocks/reports');

        $textfieldname = "{$this->name}_autocompletetext";

        // Attempt to load relavent display text
        $text = $this->preferences_get($textfieldname);
        $key  = $this->preferences_get($this->name);
        if (!empty($key) and isset($this->options[$key])) {
            $text = $this->options[$key];
        }

        $mform->addElement('text', $textfieldname, $this->label);
        $mform->setType($textfieldname, PARAM_TEXT);
        $mform->setDefault($textfieldname, $text);

        $mform->addElement('hidden', $this->name, $this->preferences_get($this->name));
        $mform->setType('name', PARAM_INT);

        $helper->html->mform_autocomplete($mform, $this->options, $textfieldname, $this->name);

        if ($this->advanced) {
            $mform->setAdvanced($textfieldname);
        }

        return $this;
    }

    /**
     * Restrict to value
     */
    public function sql() {
        $preference = $this->preferences_get($this->name);
        if (!empty($preference)) {
            if (is_numeric($preference)) {
                return "$this->field = $preference";
            }
            return $this->field.' = \''.addslashes($preference).'\'';
        }
        return false;
    }
}