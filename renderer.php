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
 * MR Renderer
 *
 * Default renderer for the framework.
 *
 * @package mr
 * @author Mark Nielsen
 */
class local_mr_renderer extends plugin_renderer_base {
    /**
     * Renders mr_notify
     *
     * @param mr_notify $notify mr_notify instance
     * @return string
     */
    protected function render_mr_notify(mr_notify $notify) {
        $output = '';
        foreach($notify->get_messages() as $message) {
            $output .= $this->output->notification($message[0], $message[1]);
        }
        return $output;
    }

    /**
     * Renders mr_tabs
     *
     * @param mr_tabs $tabs mr_tabs instance
     * @return string
     */
    protected function render_mr_tabs(mr_tabs $tabs) {
        $rows   = $tabs->get_rows();
        $output = '';

        if (!empty($rows)) {
            $inactive = $active = array();

            if (count($rows) == 2 and !empty($tabs->subtab) and !empty($rows[1][$tabs->subtab])) {
                $active[]   = $tabs->toptab;
                $currenttab = $tabs->subtab;
            } else {
                $currenttab = $tabs->toptab;
            }
            $output = print_tabs($rows, $currenttab, $inactive, $active, true);
        }
        return $output;
    }

    /**
     * Render mr_heading
     *
     * @param mr_heading $heading mr_heading instance
     * @return string
     */
    protected function render_mr_heading(mr_heading $heading) {
        // Do we have anything to render?
        if (empty($heading->text)) {
            return '';
        }

        $icon = '';
        if (!empty($heading->icon)) {
            $icon = $this->output->pix_icon($heading->icon, $heading->iconalt, $heading->component, array('class'=>'icon'));
        }
        $help = '';
        if (!empty($heading->helpidentifier)) {
            $help = $this->output->help_icon($heading->helpidentifier, $heading->component);
        }
        return $this->output->heading($icon.$heading->text.$help, $heading->level, $heading->classes, $heading->id);
    }
}