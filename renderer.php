<?php

class local_mr_renderer extends plugin_renderer_base {
    protected function render_mr_notify(mr_notify $notify) {
        $output = '';
        foreach($notify->get_messages() as $message) {
            $output .= $this->output->notification($message[0], $message[1]);
        }
        return $output;
    }

    protected function render_mr_tabs(mr_tabs $tabs) {
        $currenttab = $tabs->get_current();
        $alltabs    = $tabs->get_tabs();

        if (!empty($currenttab) and !empty($alltabs['__parents__'])) {
            $tabs = $toptabs = $subtabs = $inactive = $active = array();

            foreach ($alltabs['__parents__'] as $parents) {
                $toptabs = array_merge($toptabs, $parents);

                foreach ($parents as $tabindex => $parent) {
                    if (empty($subtabs) and !empty($alltabs[$tabindex])) {
                        foreach ($alltabs[$tabindex] as $children) {
                            $subtabs = array_merge($subtabs, $children);
                        }
                        if (!array_key_exists($currenttab, $subtabs)) {
                            $subtabs = array();
                        } else {
                            $active[] = $tabindex;
                        }
                    }
                }
            }
            $tabs[] = $toptabs;
            if (!empty($subtabs)) {
                $tabs[] = $subtabs;
            }
            return print_tabs($tabs, $currenttab, $inactive, $active, true);
        }
        return '';
    }

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