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
     * @param object $mform Moodle form
     * @param array $options Array of autocomplete options, if $hiddenfieldname is
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
        global $CFG;

        static $init = false;

        if (!$init) {
            $sheet = $CFG->wwwroot.'/lib/yui/autocomplete/assets/skins/sam/autocomplete.css';
            if (!isset($CFG->stylesheets)) {
                $CFG->stylesheets = array();
            }
            if (!in_array($sheet, $CFG->stylesheets)) {
                array_unshift($CFG->stylesheets, $sheet);
            }

            require_js(array(
                'yui_dom-event',
                'yui_datasource',
                'yui_get',
                'yui_animation',
                'yui_autocomplete',
            ));
            $init = true;
        }

        // Generate data source
        $data  = array();
        foreach ($options as $optionid => $option) {
            if (empty($hiddenfieldname)) {
                $data[] = '\''.addslashes_js($option).'\'';
            } else {
                $data[] = '{text:\''.addslashes_js($option)."', id: $optionid}";
            }
        }
        $data = implode(',', $data);

        if (empty($hiddenfieldname)) {
            $fields = "['text']";
        } else {
            $fields = "['text', 'id']";
        }

        $javascript = <<<HTML
<script type="text/javascript">
    (function() {
        // Add class for skinning
        YAHOO.util.Dom.addClass(document.getElementsByTagName('body')[0], 'yui-skin-sam');

        var myInputField = document.getElementById('id_$textfieldname');

        // Surround input field in a div to control width
        var myWrapperDiv = document.createElement('div');
        myWrapperDiv.setAttribute('style', 'width: {$width}px;');
        myInputField.parentNode.appendChild(myWrapperDiv);
        myWrapperDiv.appendChild(myInputField);

        // Add container div for the autocomplete list
        var myContainerDiv = document.createElement('div');
        myContainerDiv.setAttribute('id', 'id_autocontainer_$textfieldname');
        myWrapperDiv.appendChild(myContainerDiv);

        // Use a LocalDataSource
        var myDataSource = new YAHOO.util.LocalDataSource([ $data ]);
        myDataSource.responseSchema = {fields : $fields};

        // Instantiate the AutoComplete
        var myAutoComplete = new YAHOO.widget.AutoComplete('id_$textfieldname', 'id_autocontainer_$textfieldname', myDataSource);
        myAutoComplete.useShadow           = true;
        myAutoComplete.maxResultsDisplayed = 20;
        myAutoComplete.applyLocalFilter    = true;
        myAutoComplete.queryMatchContains  = true;

        var hiddenName = '$hiddenfieldname';
        if (hiddenName != '') {
            var myHiddenField = YAHOO.util.Dom.get('id_' + hiddenName);
            var mySelectHandler = function(sType, aArgs) {
                // Selected item's result data
                var oData = aArgs[2];

                // Update hidden form field with the selected item's ID
                myHiddenField.value = oData[1];
            };
            var myEnforceHandler = function() {
                myHiddenField.value = 0;
            };
            var unmatchedItemSelectHandler = function(oSelf , sSelection) {
                // Instead of using myAutoComplete.forceSelection
                // need to use this otherwise defaults get cleared onfocus/unfocus
                if (myAutoComplete._sInitInputValue != myInputField.value) {
                    myAutoComplete._clearSelection();
                }
            };
            myAutoComplete.itemSelectEvent.subscribe(mySelectHandler);
            myAutoComplete.selectionEnforceEvent.subscribe(myEnforceHandler);
            myAutoComplete.unmatchedItemSelectEvent.subscribe(unmatchedItemSelectHandler);
        }
    })();
</script>
HTML;

        // Update form - need to force some attributes and add the javascript
        $mform->updateElementAttr($textfieldname, array('autocomplete' => 'off', 'style' => "width: {$width}px;"));
        $mform->addElement('html', $javascript);

        // Add ID to hidden field so javascript can find it
        if (!empty($hiddenfieldname)) {
            $mform->updateElementAttr($hiddenfieldname, array('id' => "id_$hiddenfieldname"));
        }
    }
}