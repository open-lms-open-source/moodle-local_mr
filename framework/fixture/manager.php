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
 * MR Fixture Manager
 *
 * This class helps to manage fixture objects by
 * giving you a place to store your fixtures.
 *
 * Also, this class helps to delete all of your
 * created fixtures.
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_fixture_manager {
    /**
     * @var mr_fixture_manager
     */
    protected static $instance;

    /**
     * @var mr_fixture_manager[]
     */
    protected static $instances = array();

    /**
     * @var mr_fixture_interface[]
     */
    protected $fixtures = array();

    /**
     * A single static instance, helpful for unit tests
     * when you just need a single instance that's easily
     * accessible.
     *
     * @static
     * @return mr_fixture_manager
     */
    public static function instance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Registers itself with all known instances
     */
    public function __construct() {
        self::$instances[] = $this;
    }

    /**
     * Set a fixture instance by name and builds the fixture
     *
     * The name is used to later retrieve your fixture.
     *
     * @param string $name A unique name for the fixture
     * @param mr_fixture_interface $fixture
     * @return \mr_fixture_manager
     * @throws coding_exception
     * @see mr_fixture_manager::get()
     */
    public function set($name, mr_fixture_interface $fixture) {
        if ($this->has($name)) {
            throw new coding_exception("Fixture with associated name of $name has already been set, please choose a unique name for each fixture");
        }
        $fixture->build();
        $this->fixtures[$name] = $fixture;
        return $this;
    }

    /**
     * Get a fixture instance by name
     *
     * @param string $name The unique name for the fixture
     * @return mr_fixture_interface
     * @throws coding_exception
     */
    public function get($name) {
        if (!$this->has($name)) {
            throw new coding_exception("Could not find a fixture associated with the name of $name");
        }
        return $this->fixtures[$name];
    }

    /**
     * Determine if a fixture with a specific name exists
     *
     * @param string $name The unique name for the fixture
     * @return bool
     */
    public function has($name) {
        return array_key_exists($name, $this->fixtures);
    }

    /**
     * Delete all fixtures
     *
     * @see mr_fixture_manager::tearDown()
     */
    public function destroy() {
        foreach ($this->fixtures as $fixture) {
            $fixture->destroy();
        }
        $this->fixtures = array();
    }

    /**
     * Convenience method for unit tests, call in your tearDown
     * method to delete all of the fixtures from all fixture managers
     * you may have created.
     *
     * @static
     */
    public static function tearDown() {
        // Don't worry about cleanup, Moodle will reset the database anyways.
        self::$instances = array();
        self::$instance = null;
    }
}