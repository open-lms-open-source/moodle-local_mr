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
 * MR Config Storage Default
 *
 * Stores the config using the set_config(), get_config()
 * and unset_config() methods.
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_config_storage_default implements mr_config_storage_interface {
    /**
     * Moodle component
     *
     * @var string
     */
    protected $component;

    /**
     * Local cache for reading the configs
     *
     * @var null|stdClass
     */
    protected $cache = null;

    /**
     * @param string $component Moodle component
     */
    public function __construct($component) {
        $this->component = $component;
    }

    /**
     * @return null|stdClass
     */
    public function get_cache() {
        return $this->cache;
    }

    /**
     * Loads the cache if not already
     *
     * @return stdClass The cache
     */
    public function load_cache() {
        if (is_null($this->cache)) {
            $config = get_config($this->component);

            if (empty($config)) {
                $config = new stdClass;
            }
            $this->cache = $config;
        }
        return $this->cache;
    }

    /**
     * Clears cache
     */
    public function clear_cache() {
        $this->cache = null;
    }

    public function read(mr_config_interface $config) {
        $cache = $this->load_cache();
        if (property_exists($cache, $config->get_name())) {
            $value   = $cache->{$config->get_name()};
            $default = $config->get_default();

            if (is_array($default) or $default instanceof Serializable) {
                $value = unserialize($value);
            }
            $config->set_value($value);
        }
    }

    public function write(mr_config_interface $config) {
        $value   = $config->get_value();
        $default = $config->get_default();

        if (is_array($default) or $default instanceof Serializable) {
            $value = serialize($value);
        }
        set_config($config->get_name(), $value, $this->component);

        // Invalidate our local cache
        $this->clear_cache();
    }

    public function remove(mr_config_interface $config) {
        $name = $config->get_name();
        unset_config($name, $this->component);

        if (!is_null($this->cache) and property_exists($this->cache, $name)) {
            unset($this->cache->{$name});
        }
    }
}