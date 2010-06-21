<?php


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot.'/local/mr/framework/bootstrap.php');

class mr_bootstrap_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/bootstrap.php');

    // How to test?
}