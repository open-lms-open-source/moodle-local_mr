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
 * MR HTML Notify
 *
 * This model is used to add messages to the session
 * which can then be printed on subsequent page loads.
 *
 * Use case: submit data to be saved, set a message with
 * mr_html_notify like "Changes Saved" and then redirect to the
 * orignal screen and display the message.
 *
 * This class is tightly integrated with mr_controller.
 *
 * @author Mark Nielsen
 * @package mr
 * @see mr_controller
 */
class mr_html_notify implements renderable {
    /**
     * Message that is bad
     */
    const BAD = 'notifyproblem';

    /**
     * Message that is good
     */
    const GOOD = 'notifysuccess';

    /**
     * Get string component
     *
     * @var string
     */
    protected $component = '';

    /**
     * Message alignment
     *
     * @var string
     */
    protected $align  = 'center';

    /**
     * Constructor
     *
     * Override default component and align.
     *
     * @param string $component Default get string component
     * @param string $align Default alignment
     */
    public function __construct($component = '', $align = 'center') {
        $this->set_component($component)
             ->set_align($align);
    }

    /**
     * Set component string
     *
     * @param string $component Get string component
     * @return mr_html_notify
     */
    public function set_component($component) {
        $this->component = $component;
        return $this;
    }

    /**
     * Set alignment
     *
     * @param string $align Alignment
     * @return mr_html_notify
     */
    public function set_align($align) {
        $this->align = $align;
        return $this;
    }

    /**
     * Add a good message
     *
     * @param string $identifier The string identifier to use in get_string()
     * @param mixed $a To be passed in the call to get_string()
     * @param string $component Get string component
     * @param string $align Alignment of the message
     * @return mr_html_notify
     */
    public function good($identifier, $a = NULL, $component = NULL, $align = NULL) {
        return $this->add($identifier, self::GOOD, $a, $component, $align);
    }

    /**
     * Add a bad message
     *
     * @param string $identifier The string identifier to use in get_string()
     * @param mixed $a To be passed in the call to get_string()
     * @param string $component Get string component
     * @param string $align Alignment of the message
     * @return mr_html_notify
     */
    public function bad($identifier, $a = NULL, $component = NULL, $align = NULL) {
        return $this->add($identifier, self::BAD, $a, $component, $align);
    }

    /**
     * Adds a message to be printed.  Messages are printed
     * by calling {@link print()}.
     *
     * @uses $SESSION
     * @param string $identifier The string identifier to use in get_string()
     * @param string $class Class to be passed to notify().  Usually notifyproblem or notifysuccess.
     * @param mixed $a To be passed in the call to get_string()
     * @param string $component Get string component
     * @param string $align Alignment of the message
     * @example controller/default.php See this being used in a mr_controller
     * @return mr_html_notify
     * @see BAD, GOOD
     */
    public function add($identifier, $class = self::BAD, $a = NULL, $component = NULL, $align = NULL) {
        if (is_null($component)) {
            $component = $this->component;
        }
        return $this->add_string(get_string($identifier, $component, $a), $class, $align);
    }

    /**
     * Add a string to be printed
     *
     * @param string $string The string to be printed
     * @param string $class The class to be passed to notify().  Usually notifyproblem or notifysuccess.
     * @param string $align Alignment of the message
     * @return mr_html_notify
     * @see BAD, GOOD
     */
    public function add_string($string, $class = self::BAD, $align = NULL) {
        global $SESSION;

        if (empty($SESSION->messages) or !is_array($SESSION->messages)) {
            $SESSION->messages = array();
        }
        if (is_null($align)) {
            $align = $this->align;
        }
        $SESSION->messages[] = array($string, $class, $align);

        return $this;
    }

    /**
     * Get all messages added to the session.
     *
     * @uses $SESSION
     * @param boolean $clear Remove messages from the session
     * @return string
     */
    public function get_messages($clear = true) {
        global $SESSION;

        $messages = array();
        if (!empty($SESSION->messages)) {
            $messages = $SESSION->messages;
        }
        if ($clear) {
            unset($SESSION->messages);
        }
        return $messages;
    }
}