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
 * MR Helper HTML
 *
 * Similar to lib/weblib.php, provide methods that
 * assist with generating HTML.
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_helper_html extends mr_helper_abstract {

    /**
     * Add autocomplete to a form text field
     *
     * @param MoodleQuickForm $mform Moodle form
     * @param array|moodle_url $options Array of autocomplete options, if $hiddenfieldname is
     *                       passed, array indexes are considered record IDs
     * @param string $textfieldname The text field's name
     * @param string $hiddenfieldname The hidden field's name.  If passed,
     *                                the option index will be set to this hidden
     *                                value when its option value is selected in
     *                                the text field
     * @param string $width The pixel width of the text field (Due to YUI, must
     *                      use width instead of size)
     * @return void
     * @link http://developer.yahoo.com/yui/examples/autocomplete/ac_basic_array.html What you get with no $hiddenfieldname
     * @link http://developer.yahoo.com/yui/examples/autocomplete/ac_itemselect.html What you get with $hiddenfieldname
     */
    public function mform_autocomplete($mform, $options, $textfieldname, $hiddenfieldname = '', $width = '300') {
        global $PAGE;

        $url  = NULL;
        $data = NULL;

        // Generate data source
        if ($options instanceof moodle_url) {
            $url = $options->out(false);
        } else {
            $data  = array();
            foreach ($options as $optionid => $option) {
                if (empty($hiddenfieldname)) {
                    $data[] = $option;
                } else {
                    $data[] = (object) array('text' => $option, 'id' => $optionid);
                }
            }
        }

        $fields = array('text');
        if (!empty($hiddenfieldname)) {
            $fields[] = 'id';
        }

        $module = array(
            'name'      => 'local_mr_framework',
            'fullpath'  => '/local/mr/framework/assets/javascript.js',
            'requires'  => array(
                'yui2-yahoo',
                'yui2-dom',
                'yui2-event',
                'yui2-datasource',
                'yui2-json',
                'yui2-connection',
                'yui2-get',
                'yui2-animation',
                'yui2-autocomplete',
            ),
        );
        $arguments = array((object) array(
            'fieldname' => $textfieldname,
            'hiddenfieldname' => $hiddenfieldname,
            'width' => $width,
            'url' => $url,
            'data' => $data,
            'fields' => $fields,
        ));
        $PAGE->requires->js_init_call('M.local_mr.init_mr_html_autocomplete', $arguments, true, $module);
        // $PAGE->requires->css('/lib/yui/2.8.1/build/autocomplete/assets/autocomplete-core.css');
        // $PAGE->requires->css('/lib/yui/2.8.1/build/autocomplete/assets/skins/sam/autocomplete.css');

        // Update form - need to force some attributes and add the javascript
        $mform->updateElementAttr($textfieldname, array('autocomplete' => 'off', 'style' => "width: {$width}px;"));

        // Add ID to hidden field so javascript can find it
        if (!empty($hiddenfieldname)) {
            $mform->updateElementAttr($hiddenfieldname, array('id' => "id_$hiddenfieldname"));
        }
    }


    /**
     * Javascript initialization for selectmultiplus filter
     *
     * @param string $selectname - name of the select element
     * @return void
     *
     * @link http://yuilibrary.com/yui/docs/autocomplete/
     * @link http://yuilibrary.com/yui/docs/autocomplete/#select-node
     */
    public function filter_selectmultiplus_init($selectname) {
        global $PAGE;

        $textname = $selectname . '_autocomplete';
        $uldivid  = 'id_' . $selectname . '_addedlist';

        $module = array(
            'name' => 'local_mr_framework',
            'fullpath' => '/local/mr/framework/assets/javascript.js',
            'requires' => array(
                'node',
                'event-key',
                'autocomplete',
                'autocomplete-filters',
            ),
        );

        $arguments = array((object) array(
            'selectname' => $selectname,
            'textname' => $textname,
            'uldivid' => $uldivid
        ));

        $PAGE->requires->js_init_call('M.local_mr.init_filter_selectmultiplus', $arguments, true, $module);

    }
}