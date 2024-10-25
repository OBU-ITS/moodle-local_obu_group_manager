<?php

/**
 * Example URL : /local/obu_group_manager/test/create_class_group.php?courseid=3
 */
require('../../../config.php');

global $CFG;

if (!is_siteadmin()) {
    redirect(new \moodle_url('/'));
    die();
}

$courseid = required_param('courseid', PARAM_INT);

require_once($CFG->dirroot.'/local/obu_group_manager/lib.php');

local_obu_group_manager_create_system_group($courseid);
