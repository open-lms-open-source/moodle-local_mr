<?php
/**
 * Filter Multiple Select
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/model/filter/abstract.php');

class block_reports_model_filter_selectmulti extends block_reports_model_filter_abstract {
    /**
     * Select options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Defaults
     *
     * @var array
     */
    protected $defaults = array();

    /**
     * Adding an options param for the select options
     */
    public function __construct($name, $label, $options, $defaults = array(), $advanced = false, $field = NULL) {
        parent::__construct($name, $label, $advanced, $field);
        $this->options  = $options;
        $this->defaults = $defaults;
    }

    /**
     * First option is default
     */
    public function preferences_defaults() {
        return array($this->name => implode(',', $this->defaults));
    }

    /**
     * Enforce checkboxes - if not set
     * then set date to 0
     */
    public function preferences_update($data) {
        $raw = optional_param($this->name, '', PARAM_RAW);
        if (!empty($raw) and !empty($data->{$this->name})) {
            $data->{$this->name} = implode(',', $data->{$this->name});
        } else {
            $data->{$this->name} = '';
        }
        return parent::preferences_update($data);
    }

    /**
     * Add select input
     */
    public function add_element($mform) {
        $mform->addElement('select', $this->name, $this->label, $this->options)->setMultiple(true);

        if ($defaults = $this->preferences_get($this->name)) {
            $mform->setDefault($this->name, explode(',', $defaults));
        }
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
            $preference = explode(',', $preference);

            if (count($preference) == 1) {
                if (is_numeric($preference[0])) {
                    return $this->field.' = '.$preference[0];
                }
                return $this->field.' = '.$db->quote($preference[0]);
            } else {
                $values = array();
                foreach ($preference as $value) {
                    if (is_numeric($value)) {
                        $values[] = $value;
                    } else {
                        $values[] = $db->quote($value);
                    }
                }
                return $this->field.' IN ('.implode(',', $values).')';
            }
        }
        return false;
    }
}