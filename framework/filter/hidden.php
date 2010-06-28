<?php
/**
 * Filter hidden
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/model/filter/abstract.php');

class block_reports_model_filter_hidden extends block_reports_model_filter_abstract {
    /**
     * Value of the hidden field
     *
     * @var mixed
     */
    protected $_value;

    /**
     * Construct
     *
     * @param string $name Filter name
     * @param mixed $value Filter value
     * @param string $field SQL field, defaults to $name
     */
    public function __construct($name, $value, $field = NULL) {
        $this->_value = $value;

        parent::__construct($name, '', false, $field);
    }

    /**
     * Defaults to value
     */
    public function preferences_defaults() {
        return array($this->name, $this->value);
    }

    /**
     * Add hidden field
     */
    public function add_element($mform) {
        $mform->addElement('hidden', $this->name, $this->_value);

        if (is_numeric($this->_value)) {
            $mform->setType('name', PARAM_INT);
        } else {
            $mform->setType('name', PARAM_TEXT);
        }

        return $this;
    }

    /**
     * Set field to value
     */
    public function sql() {
        global $db;

        if (is_numeric($this->_value)) {
            return "$this->field = $this->_value";
        }
        return "$this->field = ".$db->quote($this->_value);
    }
}