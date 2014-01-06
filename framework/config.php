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

/**
 * MR Config
 *
 * This stores information about a configuration
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_config implements mr_config_interface {
    /**
     * Default value
     *
     * If the default value is an array or implements
     * Serializable interface, then the config value
     * is serialized when written to storage.
     *
     * @var mixed
     */
    protected $default;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * The configuration name
     *
     * @var string
     */
    protected $name;

    /**
     * @param string $name The configuration name
     * @param mixed $default The default configuration value
     */
    public function __construct($name, $default) {
        $this->name = $name;
        $this->default = $default;

        $this->set_value($default);
    }

    public function get_default() {
        return $this->default;
    }

    public function set_value($value) {
        $this->value = $value;
        return $this;
    }

    public function get_value() {
        return $this->value;
    }

    public function get_name() {
        return $this->name;
    }
}