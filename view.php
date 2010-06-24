<?php
/**
 * View renderer
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package local/mr
 */

require_once('../../config.php');
require($CFG->dirroot.'/local/mr/bootstrap.php');

mr_controller::render('local/mr', 'mrframework', 'local_mr');