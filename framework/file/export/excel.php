<?php
/**
 * Excel Export
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

require_once($CFG->dirroot.'/blocks/reports/plugin/export/base/class.php');

class block_reports_plugin_export_excel_class extends block_reports_plugin_export_base_class {
    /**
     * Max rows per worksheet
     */
    const MAXROWS = 65535;

    /**
     * Workbook instance
     *
     * @var MoodleExcelWorkbook
     */
    protected $workbook;

    /**
     * Current worksheet
     *
     * @var object
     */
    protected $writer;

    /**
     * Current row
     *
     * @var int
     */
    protected $row = 0;

    /**
     * Worksheet count
     *
     * @var int
     */
    protected $worksheet = 1;

    /**
     * Export name
     *
     * @var string
     */
    protected $name = '';

    /**
     * File extension
     *
     * @var string
     */
    protected $extension = 'xls';

    /**
     * Cannot make files with the lib
     */
    public function generates_file() {
        return false;
    }

    /**
     * Can only handle 50k
     */
    public function max_rows() {
        return 50000;
    }

    /**
     * Open workbook and send to browser
     *
     * @param string $name The preferred file name (no extension)
     * @return void
     */
    public function init($name) {
        $this->name = $name;
        $filename   = clean_filename($name);
        $filename   = trim($name, '_');

        $this->workbook = $this->_new_workbook();
        $this->workbook->send("$filename.$this->extension");

        // Adding the worksheet
        $this->writer =& $this->workbook->add_worksheet("$this->name $this->worksheet");
    }

    /**
     * Generate a new workbook
     *
     * @return mixed
     */
    public function _new_workbook() {
        global $CFG;

        require_once($CFG->dirroot.'/lib/excellib.class.php');

        return new MoodleExcelWorkbook('-');
    }

    /**
     * Write headers to the file
     */
    public function set_headers($headers) {
        $this->add_row($headers);
    }

    /**
     * Write the row to the file
     */
    public function add_row($row) {
        if ($this->row >= self::MAXROWS) {
            $this->worksheet++;

            $this->writer =& $this->workbook->add_worksheet("$this->name $this->worksheet");
            $this->row    = 0;
        }

        $column = 0;
        foreach ($row as $cell) {
            if (is_numeric($cell)) {
                $this->writer->write_number($this->row, $column, $cell);
            } else {
                $this->writer->write_string($this->row, $column, $cell);
            }
            $column++;
        }
        $this->row++;
    }

    /**
     * Close the file pointer and return file
     */
    public function close() {
        $this->workbook->close();
    }
}