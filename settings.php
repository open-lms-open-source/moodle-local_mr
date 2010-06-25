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
 * @package local/mr
 * @author Mark Nielsen
 */

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

if (defined('MR_CACHE_TEST') or defined('MR_DOCS')) {
    $ADMIN->add('root', new admin_category('local_mr', get_string('mrframework', 'local_mr')));
}

if (defined('MR_CACHE_TEST')) {
    $lifetimes = array(
        0               => get_string('never'),
        (MINSECS * 10)  => get_string('xminutes', 'local_mr', 10),
        (MINSECS * 30)  => get_string('xminutes', 'local_mr', 30),
        HOURSECS        => get_string('xhours', 'local_mr', 1),
        (HOURSECS * 4)  => get_string('xhours', 'local_mr', 4),
        (HOURSECS * 12) => get_string('xhours', 'local_mr', 12),
        DAYSECS         => get_string('xdays', 'local_mr', 1),
        WEEKSECS        => get_string('xweeks', 'local_mr', 1),
        (WEEKSECS * 4)  => get_string('xweeks', 'local_mr', 4),
    );

    $settings   = array();
    $settings[] = new admin_setting_heading('cache_heading', get_string('cache_heading', 'local_mr'), get_string('cache_headingdesc', 'local_mr', $CFG->wwwroot.'/local/mr/view.php?action=cleancache'));
    $settings[] = new admin_setting_configselect('cache_lifetime', get_string('cache_lifetime', 'local_mr'), get_string('cache_lifetimedesc', 'local_mr'), 0, $lifetimes);

    // Define the config plugin so it is saved to
    // the config_plugin table then add to the settings page
    $page = new admin_settingpage('local_mr_cache', get_string('mrcache', 'local_mr'));
    foreach ($settings as $setting) {
        $setting->plugin = 'local/mr';
        $page->add($setting);
    }
    $ADMIN->add('local_mr', $page);
}

if (defined('MR_DOCS')) {
    $ADMIN->add('local_mr', new admin_externalpage('local_mr_docs', get_string('local_mr_docs', 'local_mr'), "$CFG->wwwroot/local/mr/view.php?action=docs"));
}
