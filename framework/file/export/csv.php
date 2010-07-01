<?php
/**
 * CSV Export
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks/reports
 **/

/**
 * @see mr_file_export_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/file/export/abstract.php');
require_once($CFG->libdir.'/filelib.php');

class mr_file_export_csv extends mr_file_export_abstract {
    /**
     * The export file
     *
     * @var string
     */
    protected $file;

    /**
     * The file point to the export file
     *
     * @var resource
     */
    protected $fp;

    /**
     * Value delimiter
     *
     * @var string
     */
    protected $delimiter = ',';

    /**
     * File extension
     *
     * @var string
     */
    protected $extension = 'csv';

    /**
     * Make the directory for the file and open the file pointer
     *
     * @param string $name The preferred file name (no extension)
     * @param string $dir The directory to store the file in (temp by default)
     * @return void
     */
    public function init($name, $dir = NULL) {
        global $CFG;

        if (is_null($dir)) {
            $dir = $CFG->dataroot.'/temp';
        }
        check_dir_exists($dir, true, true);

        // Generate file path
        $name = clean_filename($name);
        $name = trim($name, '_');

        $this->file = "$dir/$name.$this->extension";

        // Make sure it doesn't exist
        $this->cleanup();

        // Open for writing
        $this->fp = fopen($this->file, 'w');
    }

    /**
     * Write headers to the file
     */
    public function set_headers($headers) {
        fputcsv($this->fp, $headers, $this->delimiter);
    }

    /**
     * Write the row to the file
     */
    public function add_row($row) {
        fputcsv($this->fp, $row, $this->delimiter);
    }

    /**
     * Close the file pointer and return file
     */
    public function close() {
        fclose($this->fp);
        return $this->file;
    }

    /**
     * Remove the csv file
     */
    public function cleanup() {
        if (file_exists($this->file)) {
            fulldelete($this->file);
        }
    }
}