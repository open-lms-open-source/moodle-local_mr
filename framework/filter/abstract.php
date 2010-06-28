<?php
/**
 * Filter Abstraction
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

abstract class block_reports_model_filter_abstract {
    /**
     * The filter's name
     *
     * @var string
     */
    protected $name;

    /**
     * Filter label in the form
     *
     * @var string
     */
    protected $label;

    /**
     * If it should be flagged as
     * advanced or not in the form
     *
     * @var boolean
     */
    protected $advanced;

    /**
     * SQL field name (if different from $name)
     *
     * @var string
     */
    protected $field;

    /**
     * User preferences (filter values are stored here)
     *
     * @var block_reports_model_preferences
     */
    protected $preferences;

    /**
     * Construct
     *
     * @param string $name Filter name
     * @param string $label Filter label
     * @param string $advanced Filter advanced form setting
     * @param string $field SQL field, defaults to $name
     */
    public function __construct($name, $label, $advanced = false, $field = NULL) {
        $this->name     = $name;
        $this->label    = $label;
        $this->advanced = $advanced;

        if (is_null($field)) {
            $this->field = $this->name;
        } else {
            $this->field = $field;
        }
    }

    /**
     * Convert this filter into a simple string
     *
     * @return string
     */
    public function __toString() {
        $string = '';
        foreach ($this->preferences_defaults() as $name => $default) {
            $string .= "$name".$this->preferences_get($name);
        }
        return $string;
    }

    /**
     * Get the field's name
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Get SQL field name
     *
     * @return string
     */
    public function get_field() {
        return $this->field;
    }

    /**
     * Filter defaults
     *
     * @return array
     */
    public function preferences_defaults() {
        return array($this->name => '');
    }

    /**
     * Preferences init
     *
     * @param string $prefix Unique prefix
     * @return block_reports_model_filter_abstract
     */
    public function preferences_init($preferences) {
        $this->preferences = $preferences;

        return $this;
    }

    /**
     * Get a preference value (default can be returned)
     *
     * @param string $name Preference name
     * @return mixed
     */
    public function preferences_get($name) {
        $defaults = $this->preferences_defaults();

        return $this->preferences->get($name, $defaults[$name]);
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
     * Remove preferences for this filter
     *
     * @param string $name A specific preference to delete, if NULL all are deleted
     * @return block_reports_model_filter_abstract
     */
    public function preferences_delete($name = NULL) {
        if (is_null($name)) {
            $names = array_keys($this->preferences_defaults());
        } else {
            $names = array($name);
        }
        foreach ($names as $name) {
            $this->preferences->delete($name);
        }
        return $this;
    }

    /**
     * Add filter form element
     *
     * @param MoodleQuickForm $mform Filter form
     * @return block_reports_model_filter_abstract
     */
    abstract public function add_element($mform);

    /**
     * Generate this filter's SQL
     *
     * @return mixed
     */
    abstract public function sql();
}