<?php

/**
 * Example URL : /local/obu_group_manager/test/create_group_on_parent_course.php?courseid=3&parentid=2
 */
require('../../../config.php');

global $CFG;

$courseid = required_param('courseid', PARAM_INT);
$parentid = required_param('parentid', PARAM_INT);

require_once($CFG->dirroot.'/local/obu_group_manager/lib.php');

$parent = get_course($parentid);

local_obu_group_manager_create_system_group(
    $courseid,
    null,
    null,
    null,
    null,
    $parent
);