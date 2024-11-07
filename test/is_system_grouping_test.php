<?php
/**
*   Example URL: /local/obu_group_manager/test/is_system_grouping_test.php?idnumber=obuSys
**/

global $CFG;
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/obu_group_manager/lib.php');

if (!is_siteadmin()) {
    redirect(new \moodle_url('/'));
    die();
}

$idnumber = required_param('idnumber', PARAM_TEXT);

$trace = new \html_progress_trace();
$response = local_obu_group_manager_is_system_grouping($idnumber);
$trace->output(sprintf("Is '$idnumber' a grouping idnumber: %s", $response ? "True" : "False"));
$trace->finished();