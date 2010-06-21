<?php

$ADMIN->add('root', new admin_category('local_mr', get_string('mrframework', 'local_mr')));

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
$settings[] = new admin_setting_heading('cache_heading', get_string('cache_heading', 'local_mr'), get_string('cache_headingdesc', 'local_mr', $CFG->wwwroot.'/blocks/libmr/view.php?action=cleancache'));
$settings[] = new admin_setting_configselect('cache_lifetime', get_string('cache_lifetime', 'local_mr'), get_string('cache_lifetimedesc', 'local_mr'), 0, $lifetimes);

// Define the config plugin so it is saved to
// the config_plugin table then add to the settings page
$page = new admin_settingpage('local_mr_cache', get_string('mrcache', 'local_mr'));
foreach ($settings as $setting) {
    $setting->plugin = 'local/mr';
    $page->add($setting);
}
$ADMIN->add('local_mr', $page);