<?php
/**
 * Header filter - provides a way to add a new header element to the
 * MoodleQuickForm in order to seperate filters
 *
 * @author Sam Chaffee
 * @version $Id$
 * @package blocks/reports
 **/

class block_reports_model_filter_header extends block_reports_model_filter_abstract {

    public function __construct($name, $label) {
        parent::__construct($name, $label, false, '');
    }

    public function add_element($mform) {
        $mform->addElement('header', $this->name, $this->label);

        return $this;
    }

    public function sql() {}
}

?>
