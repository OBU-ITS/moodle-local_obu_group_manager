<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * OBU Group manager -  local library
 *
 * @package    local_obu_group_manager
 * @author     Joe Souch
 * @category   local
 * @copyright  2024 Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/obu_group_manager/lib.php');
require_once($CFG->libdir . '/grouplib.php');
require_once($CFG->dirroot . '/group/lib.php');

const SYSTEM_IDENTIFIER = 'obuSys';

/**
 * Run synchronization process
 *
 * @param progress_trace $trace
 * @param int|null $courseid or null for all courses
 * @param int|null $courseendafter or null for all courses
 * @return void
 */
function local_obu_group_manager_all_group_sync(progress_trace $trace, $courseid = null, $courseendafter = 0) {
    if ($courseid !== null) {
        $courseids = [$courseid];
    } else {
        $courseids = local_obu_group_manager_get_srs_courses($courseendafter);
    }

    $coursescount = count($courseids);
    $trace->output("Total courses: $coursescount", 1);

    $starttime = time();
    $trace->output("Start at: $starttime", 1);

    foreach ($courseids as $courseid) {
        $parent = get_course($courseid);

        $trace->output($parent->fullname, 1);

        $parentgroupall = local_obu_group_manager_get_all_group($courseid);

        $parentcurrentenrolments = groups_get_members($parentgroupall->id);
        $mappedcurrent = array_combine(array_column($parentcurrentenrolments, 'id'), $parentcurrentenrolments);

        $parentdatabaseenrolments = local_obu_group_manager_get_all_database_enrolled_students($courseid);
        $mappeddatabase = array_combine(array_column($parentdatabaseenrolments, 'id'), $parentdatabaseenrolments);

        foreach ($parentcurrentenrolments as $user) {
            if(!array_key_exists($user->id, $mappeddatabase)) {
                groups_remove_member($parentgroupall, $user);
            }
        }

        foreach ($parentdatabaseenrolments as $user) {
            if(!array_key_exists($user->id, $mappedcurrent)) {
                groups_add_member($parentgroupall->id, $user->id, 'local_obu_group_manager');
            }
        }
    }

    $endtime = time();
    $trace->output("End at: $endtime", 1);
    $duration = $endtime - $starttime;
    $trace->output("Duration: $duration", 1);
}

function local_obu_group_manager_get_srs_courses($courseendafter) {
    global $DB;

    $sql = "SELECT DISTINCT c.id
            FROM {course} c
            JOIN {course_categories} cat ON cat.id = c.category AND cat.idnumber LIKE 'SRS%'
            WHERE c.shortname LIKE '% (%:%)'
                AND c.idnumber LIKE '%.%'
                AND c.enddate > ?";

    $courseidobjs = $DB->get_records_sql($sql, array($courseendafter));

    return array_map(function($n) { return $n->id; }, $courseidobjs);
}

function local_obu_group_manager_get_system_name(string $semester = null, string $set = null) : string
{
    if(!isset($semester) || !isset($set)) return "";

    return "$semester - $set";
}

function local_obu_group_manager_get_system_idnumber(string $courseidnumber, string $semester = null, string $set = null) : string
{
    $suffix = "";
    if(isset($semester)) {
        $suffix .= ".$semester";
    }

    if(isset($set)) {
        $suffix .= ".$set";
    }

    return SYSTEM_IDENTIFIER . ".$courseidnumber" . $suffix;
}

function local_obu_group_manager_apply_prefix($course, $name) : string {
    $prefix = get_config('local_obu_group_manager', 'obusys_group_name_prefix');

    if (empty($name)) {
        return $prefix . $course->shortname;
    }

    if (strpos($name, $prefix) === 0) {
        return $name;
    }

    if (strpos($name, $course->shortname) === 0) {
        return $prefix . $name;
    }

    return $prefix . $course->shortname . ' - ' . $name;
}


function local_obu_group_manager_get_all_group($courseid) {

    $course = get_course($courseid);
    $idnumber = local_obu_group_manager_get_system_idnumber($course->idnumber);

    if(!($group = groups_get_group_by_idnumber($courseid, $idnumber))) {
        $group = local_obu_group_manager_create_system_group($course, null, $idnumber);
    }

    return $group;
}

function local_obu_group_manager_get_all_database_enrolled_students($courseid) : array {
    global $DB;

    $sql = "SELECT DISTINCT u.*
            FROM {enrol} e 
            JOIN {user_enrolments} ue ON e.id = ue.enrolid
            JOIN {user} u ON u.id = ue.userid
            JOIN {role_assignments} ra ON ra.userid = ue.userid
            JOIN {context} c on c.id = ra.contextid AND c.instanceid = e.courseid
            WHERE c.contextlevel = 50 
                AND e.enrol = 'database' 
                AND ra.roleid = 5
                AND e.courseid = ?";

    return $DB->get_records_sql($sql, [$courseid]);
}
