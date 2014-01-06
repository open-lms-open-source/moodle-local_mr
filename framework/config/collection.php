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
 * MR Config Collection
 *
 * Easy way to manage configuration values (defaults,
 * storage, etc).
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_config_collection implements IteratorAggregate, Countable {
    /**
     * @var mr_config_storage_interface
     */
    protected $storage = null;

    /**
     * @var mr_config_interface[]
     */
    protected $configs = array();

    /**
     * Set a storage for reading, writing and removing
     * the configuration values
     *
     * @param mr_config_storage_interface $storage
     * @return mr_config_collection
     */
    public function set_storage(mr_config_storage_interface $storage = null) {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Add a configuration to be managed
     *
     * When added, the value for the configuration will
     * be read from the optional storage.
     *
     * @param mr_config_interface $config
     * @return mr_config_collection
     * @throws coding_exception
     */
    public function add(mr_config_interface $config) {
        if ($this->has($config->get_name())) {
            throw new coding_exception("Configuration already exists with name {$config->get_name()}");
        }
        $this->configs[$config->get_name()] = $config;

        if ($this->storage instanceof mr_config_storage_interface) {
            $this->storage->read($config);
        }
        return $this;
    }

    /**
     * All configs in this collection
     *
     * @return mr_config_interface[]
     */
    public function all() {
        return $this->configs;
    }

    /**
     * Get a configuration value
     *
     * @param string $name The name of the configuration
     * @return mixed
     * @throws coding_exception
     */
    public function get($name) {
        if ($this->has($name)) {
            return $this->configs[$name]->get_value();
        }
        throw new coding_exception("Attempting to access an unknown configruation: $name");
    }

    /**
     * Set the value for a configuration
     *
     * This is also written to the optional storage
     *
     * @param string $name The configuration name
     * @param mixed $value The configuration value
     * @return mr_config_collection
     * @throws coding_exception
     */
    public function set($name, $value) {
        if ($this->has($name)) {
            $this->configs[$name]->set_value($value);

            if ($this->storage instanceof mr_config_storage_interface) {
                $this->storage->write($this->configs[$name]);
            }
            return $this;
        }
        throw new coding_exception("Attempting to set an unknown configruation: $name");
    }

    /**
     * Remove a configuration
     *
     * This is also written to the optional storage
     *
     * @param string $name The configuration name
     * @return mr_config_collection
     */
    public function remove($name) {
        if ($this->has($name)) {
            if ($this->storage instanceof mr_config_storage_interface) {
                $this->storage->remove($this->configs[$name]);
            }
            unset($this->configs[$name]);
        }
        return $this;
    }

    /**
     * Determine if a configuration exists
     *
     * @param string $name The configuration name
     * @return bool
     */
    public function has($name) {
        return array_key_exists($name, $this->configs);
    }

    /**
     * For IteratorAggregate Interface
     *
     * @return ArrayIterator
     */
    public function getIterator() {
        return new ArrayIterator($this->configs);
    }

    /**
     * For Countable Interface
     *
     * Get the number of configs in this collection
     *
     * @return int
     */
    public function count() {
        return count($this->configs);
    }
}