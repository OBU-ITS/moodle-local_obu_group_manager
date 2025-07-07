<?php

/**
 * Example URL : /local/obu_group_manager/test/get_all_group.php?courseid=2
 */
require('../../../config.php');

global $CFG;

if (!is_siteadmin()) {
    redirect(new \moodle_url('/'));
    die();
}

$courseid = required_param('courseid', PARAM_INT);

require_once($CFG->dirroot.'/local/obu_group_manager/locallib.php');

local_obu_group_manager_get_all_group($courseid);