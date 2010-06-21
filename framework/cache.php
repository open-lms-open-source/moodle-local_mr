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
 * @see mr_var
 */
require_once($CFG->dirroot.'/local/mr/framework/var.php');

/**
 * @see mr_zend
 */
require_once($CFG->dirroot.'/local/mr/framework/zend.php');

/**
 * MR Cache
 *
 * A basic caching class to cache data across
 * sessions and page requests.
 *
 * @package mr
 * @example controller/cache.php See the cache in action
 * @author Mark Nielsen
 */
class mr_cache {

    /**
     * Cache interface
     */
    const FRONTEND = 'Core';

    /**
     * Cache storage
     */
    const BACKEND = 'Memcached';

    /**
     * Cache object
     *
     * @var Zend_Cache
     */
    protected $cache = false;

    /**
     * Constructor - create a new interface to the cache
     *
     * @param string $prefix Prefix all cache IDs with this string
     */
    public function __construct($prefix = NULL) {
        // Setup Zend
        mr_zend::set_includepath();

        require_once('Zend/Cache.php');
        require_once('Zend/Cache/Exception.php');

        // For prefixes, switch forward slashes, which are not accepted, to underscores
        if (!is_null($prefix)) {
            $prefix = str_replace('/', '_', $prefix);
        }

        // DISABLED!!! Not yet implemented on our servers
        if (false and !empty(mr_var::get('mrconfig')->cache_lifetime)) {
            $frontendoptions = array(
                'cache_id_prefix' => $prefix,
                'lifetime' => mr_var::get('mrconfig')->cache_lifetime,
            );

            $backendoptions = array(
            );

            try {
                $this->cache = Zend_Cache::factory(
                    self::FRONTEND,
                    self::BACKEND,
                    $frontendoptions,
                    $backendoptions
                );
            } catch (Zend_Cache_Exception $e) {
                $this->cache = false;
            }
        }
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * Pass true as second param if you cached data that does not pass the is_string() check
     *
     * @param string $id Cache ID
     * @param boolean $unserialize Automatically unserialize the cached result
     * @return mixed
     * @throws coding_exception
     */
    public function load($id, $unserialize = false) {
        if ($this->cache === false) {
            return false;
        }
        try {
            $data = $this->cache->load($id);

            // Unserialize if needed
            if ($data and $unserialize) {
                $data = unserialize($data);
            }
            return $data;
        } catch (Zend_Cache_Exception $e) {
            throw new coding_exception('Zend Cache Error: '.$e->getMessage());
        }
    }

    /**
     * Test if a cache is available for the given id
     *
     * @param string $id Cache ID
     * @return boolean
     * @throws coding_exception
     */
    public function test($id) {
        if ($this->cache === false) {
            return false;
        }
        try {
            return $this->cache->test($id);
        } catch (Zend_Cache_Exception $e) {
            throw new coding_exception('Zend Cache Error: '.$e->getMessage());
        }
    }

    /**
     * Save data to the cache
     *
     * @param mixed $data The data to be saved, if data fails is_string() check, then it will be serialized.
     *                    You then must unserialize it when it is retrieved from cache or call the load method
     *                    like so: $cache->load('cacheId', true);
     * @param string $id Cache id (if not set, the last cache id will be used)
     * @return void
     * @throws coding_exception
     */
    public function save($data, $id = null) {
        if ($this->cache === false) {
            return true;
        }
        try {
            if (!is_string($data)) {
                $data = serialize($data);
            }
            return $this->cache->save($data, $id);
        } catch (Zend_Cache_Exception $e) {
            throw new coding_exception('Zend Cache Error: '.$e->getMessage());
        }
    }

    /**
     * Remove a cached item
     *
     * @param string $id Cache ID
     * @return boolean
     * @throws coding_exception
     */
    public function remove($id) {
        if ($this->cache === false) {
            return true;
        }
        try {
            return $this->cache->remove($id);
        } catch (Zend_Cache_Exception $e) {
            throw new coding_exception('Zend Cache Error: '.$e->getMessage());
        }
    }

    /**
     * Clean all cache entries
     *
     * @return boolean
     * @throws coding_exception
     */
    public function clean() {
        if ($this->cache === false) {
            return true;
        }
        try {
            return $this->cache->clean();
        } catch (Zend_Cache_Exception $e) {
            throw new coding_exception('Zend Cache Error: '.$e->getMessage());
        }
    }

    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $id Cache ID
     * @param int $extraLifetime The time to add to the life
     * @return boolean
     * @throws coding_exception
     */
    public function touch($id, $extraLifetime) {
        if ($this->cache === false) {
            return true;
        }
        try {
            return $this->cache->touch($id, $extraLifetime);
        } catch (Zend_Cache_Exception $e) {
            throw new coding_exception('Zend Cache Error: '.$e->getMessage());
        }
    }
}