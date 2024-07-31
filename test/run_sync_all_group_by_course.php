<?php

/**
 * Example URL : /local/obu_group_manager/test/run_sync_all_group_by_course.php?courseid=3
 */
require('../../../config.php');

global $CFG;

$courseid = required_param('courseid', PARAM_INT);

require_once($CFG->dirroot.'/local/obu_group_manager/locallib.php');

$trace = new \html_progress_trace();
local_obu_group_manager_all_group_sync($trace, $courseid);
$trace->finished();