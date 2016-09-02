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
 * @package local_mr
 * @author Mark Nielsen
 */

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * Default controller
 *
 * @author Mark Nielsen
 * @package local_mr
 */
class local_mr_controller_default extends mr_controller_block {
    /**
     * Special setup for docs page
     */
    public function setup() {
        global $CFG;

        if ($this->action == 'docs') {
            require_once($CFG->libdir.'/adminlib.php');
            admin_externalpage_setup('local_mr_docs');
        } else {
            parent::setup();
        }
    }

    /**
     * Require capability for viewing this controller
     */
    public function require_capability() {
        require_capability('moodle/site:config', $this->get_context());
    }

    /**
     * Default screen
     */
    public function view_action() {
        return '';
    }

    /**
     * Display Framework Docs
     */
    public function docs_action() {
        $link      = new moodle_url('/local/mr/docs/index.html');
        $action    = new popup_action('click', $link, 'localmrdocs', array('height' => 950, 'width' => 1500));
        $docspop   = $this->output->action_link($link, get_string('popupdocs', 'local_mr'), $action);
        $link      = new moodle_url('/local/mr/docs/errors.html');
        $action    = new popup_action('click', $link, 'localmrdocs', array('height' => 950, 'width' => 1500));
        $errorspop = $this->output->action_link($link, get_string('popuperrors', 'local_mr'), $action);

        return $this->output->box("$docspop&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$errorspop").
               $this->helper->tag->iframe('Your browser does not support iframes.')
                                 ->src('docs/index.html')
                                 ->height('800px')
                                 ->width('100%');
    }
}