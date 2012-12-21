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

/**
 * @see mr_db_table
 */
require_once($CFG->dirroot.'/local/mr/framework/db/table.php');

/**
 * @see mr_db_queue
 */
require_once($CFG->dirroot.'/local/mr/framework/db/queue.php');

/**
 * MR DB Dump
 *
 * Generates a MySQL dump file from
 * a recordset.
 *
 * Example:
 * <code>
 * <?php
 *      // Create a new instance
 *      $dump = new mr_db_dump("$CFG->dataroot/archive/plugin/logs.sql");
 *      // Get a resultset
 *      $rs = $DB->get_recordset_sql('SELECT * FROM tablename');
 *      // Run the dump and zip it up
 *      $zipfile = $dump->run('tablename', $rs)->zip();
 *
 *      // Later, call clean in some way if you want
 *      // to delete any of the files...
 *      $dump->clean();
 * ?>
 * </code>
 *
 * WARNING: Works only with mysqli
 *
 * @author Mark Nielsen
 * @package mr
 **/
class mr_db_dump {
    /**
     * Counter - counts number of rows sent to the dump file
     *
     * @var int
     */
    protected $rowsdumped = 0;

    /**
     * Full file path to the SQL dump file
     *
     * @var string
     */
    protected $file;

    /**
     * Constructor
     *
     * @param string $file The full path to create the SQL dump file, EG: $CFG->dataroot.'/archive/plugin/logs.sql'
     * @param boolean $mustnotexist If the file must not exist beforehand.  Prevents overwriting files on accident.
     * @param boolean $appendtime Append the time to the file name, helps with uniqueness and informative.  EG: Appends _YYYYMMDDHHMMSS
     * @throws coding_exception
     */
    public function __construct($file, $mustnotexist = true, $appendtime = true) {
        $this->file = $file;
        if ($appendtime) {
            $info       = pathinfo($this->file);
            $this->file = $info['dirname'].'/'.$info['filename'].'_'.date('YmdHis').'.'.$info['extension'];
        }
        if ($mustnotexist and file_exists($this->file)) {
            throw new coding_exception('File already exists (This prevents accidental file overwrites).');
        }
        $this->validate_file();
    }

    /**
     * Validates the file path
     *
     * Validation steps:
     *<ul>
     *  <li>The path must be set.</li>
     *  <li>The file extension must be 'sql'</li>
     *  <li>The file's directory must exist, but this will try to make it first.</li>
     *  <li>The file and directory must be writable.</li>
     *</ul>
     *
     * @return mr_db_dump
     * @throws coding_exception
     */
    protected function validate_file() {
        if (empty($this->file)) {
            throw new coding_exception('File is not set');
        }
        if (pathinfo($this->file, PATHINFO_EXTENSION) != 'sql') {
            throw new coding_exception('File extension must be \'sql\'');
        }
        if (!check_dir_exists(pathinfo($this->file, PATHINFO_DIRNAME))) {
            throw new coding_exception('File\'s directory does not exist and cannot be created');
        }
        if (!file_exists($this->file) and !touch($this->file)) {
            throw new coding_exception('Failed to create the file');
        }
        $fp = fopen($this->file, 'a');
        if (!is_resource($fp)) {
            throw new coding_exception('Failed to open file for writing');
        }
        fclose($fp);

        return $this;
    }

    /**
     * Run the dump
     *
     * One of the primary entry points.
     *
     * @param mixed $table The table name or an instance of mr_db_table
     * @param moodle_recordset $rs The recordset to iterate over and add to the dump file
     * @return mr_db_dump
     * @throws coding_exception
     */
    public function run($table, $rs) {
        $this->dump($table, $rs);
    }

    /**
     * Run the dump and delete records that get added to the dump
     *
     * One of the primary entry points.  Don't use
     * this method when exporting very very large amounts
     * of rows.  It is better to use separate delete SQL to
     * remove the rows.
     *
     * @param mixed $table The table name or an instance of mr_db_table
     * @param moodle_recordset $rs The recordset to iterate over and add to the dump file
     * @return mr_db_dump
     * @throws coding_exception
     */
    public function archive($table, $rs) {
        $this->dump($table, $rs, true);
    }

    /**
     * Actually runs the dump and optionally the archive
     *
     * @param string|mr_db_table $table The table name or an instance of mr_db_table
     * @param moodle_recordset $rs The recordset to iterate over and add to the dump file
     * @param bool $archive  If true, then records dumped will also be deleted
     * @throws coding_exception
     * @return mr_db_dump
     */
    protected function dump($table, $rs, $archive = false) {
        global $CFG, $DB;

        $this->validate_file();

        if (is_string($table)) {
            $table = new mr_db_table($table);
        }
        if (!$table instanceof mr_db_table) {
            throw new coding_exception('Must pass table name or an instance of mr_db_table');
        }
        if ($archive) {
            $queue = new mr_db_queue();
        }

        if ($rs->valid()) {
            $started   = true;
            $fp        = fopen($this->file, 'a');
            $tablename = $CFG->prefix.$table->get_name();
            $config    = $DB->export_dbconfig();
            $mysqli    = new mysqli($config->dbhost, $config->dbuser, $config->dbpass, $config->dbname);

            if ($mysqli->connect_error) {
                throw new coding_exception("Failed to connect to the database: ($mysqli->connect_errno) $mysqli->connect_error");
            }
            while ($rs->valid()) {
                $row = $rs->current();
                $rs->next();

                if ($started) {
                    $started   = false;
                    $columns   = array_keys((array) $row);
                    $columns   = implode('`,`', $columns);
                    $timestamp = time();
                    $timestr   = userdate($timestamp);

                    fwrite($fp, "--\n");
                    fwrite($fp, "-- Dumping data for table `$tablename` $timestr ($timestamp)\n");
                    fwrite($fp, "--\n\n");
                    fwrite($fp, "LOCK TABLES `$tablename` WRITE;\n");
                    fwrite($fp, "/*!40000 ALTER TABLE `$tablename` DISABLE KEYS */;\n");
                    fwrite($fp, "INSERT INTO `$tablename` (`$columns`) VALUES\n");
                }

                $values = array();
                foreach ($row as $field => $value) {
                    if (is_null($value)) {
                        $values[] = 'NULL';
                    } else if (is_numeric($value)) {
                        $values[] = $value;
                    } else {
                        if (mb_detect_encoding($value) != 'UTF-8') {
                            $value = mb_convert_encoding($value, 'UTF-8');
                        }
                        $values[] = '\''.$mysqli->real_escape_string($value).'\'';
                    }
                }
                $values = implode(',', $values);

                fwrite($fp, "($values)");

                // If not the last row, then prep for next row
                if ($rs->valid()) {
                    fwrite($fp, ",\n");
                }

                if ($archive) {
                    $record = $table->record($row);
                    $record->queue_delete();
                    $queue->add($record);
                }
                $this->rowsdumped++;
            }
            $rs->close();
            $mysqli->close();

            if ($archive) {
                $queue->flush();
            }

            fwrite($fp, ";\n/*!40000 ALTER TABLE `$tablename` ENABLE KEYS */;\nUNLOCK TABLES;\n\n");
            fclose($fp);
        }
        return $this;
    }

    /**
     * Get the number of rows dumped
     *
     * @return int
     */
    public function get_rows_dumped() {
        return $this->rowsdumped;
    }

    /**
     * Zip the SQL file
     *
     * @return string The path to the zip file
     * @throws coding_exception
     */
    public function zip() {
        global $CFG;

        require_once($CFG->libdir.'/filestorage/zip_packer.php');

        $this->validate_file();
        $zipfile = $this->get_zip_file();
        $packer  = new zip_packer();
        $zipname = pathinfo($this->file, PATHINFO_BASENAME);

        if (file_exists($zipfile)) {
            throw new coding_exception('Destination for zip file already exists');
        }
        if (!$packer->archive_to_pathname(array($zipname => $this->file), $zipfile)) {
            throw new coding_exception('Failed to zip file');
        }
        return $zipfile;
    }

    /**
     * Get the zip file path (may not exist!)
     *
     * @return string
     */
    protected function get_zip_file() {
        return pathinfo($this->file, PATHINFO_DIRNAME).'/'.pathinfo($this->file, PATHINFO_FILENAME).'.zip';
    }

    /**
     * Cleanup!
     *
     * Can delete the zip file and the SQL file
     *
     * @param boolean $deletezip Delete the zip file
     * @param boolean $deletesql Delete the SQL file
     * @param boolean $deleteifempty Force the deletion of both files if they are empty
     * @return mr_db_dump
     */
    public function clean($deletezip = true, $deletesql = true, $deleteifempty = true) {
        $zipfile     = $this->get_zip_file();
        $forcedelete = ($deleteifempty and $this->rowsdumped == 0);

        if (($deletezip or $forcedelete) and file_exists($zipfile)) {
            unlink($zipfile);
        }
        if (($deletesql or $forcedelete) and file_exists($this->file)) {
            unlink($this->file);
        }
        return $this;
    }
}