<?php
/**
 * ODS Export
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/plugin/export/excel/class.php');

class block_reports_plugin_export_ods_class extends block_reports_plugin_export_excel_class {
    /**
     * File extension
     */
    protected $extension = 'ods';

    /**
     * Can only handle 100k
     */
    public function max_rows() {
        return 100000;
    }

    /**
     * Different workbook
     */
    public function _new_workbook() {
        global $CFG;

        require_once($CFG->dirroot.'/lib/odslib.class.php');

        return new MoodleODSWorkbook('-');
    }
}