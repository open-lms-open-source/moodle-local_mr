<?php
/**
 * Text Export
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/plugin/export/csv/class.php');

class mr_file_export_text extends mr_file_export_csv {
    /**
     * Tab
     */
    protected $delimiter = "\t";

    /**
     * txt
     */
    protected $extension = 'txt';
}