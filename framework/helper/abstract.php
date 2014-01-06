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
 * MR Helper Abstraction
 *
 * Implement a function named "direct" to support direct calling of the helper.
 *
 * @author Mark Nielsen
 * @package mr
 * @example helper/world.php See a helper implemented in a plugin
 */
abstract class mr_helper_abstract {
    /**
     * The namespace of the mr_helper that created this helper instance
     *
     * @var string
     */
    protected $_helper_namespace = 'local/mr/framework';

    /**
     * Set the namespace of the mr_helper that created this instance
     *
     * @param string $namespace The namespace, EG: blocks/helloworld
     * @return void
     */
    public function _set_helper_namespace($namespace) {
        $this->_helper_namespace = $namespace;
    }
}