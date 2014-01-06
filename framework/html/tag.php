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
 * MR HTML Tag
 *
 * This class is designed to generate HTML tags
 * with attributes.
 *
 * The class relies on the fluent interface in
 * order to build html tags with their attributes.
 *
 * The first call is always a call to a function whose name
 * is the name of the HTML tag you want to create.
 *
 * Example:
 * <code>
 * <?php
 * $tag = new mr_html_tag();
 * // Generate an <a> tag
 * $html = $tag->a('Click me!')->href('http://foo.bar')->close();
 * // $html is set to <a href="http://foo.bar">Click me!</a>
 *
 * // Generate a <div> tag
 * $html = $tag->div('Some words')->class('centerpara')->close();
 * // $html is set to <div class="centerpara">Some words</div>
 * ?>
 * </code>
 *
 * Also, there is a static interface.
 *
 * Example (this is equivalent to the above example):
 * <code>
 * <?php
 * $html = mr_html_tag::open()->a('Click me!')->href('http://foo.bar')->close();
 * $html = mr_html_tag::open()->div('Some words')->class('centerpara')->close();
 * ?>
 * </code>
 *
 * There are a couple of ways to modify attributes.  Review the rest of the
 * class methods and see example code for recommended ways to call it.
 *
 * @package mr
 * @author Mark Nielsen
 * @example controller/default.php See this class in action
 */
class mr_html_tag {
    /**
     * HTML tag name
     *
     * @var string
     */
    protected $tag = NULL;

    /**
     * HTML tag contents (EG: goes between the open/close tags)
     *
     * @var string
     */
    protected $contents = NULL;

    /**
     * HTML tag attribute list
     *
     * @var object
     */
    protected $attributes;

    /**
     * Constructor
     */
    public function __construct() {
        $this->attributes = new stdClass;
    }

    /**
     * Init routine
     *
     * This is protected because it should only
     * be called by this class itself through
     * mr_html_tag::__call()
     *
     * @param string $tag The HTML tag name
     * @param array $contents The HTML tag contents
     * @return void
     */
    protected function init($tag, $contents) {
        $this->tag = $tag;

        if (is_array($contents) and !empty($contents)) {
            $this->contents = implode('', $contents);
        }
    }

    /**
     * Convert this object to it's HTML string equivalent
     *
     * @return string
     */
    public function __toString() {
        return $this->close();
    }

    /**
     * This is where all of the real work is done.
     *
     * Special method calls include:
     * <ul>
     *      <li>append_ATTRIBUTENAME($value) (Same as mr_html_tag::prepend_attribute(ATTRIBUTENAME, $value))</li>
     *      <li>prepend_ATTRIBUTENAME($value) (Same as mr_html_tag::prepend_attribute(ATTRIBUTENAME, $value))</li>
     *      <li>remove_ATTRIBUTENAME() (Same as mr_html_tag::remove_attribute(ATTRIBUTENAME))</li>
     *      <li>remove_ATTRIBUTENAME() (Same as mr_html_tag::get_attribute(ATTRIBUTENAME))</li>
     * </ul>
     *
     * How to use:
     * <code>
     * <?php
     * $tag  = new mr_html_tag();
     * $link = $tag->a('Click me!');  // This will return a new instance
     *                                // mr_html_tag and set the tag='a'
     *                                // and contents='Click me!'
     * $link->href('http://foo.bar'); // This will add an attribute to the a
     *                                // tag: href="http://foo.bar" (Same as
     *                                // mr_html_tag::add_attribute()
     * $html = $link->close();        // This will close the tag and return the HTML
     *
     * // All of the above calls can be stringed together using
     * // the fluent interface to get the HTML
     * $html = $tag->a('Click me!')
     *             ->href('http://foo.bar')
     *             ->close();
     * ?>
     * </code>
     *
     * @param string $name The function name called
     * @param string $arguments The function arguments passed
     * @return mixed
     * @throws coding_exception
     * @example controller/default.php See more examples
     */
    public function __call($name, $arguments) {
        $parts = explode('_', $name);

        // Special case, tag not yet defined
        if (is_null($this->tag)) {
            // Create a new tag instance and set it up
            $html = new mr_html_tag();
            $html->init($name, $arguments);

            return $html;

        // Handle adding of an attribute
        } else if (count($parts) == 1) {
            if (count($arguments) != 1) {
                throw new coding_exception("Invalid method call mr_html_tag::$name() - must pass an argument");
            }
            return $this->add_attribute($name, $arguments[0]);

        // Handle attribute manipulation and retrieval
        } else if (count($parts) == 2) {
            list($method, $attrname) = $parts;

            switch ($method) {
                case 'get':
                    return $this->get_attribute($attrname);
                case 'append':
                case 'prepend':
                    if (count($arguments) != 1) {
                        throw new coding_exception("Invalid method call mr_html_tag::$name() - must pass an argument");
                    }
                    $method = "{$method}_attribute";
                    return $this->$method($attrname, $arguments[0]);

                case 'remove':
                    return $this->remove_attribute($attrname);
            }
        }
        throw new coding_exception("Call to non-existent method: mr_html_tag::$name()");
    }

    /**
     * Static interface
     *
     * Example
     * <code>
     * <?php
     * $html = mr_html_tag::open()->a('Click me!')->href('http://foo.bar')->close();
     * ?>
     * </code>
     *
     * @return mr_html_tag
     */
    public static function open() {
        return new mr_html_tag();
    }

    /**
     * Get an attribute
     *
     * Recommended way to call:
     * <code>
     * <?php
     * $tag  = new mr_html_tag();
     * $link = $tag->a('Click me!')->href('http://foo.bar');
     * $href = $link->get_href();  // Calls get_attribute('href') and returns 'http://foo.bar'
     * ?>
     * </code>
     *
     * @param string $name The attribute name
     * @return mixed
     */
    public function get_attribute($name) {
        if (isset($this->attributes->$name)) {
            return $this->attributes->$name;
        }
        return false;
    }

    /**
     * Remove an attribute
     *
     * Recommended way to call:
     * <code>
     * <?php
     * $tag  = new mr_html_tag();
     * $link = $tag->a('Click me!')->href('http://foo.bar');
     * $link->remove_href(); // Calls remove_attribute('href') and unsets href attribute
     * ?>
     * </code>
     *
     * @param string $name The attribute name
     * @return mr_html_tag
     */
    public function remove_attribute($name) {
        unset($this->attributes->$name);
        return $this;
    }

    /**
     * Bulk remove attributes
     *
     * @param array $attributes An array of attribute names
     * @return mr_html_tag
     */
    public function remove_attributes($attributes) {
        foreach ($attributes as $name) {
            $this->remove_attribute($name);
        }
        return $this;
    }

    /**
     * Get an attribute
     *
     * Recommended way to call:
     * <code>
     * <?php
     * $tag  = new mr_html_tag();
     * $link = $tag->a('Click me!');  // Create a link tag
     * $link->href('http://foo.bar'); // Calls add_attribute('href', 'http://foo.bar')
     * ?>
     * </code>
     *
     * @param string $name The attribute name
     * @param mixed $value The attribute value
     * @return mr_html_tag
     */
    public function add_attribute($name, $value) {
        $this->attributes->$name = $value;
        return $this;
    }

    /**
     * Bulk add attributes
     *
     * @param mixed $attributes Array or object of atrributes
     * @return mr_html_tag
     */
    public function add_attributes($attributes) {
        foreach ($attributes as $name => $value) {
            $this->add_attribute($name, $value);
        }
        return $this;
    }

    /**
     * Append an attribute
     *
     * Recommended way to call:
     * <code>
     * <?php
     * $tag  = new mr_html_tag();
     * $link = $tag->a('Click me!')->href('http://foo.bar')->class('foo');
     * $link->append_class('bar');  // Calls append_attribute('class', 'bar') 
     *                              // and class attribute will render as class="foo bar"
     * ?>
     * </code>
     *
     * @param string $name The attribute name
     * @param mixed $value The attribute value to append
     * @return mr_html_tag
     */
    public function append_attribute($name, $value) {
        if ($current = $this->get_attribute($name)) {
            $this->add_attribute($name, "$current $value");
        } else {
            $this->add_attribute($name, $value);
        }
        return $this;
    }

    /**
     * Bulk append attributes
     *
     * @param mixed $attributes Array or object of atrributes
     * @return mr_html_tag
     */
    public function append_attributes($attributes) {
        foreach ($attributes as $name => $value) {
            $this->append_attribute($name, $value);
        }
        return $this;
    }

    /**
     * Prepend an attribute
     *
     * Recommended way to call:
     * <code>
     * <?php
     * $tag  = new mr_html_tag();
     * $link = $tag->a('Click me!')->href('http://foo.bar')->class('foo');
     * $link->prepend_class('bar');  // Calls prepend_attribute('class', 'bar') 
     *                               // and class attribute will render as class="bar foo"
     * ?>
     * </code>
     *
     * @param string $name The attribute name
     * @param mixed $value The attribute value to prepend
     * @return mr_html_tag
     */
    public function prepend_attribute($name, $value) {
        if ($current = $this->get_attribute($name)) {
            $this->add_attribute($name, "$value $current");
        } else {
            $this->add_attribute($name, $value);
        }
        return $this;
    }

    /**
     * Bulk prepend attributes
     *
     * @param mixed $attributes Array or object of atrributes
     * @return mr_html_tag
     */
    public function prepend_attributes($attributes) {
        foreach ($attributes as $name => $value) {
            $this->prepend_attribute($name, $value);
        }
        return $this;
    }

    /**
     * Render the HTML tag
     *
     * This method will render the tag with all of its
     * attributes and content.  You can pass additional
     * strings to this method to add more content to the
     * tag.
     *
     * Example code:
     * <code>
     * <?php
     * $tag  = new mr_html_tag();
     * $html = $tag->div()->class('generalbox')->close(
     *     $somestring1,
     *     $somestring2,
     *     $tag->p($somestring3)
     * );
     * ?>
     * </code>
     *
     * @param string $param Pass as many strings as you like, all will be added to the contents of the tag
     * @return string
     * @throws coding_exception
     */
    public function close() {
        if (is_null($this->tag)) {
            throw new coding_exception('The HTML tag name is not defined, cannot render HTML');
        }

        // Handle any strings passed
        $contents = func_get_args();
        if (!empty($contents)) {
            $this->contents = ((string) $this->contents).implode('', $contents);
        }

        // Generate the tag
        if (!is_null($this->contents)) {
            return html_writer::tag($this->tag, $this->contents, (array) $this->attributes);
        }
        return html_writer::empty_tag($this->tag, (array) $this->attributes);
    }
}