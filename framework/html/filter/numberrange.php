<?php
/**
 * Filter Number range
 *
 * @author Sam Chaffee
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/model/filter/abstract.php');

class block_reports_model_filter_numberrange extends block_reports_model_filter_abstract {

    /**
     * The low value default
     * 
     * @var mixed
     */
    protected $lv_default;

    /**
     * The high value default
     * 
     * @var mixed
     */
    protected $hv_default;

    /**
     * Construct
     *
     * @param string $name Filter name
     * @param string $label Filter label
     * @param string $advanced Filter advanced form setting
     * @param string $field SQL field, defaults to $name
     * @param int $lv_default Low value default
     * @param int $hv_default High value default
     */
    public function __construct($name, $label, $advanced = false, $field = NULL, $lv_default = 0, $hv_default = 0) {
        parent::__construct($name, $label, $advanced, $field);

        $this->lv_default = $lv_default;
        $this->hv_default = $hv_default;
    }

    /**
     * Defaults for two fields
     */
    public function preferences_defaults() {
        return array($this->name.'_lv' => $this->lv_default, $this->name.'_hv' => $this->hv_default);
    }

    /**
     * Enforce checkboxes - if not set
     * then set date to 0
     */
    public function preferences_update($data) {
        $name = "{$this->name}_lc";
        if (empty($data->$name)) {
            $name = "{$this->name}_lv";
            $data->$name = 0;
        }
        $name = "{$this->name}_hc";
        if (empty($data->$name)) {
            $name = "{$this->name}_hv";
            $data->$name = 0;
        }
        return parent::preferences_update($data);
    }

    /**
     * Add text fields and checkboxes to enable/disable
     */
    public function add_element($mform) {
        // Naming
        //  l = low
        //  c = checkbox
        //  v = textbox
        //  h = high

        $attributes = array('size' => 5);

        $group[] =& $mform->createElement('checkbox', $this->name.'_lc', null, get_string('greaterthanoreq', 'block_reports'));
        $group[] =& $mform->createElement('text', $this->name.'_lv', null, $attributes);
        $group[] =& $mform->createElement('checkbox', $this->name.'_hc', null, get_string('lessthanoreq', 'block_reports'));
        $group[] =& $mform->createElement('text', $this->name.'_hv', null, $attributes);
        $mform->addElement('group', $this->name.'_grp', $this->label, $group, '', false);

        if ($this->advanced) {
            $mform->setAdvanced($this->name.'_grp');
        }

        // Handle defaults
        $mform->setDefault($this->name.'_lv', $this->preferences_get($this->name.'_lv'));
        $mform->setDefault($this->name.'_hv', $this->preferences_get($this->name.'_hv'));
        $mform->setDefault($this->name.'_lc', (int) $this->preferences_get($this->name.'_lv'));
        $mform->setDefault($this->name.'_hc', (int) $this->preferences_get($this->name.'_hv'));

        // Set the type
        $mform->setType($this->name.'_lv', PARAM_NUMBER);
        $mform->setType($this->name.'_hv', PARAM_NUMBER);

        // Disable text fields when checkbox is not checked
        $mform->disabledIf($this->name.'_lv', $this->name.'_lc', 'notchecked');
        $mform->disabledIf($this->name.'_hv', $this->name.'_hc', 'notchecked');

        return $this;
    }

    /**
     * Set limits on field
     */
    public function sql() {
        $sql = array();

        $preference = $this->preferences_get($this->name.'_lv');
        if (!empty($preference)) {
            $sql[] = "$this->field >= $preference";
        }
        $preference = $this->preferences_get($this->name.'_hv');
        if (!empty($preference)) {
            // Note, we may want "$this->field > 0 AND " added to the following
            $sql[] = "$this->field <= $preference";
        }

        if (!empty($sql)) {
            return implode(' AND ', $sql);
        }
        return false;
    }

    /**
     * Hook into MoodleQuickForm setHelpButton method
     *
     * @param MoodleQuickForm $mform
     * @return block_reports_model_filter_abstract
     */
    public function set_helpbutton($mform) {
        if (isset($this->helpbutton) and is_array($this->helpbutton)) {
            $mform->setHelpButton($this->name . '_grp', $this->helpbutton);
        }
        return $this;
    }
}

?>
