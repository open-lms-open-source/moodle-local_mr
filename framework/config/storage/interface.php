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
 * MR Configuration Storage Interface
 *
 * Provide a way to read, write and remove
 * configuration values.
 *
 * @author Mark Nielsen
 * @package mr
 */
interface mr_config_storage_interface {
    /**
     * Get the configuration value for the passed
     * configuration and set the value to the passed
     * configuration if found
     *
     * If the default config value is an array or implements
     * Serializable interface, then the config value
     * can be unserialized when read from storage.
     *
     * @param mr_config_interface $config
     * @return void
     */
    public function read(mr_config_interface $config);

    /**
     * Write the value from the passed configuration
     *
     * If the default config value is an array or implements
     * Serializable interface, then the config value
     * can be serialized when written to storage.
     *
     * @param mr_config_interface $config
     * @return void
     */
    public function write(mr_config_interface $config);

    /**
     * Delete the configuration
     *
     * @param mr_config_interface $config
     * @return mixed
     */
    public function remove(mr_config_interface $config);
}
