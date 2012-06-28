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
 */

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * @see mr_html_filter_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/html/filter/abstract.php');

/**
 * MR HTML Filter Checkbox
 *
 * @author Mark Nielsen
 * @author Sam Chaffee
 * @package mr
 */
class mr_html_filter_checkbox extends mr_html_filter_abstract {
    /**
     * Default state, checked/unchecked (0, 1)
     *
     * @var int
     */
    protected $default;

    /**
     * Right label
     *
     * @var string
     */
    protected $rightlabel;

    /**
     * SQL for when the checkbox is unchecked
     *
     * @var string
     */
    protected $checkedsql;

    /**
     * SQL for when the checkbox is checked
     *
     * @var string
     */
    protected $uncheckedsql;

    /**
     * Checkbox filter constructor
     *
     * @param string $name Name for the filter instance
     * @param string $label Label to the left of the checkbox
     * @param string $rightlabel Label to the right of the checkbox
     * @param int $default The default state of the checkbox (0, 1)
     * @param array $checkedsql SQL to use when checked
     * @param array $uncheckedsql SQL to use when not checked
     * @param bool $advanced Whether or not the form element should be an advanced option
     * @param string $field The field to be used in the filter
     */
    public function __construct($name, $label, $rightlabel = '', $default = 0, $checkedsql = array(), $uncheckedsql = array(), $advanced = false, $field = NULL) {
        parent::__construct($name, $label, $advanced, $field);

        $this->rightlabel   = $rightlabel;
        $this->default      = $default;
        $this->checkedsql   = $checkedsql;
        $this->uncheckedsql = $uncheckedsql;
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
     * Add checkbox
     */
    public function add_element($mform) {
        $mform->addElement('advcheckbox', $this->name, $this->label, $this->rightlabel, array('group' => 1), array(0, 1));
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
        $preference = $this->preferences_get($this->name);
        if (!empty($preference)) {
            return $this->checkedsql;
        } else {
            return $this->uncheckedsql;
        }
    }
}