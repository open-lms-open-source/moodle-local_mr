<?php
/**
 * Default controller
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package local/mr
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

    /**
     * Clean mr_cache
     */
    public function cleancache_action() {
        global $CFG;

        $cache = new mr_cache();
        $cache->clean();

        $this->notify->good('cachecleaned');

        return $this->output->single_button(new moodle_url("$CFG->wwwroot/$CFG->admin/settings.php?section=local_mr_cache"), get_string('continue'));
    }
}