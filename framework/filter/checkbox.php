<?php
/**
 * Filter Checkbox - uses formslib advanced checkbox
 *
 * @author Mark Nielsen
 * @author Sam Chaffee
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/model/filter/abstract.php');

class block_reports_model_filter_checkbox extends block_reports_model_filter_abstract {

    protected $default;

    protected $right_label;

    protected $checked_sql;

    protected $unchecked_sql;

    /**
     * Checkbox filter constructor
     *
     * @param string $name - name for the filter instance
     * @param string $label - label to the left of the checkbox
     * @param string $right_label - label to the right of the checkbox
     * @param int $default - the default state of the checkbox (0, 1)

     * @param bool $advanced - whether or not the form element should be an advanced option
     * @param string $field - the field to be used in the filter
     */
    public function __construct($name, $label, $right_label = '', $default = 0, $checked_sql = '', $unchecked_sql = '', $advanced = false, $field = NULL) {
        parent::__construct($name, $label, $advanced, $field);

        $this->right_label    = $right_label;
        $this->default        = $default;
        $this->checked_sql    = $checked_sql;
        $this->unchecked_sql  = $unchecked_sql;
        
    }

    /**
     * Filter defaults
     *
     * @return array
     */
    public function preferences_defaults() {
        return array($this->name => $this->default);
    }
    
    /**
     * Update user preferences to current filter settings
     *
     * @param object $data Form data
     * @return block_reports_model_filter_abstract
     */
    public function preferences_update($data) {
        foreach ($this->preferences_defaults() as $name => $default) {
            if (!isset($data->$name) or $data->$name == $default) {
                $this->preferences_delete($name);
            } else {
                $this->preferences->set($name, stripslashes($data->$name));
            }
        }
        return $this;
    }

    /**
     * Add checkbox
     */
    public function add_element($mform) {
        $mform->addElement('advcheckbox', $this->name, $this->label, $this->right_label, array('group' => 1), array(0, 1));
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
            return $this->checked_sql;
        } else {
            return $this->unchecked_sql;
        }
        return false;
    }
}