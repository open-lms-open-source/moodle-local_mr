<?php
/**
 * Text Export
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/plugin/export/csv/class.php');

class block_reports_plugin_export_text_class extends block_reports_plugin_export_csv_class {
    /**
     * Tab
     */
    protected $delimiter = "\t";

    /**
     * txt
     */
    protected $extension = 'txt';
}