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
 * @see local_mr_renderer
 */
require_once($CFG->dirroot.'/local/mr/renderer.php');

/**
 * MR Extended Renderer
 *
 * Proprietary Renderings
 *
 * @package local/mr
 */
class local_mr_extended_renderer extends local_mr_renderer {
    /**
     * Render mr_report_abstract
     *
     * @param mr_report_abstract $report mr_report_abstract instance
     * @return string
     */
    public function render_mr_report_abstract(mr_report_abstract $report) {
        // Send JSON if necessary
        $this->mr_html_table_json(
            $report->get_table(),
            $report->get_paging()
        );

        // Wrapper DIV
        $output = $this->output->box_start('boxwidthwide boxaligncenter mr_report');

        // Heading
        // $output .= $this->output->heading($report->name());

        // Render report SQL
        $output .= $this->help_render_mr_report_sql($report);

        // Render description
        if ($description = $report->get_description()) {
            $output .= $this->output->box($description, 'generalbox boxwidthnormal boxaligncenter mr_report_description');
        }

        // Render Hook
        $output .= $this->help_render_navigation_display($report);

        // Render filter
        if ($report->get_filter() instanceof mr_html_filter) {
            $output .= $this->render($report->get_filter());
        }

        // Render hook
        $output .= $this->help_render_chart_select($report);

        // Render as AJAX or our default rendering
        if ($report->get_config()->ajax and $report->get_preferences()->get('forceajax', $report->get_config()->ajaxdefault)) {
            $output .= $report->output_wrapper(
                $this->mr_html_table_ajax(
                    $report->get_table(),
                    $report->get_paging()
                )
            );
        } else {
            // Render paging top
            $output .= $this->render($report->get_paging());

            // Render table and allow report to wrap it with w/e
            $output .= $report->output_wrapper(
                $this->render($report->get_table())
            );

            // Render paging bottom
            $output .= $this->render($report->get_paging());
        }

        // Render export
        if ($report->get_export() instanceof mr_file_export) {
            $output .= $this->render($report->get_export());
        }

        // Render AJAX toggle link
        if ($report->get_config()->ajax) {
            if ($report->get_preferences()->get('forceajax', $report->get_config()->ajaxdefault)) {
                $newajax = 0;
                $label   = get_string('basichtml', 'local_mr');
            } else {
                $newajax = 1;
                $label   = get_string('standard', 'local_mr');
            }
            $url   = clone($report->get_url());
            $url->param('forceajax', $newajax);

            $link    = html_writer::link($url, $label, array('title' => $label));
            $output .= html_writer::tag('div', $link, array('class' => 'mr_ajax_table_forceajax'));
        }

        // Close wrapper DIV
        $output .= $this->output->box_end();

        return $output;
    }

    /**
     * Generate YUI view of table and paging
     *
     * @param mr_html_table $table Table instance
     * @param mr_html_paging $paging Paging instance
     * @return string
     */
    public function mr_html_table_ajax(mr_html_table $table, mr_html_paging $paging, $autoload = true, $id = null) {
        global $PAGE;

        // Columns
        $columns = array();
        foreach ($table->get_columns(true) as $column) {
            // Must set sortable to false if table is not sort enabled or if empty $rows
            if (!$table->get_sortenabled()) {
                $column->set_config('sortable', false);
            }
            $col = (object) array(
                'key'      => $column->get_name(),
                'label'    => $column->get_config()->heading,
                'sortable' => $column->get_config()->sortable,
            );
            if ($column->get_config()->editor) {
                $col->editor = $column->get_config()->editor;
            }
            $columns[] = $col;
        }

        // Page size
        $opts = array();
        if ($paging->get_perpageopts()) {
            foreach ($paging->get_perpageopts() as $opt) {
                if ($opt == 'all') {
                    $opts[] = (object) array('value' => 10000, 'text' => get_string('all'));
                } else {
                    $opts[] = (object) array('value' => $opt, 'text' => $opt);
                }
            }
        }

        // Place holder div's ID
        if(empty($id)){
            $id = html_writer::random_id();
        }
        
        $loadingmsg = $this->output->pix_icon('i/ajaxloader', get_string('loadingdotdotdot', 'local_mr')).
                      '&nbsp;'.get_string('loadingdotdotdot', 'local_mr');

        $module = array(
            'name'      => 'local_mr',
            'fullpath'  => '/local/mr/extended/renderer.js',
            'requires'  => array(
                'yui2-yahoo',
                'yui2-dom',
                'yui2-event',
                'yui2-element',
                'yui2-paginator',
                'yui2-datasource',
                'yui2-json',
                'yui2-connection',
                'yui2-get',
                'yui2-dragdrop',
                'yui2-datatable',
            ),
        );
        $arguments = array((object) array(
            'id'          => $id,
            'url'         => $table->get_url()->out(false, array('tjson' => 1)),
            'sort'        => $table->get_sort(),
            'order'       => $table->get_order(),
            'page'        => $paging->get_page(),
            'perpage'     => $paging->get_perpage(),
            'loadingmsg'  => $loadingmsg,
            'perpageopts' => $opts,
            'columns'     => $columns,
            'asc'         => SORT_ASC,
            'desc'        => SORT_DESC,
            'autoload' => $autoload,
        ));
        $PAGE->requires->js_init_call('M.local_mr.init_mr_html_table', $arguments, false, $module);

        return html_writer::tag('div', '', array('id' => $id, 'class' => 'mr_html_table mr_ajax_table'));
    }

    /**
     * Generate table rows as JSON
     *
     * @param mr_html_table $table Table instance
     * @param mr_html_paging $paging Paging instance
     * @return void
     * @todo Make this not a hack?
     */
    public function mr_html_table_json(mr_html_table $table, mr_html_paging $paging) {
        if (optional_param('tjson', 0, PARAM_BOOL)) {
            $json                  = new stdClass;
            $json->recordsReturned = (int) count($table->get_rows());
            $json->totalRecords    = (int) $paging->get_total();
            $json->startIndex      = (int) $paging->get_page();
            $json->sort            = $table->get_sort();
            $json->pageSize        = (int) $paging->get_perpage();
            $json->records         = array();
            $json->emptyMessage    = $table->get_emptymessage();

            if ($table->get_order() == SORT_ASC) {
                $json->dir = 'asc';
            } else {
                $json->dir = 'desc';
            }
            // If we are returning 0 records, we probably don't have any at all
            if ($json->recordsReturned == 0) {
                $json->totalRecords = 0;
            }

            $columns  = $table->get_columns(true);
            $htmlrows = $this->convert_to_htmlrows($table);
            foreach ($htmlrows as $htmlrow) {
                $position = 0;
                foreach ($columns as $column) {
                    if (array_key_exists($position, $htmlrow->cells)) {
                        $value = $htmlrow->cells[$position]->text;
                    } else {
                        $value = '';
                    }
                    $record[$column->get_name()] = $value;
                    $position++;
                }
                $json->records[] = (object) $record;
            }
            echo json_encode($json);
            die;
        }
    }

    /**
     * Hook for blocks/reports
     *
     * @param mr_report_abstract
     * @return string
     */
    protected function help_render_navigation_display(mr_report_abstract $report) {
        return '';
    }

    /**
     * Hook for blocks/reports
     *
     * @param mr_report_abstract
     * @return string
     */
    protected function help_render_chart_select(mr_report_abstract $report) {
        return '';
    }
}