<?php


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot.'/local/mr/framework/autoload.php');

class mr_autoload_test extends UnitTestCase {

    public static $includecoverage = array('local/mr/framework/autoload.php');

    public function test_autoload() {
        $instance = mr_autoload::get_instance();
        $this->assertTrue($instance->autoload('mr_bootstrap'));
        $this->assertFalse($instance->autoload('mr_crazy_class_name'));
        $this->assertFalse($instance->autoload('crazy_non_mr_class_name'));
    }

    public function test_no_namespace() {
        $this->expectException('coding_exception');
        $instance = new mr_autoload('');
    }

    /*
    The following two methods would give us full code coverage - need to figure out a way to implement them though

    public function test_register() {
        mr_autoload::register();

        $this->assertTrue(class_exists('mr_bootstrap'));
    }

    public function test_unregister() {
        mr_autoload::register();
        mr_autoload::unregister();

        $this->assertFalse(class_exists('mr_bootstrap'));
    }
    */
}