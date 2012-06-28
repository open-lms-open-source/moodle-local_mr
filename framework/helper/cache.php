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
 * @see mr_helper_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/helper/abstract.php');

/**
 * @see mr_cache
 */
require_once($CFG->dirroot.'/local/mr/framework/cache.php');

/**
 * MR Helper Cache
 *
 * This helper is used for quick and easy access
 * to mr_cache.  Automatic setup of mr_cache and
 * access to all of mr_cache methods.
 *
 * @author Mark Nielsen
 * @package mr
 * @example controller/cache.php See this in action
 */
class mr_helper_cache extends mr_helper_abstract {
    /**
     * Cache model
     *
     * @var mr_cache
     */
    protected $cache = NULL;

    /**
     * Return mr_cache based on the current namespace
     *
     * @return mr_cache
     */
    protected function _cache() {
        if (is_null($this->cache)) {
            $this->cache = new mr_cache("{$this->_helper_namespace}_");
        }
        return $this->cache;
    }

    /**
     * Save data to the cache (This is the same as mr_cache method save)
     *
     * @param string $data The data to be saved, currently ONLY strings accepted
     * @param string $id Cache id (if not set, the last cache id will be used)
     * @return boolean
     * @throws coding_exception
     */
    public function direct($data, $id = null) {
        return $this->_cache()->save($data, $id);
    }

    /**
     * Direct all other calls to mr_cache
     *
     * @param string $name A method in mr_cache
     * @param array $arguments The args to pass to the mr_cache method
     * @return mixed
     * @throws coding_exception
     */
    public function __call($name, $arguments) {
        if (!is_callable(array($this->_cache(), $name))) {
            throw new coding_exception("The method $name does not exist in mr_cache");
        }
        return call_user_func_array(array($this->_cache(), $name), $arguments);
    }
}