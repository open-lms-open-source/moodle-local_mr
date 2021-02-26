// This file is part of Moodle - http://moodle.org/ //
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form autosubmit JavaScript module.
 *
 * @package    local_mr
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2017 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    var processChanges = function(e) {
        var select = $(e.target).closest('select.autosubmit');
        if (!select) {
            return;
        }
        var form = select.closest('form');
        if (form) {
            form.submit();
        }
    };

    return {
        init: function(config) {
            var selectid = config.selectid;

            var select = $('#' + selectid);
            if (select) {
                select.change(processChanges);
            }
        }
    };
});