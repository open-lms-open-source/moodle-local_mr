<?php
/**
 * Multi-select plus filter
 *
 * @author Sam Chaffee
 * @package local/mr
 */

class mr_html_filter_selectmultiplus extends mr_html_filter_abstract {
    public function __construct($name, $label, $textlabel, $options = array(), $advanced = false, $field = NULL) {
        parent::__construct($name, $label, $advanced, $field);

        $this->options = $options;
        $this->textlabel = $textlabel;
    }

    /**
     * Add form elements to the form
     *
     * @param $mform - the moodleform for the filters
     * @return selectmultiplus
     */
    public function add_element($mform) {
        //add div and empty unordered list to the form
        $mform->addElement('static', $this->name . '_addedlist', $this->label, '<div id="id_' . $this->name . '_addedlist" class="selectmultiplus addedlist"></div>');

        // Add the select element setting multiple
        $mform->addElement('select', $this->name, $this->label, $this->options, 'class="selectmultiplus"')->setMultiple(true);

        // set the defaults
        if ($defaults = $this->preferences_get($this->name)) {
            $mform->setDefault($this->name, explode(',', $defaults));
        }

        if ($this->advanced) {
            $mform->setAdvanced($this->name);
        }

        // add the input field for autocomplete
        $mform->addElement('text', $this->name . '_autocomplete', $this->textlabel, 'class="selectmultiplus"');

        // initialize the javascript
        $helper = new mr_helper();
        $helper->html->filter_selectmultiplus_init($this->name);

        return $this;
    }

    /**
     * @param $data
     * @return mr_html_filter_abstract
     */
    public function preferences_update($data) {
        $raw = optional_param_array($this->name, array(), PARAM_RAW);
        if (!empty($raw) and !empty($data->{$this->name})) {
            $data->{$this->name} = implode(',', $data->{$this->name});
        } else {
            $data->{$this->name} = '';
        }
        return parent::preferences_update($data);
    }

    /**
     * @return array|bool
     */
    public function sql() {
        global $DB;

        $preference = $this->preferences_get($this->name);
        if (!empty($preference)) {
            list($sql, $params) = $DB->get_in_or_equal(explode(',', $preference));
            $sql = $this->field . ' ' . $sql;
            return array($sql, $params);
        }
        return false;
    }
}