<?php
/**
 * Moodlerooms Framework
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @copyright Copyright (c) 2009 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @package mr
 * @author Mark Nielsen
 * @author Sam Chaffee
 */

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * Multi-select plus filter
 *
 * @author Sam Chaffee
 * @package mr
 */
class mr_html_filter_selectmultiplus extends mr_html_filter_abstract {
    public function __construct($name, $label, $options = array(), $advanced = false, $field = NULL) {
        parent::__construct($name, $label, $advanced, $field);

        $this->options = $options;
    }

    /**
     * Add filter form element
     *
     * @param MoodleQuickForm $mform Filter form
     * @return mr_html_filter_abstract
     */
    public function add_element($mform) {
        //add div and empty unordered list to the form
        $ieshim = '<div class="selectmultiplus-ieshim">.</div>';
        $mform->addElement('static', $this->name . '_addedlist', $ieshim, '<div id="id_' . $this->name . '_addedlist" class="selectmultiplus"></div>');

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
        $mform->addElement('text', $this->name . '_autocomplete', $this->label, 'class="selectmultiplus"');
        $mform->setType($this->name.'_autocomplete', PARAM_TEXT);

        // initialize the javascript
        $helper = new mr_helper();
        $helper->html->filter_selectmultiplus_init($this->name);

        return $this;
    }

    /**
     * Add all elements for this filter
     *
     * @param MoodleQuickForm $mform The filter form
     * @return mr_html_filter_abstract
     */
    public function add_elements($mform) {
        $this->add_element($mform);

        // Add help buttons
        if (!empty($this->helpbutton) && is_array($this->helpbutton)) {
            // selector element and autocomplete element
            if (!empty($this->helpbutton) && !empty($this->helpbutton['identifier']) && !empty($this->helpbutton['component'])) {
                $mform->addHelpButton(
                    $this->name,
                    $this->helpbutton['identifier'],
                    $this->helpbutton['component']
                );

                $mform->addHelpButton(
                    $this->name . '_autocomplete',
                    $this->helpbutton['identifier'],
                    $this->helpbutton['component']
                );
            }
        }

        // Add disabledIf
        if (!empty($this->disabledif)) {
            $mform->disabledIf(
                $this->get_element_name(),
                $this->disabledif['dependenton'],
                $this->disabledif['condition'],
                $this->disabledif['value']
            );
        }
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