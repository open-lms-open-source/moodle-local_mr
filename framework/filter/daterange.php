<?php
/**
 * Filter Date range
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/model/filter/abstract.php');

class block_reports_model_filter_daterange extends block_reports_model_filter_abstract {

    /**
     * Defaults for two fields
     */
    public function preferences_defaults() {
        return array($this->name.'_sd' => 0, $this->name.'_ed' => 0);
    }

    /**
     * Enforce checkboxes - if not set
     * then set date to 0
     */
    public function preferences_update($data) {
        $name = "{$this->name}_sc";
        if (empty($data->$name)) {
            $name = "{$this->name}_sd";
            $data->$name = 0;
        }
        $name = "{$this->name}_ec";
        if (empty($data->$name)) {
            $name = "{$this->name}_ed";
            $data->$name = 0;
        }
        return parent::preferences_update($data);
    }

    /**
     * Add date fields and checkboxes to enable/disable
     */
    public function add_element($mform) {
        // Naming
        //  s = start
        //  c = checkbock
        //  d = date_selector
        //  e = end

        $group[] =& $mform->createElement('checkbox', $this->name.'_sc', null, get_string('isafter', 'filters'));
        $group[] =& $mform->createElement('date_selector', $this->name.'_sd', null);
        $group[] =& $mform->createElement('checkbox', $this->name.'_ec', null, get_string('isbefore', 'filters'));
        $group[] =& $mform->createElement('date_selector', $this->name.'_ed', null);
        $mform->addElement('group', $this->name.'_grp', $this->label, $group, '', false);

        if ($this->advanced) {
            $mform->setAdvanced($this->name.'_grp');
        }

        // Handle defaults
        $mform->setDefault($this->name.'_sd', $this->preferences_get($this->name.'_sd'));
        $mform->setDefault($this->name.'_ed', $this->preferences_get($this->name.'_ed'));
        $mform->setDefault($this->name.'_sc', (int) $this->preferences_get($this->name.'_sd'));
        $mform->setDefault($this->name.'_ec', (int) $this->preferences_get($this->name.'_ed'));

        // Disable date fields when checkbox is not checked
        $mform->disabledIf($this->name.'_sd[day]', $this->name.'_sc', 'notchecked');
        $mform->disabledIf($this->name.'_sd[month]', $this->name.'_sc', 'notchecked');
        $mform->disabledIf($this->name.'_sd[year]', $this->name.'_sc', 'notchecked');
        $mform->disabledIf($this->name.'_ed[day]', $this->name.'_ec', 'notchecked');
        $mform->disabledIf($this->name.'_ed[month]', $this->name.'_ec', 'notchecked');
        $mform->disabledIf($this->name.'_ed[year]', $this->name.'_ec', 'notchecked');

        return $this;
    }

    /**
     * Set limits on field
     */
    public function sql() {
        $sql = array();

        $preference = $this->preferences_get($this->name.'_sd');
        if (!empty($preference)) {
            $sql[] = "$this->field >= $preference";
        }
        $preference = $this->preferences_get($this->name.'_ed');
        if (!empty($preference)) {
            // Note, we may want "$this->field > 0 AND " added to the following
            $sql[] = "$this->field <= $preference";
        }

        if (!empty($sql)) {
            return implode(' AND ', $sql);
        }
        return false;
    }
}