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
 * @return void
 */
function local_obu_group_manager_all_group_sync(progress_trace $trace, $courseid = null, $courseendafter = 0) {
    if ($courseid !== null) {
        $courseids = [$courseid];
    } else {
        $courseids = local_obu_group_manager_get_srs_courses($courseendafter);
    }

    foreach ($courseids as $courseid) {
        $parent = get_course($courseid);

        $trace->output($parent->fullname, 1);

        $parentgroupall = local_obu_group_manager_get_all_group($courseid);
        $parentcurrentenrolments = groups_get_members($parentgroupall->id);
        foreach ($parentcurrentenrolments as $user) {
            groups_remove_member($parentgroupall, $user);
        }
        $parentdatabaseenrolments = local_obu_group_manager_get_all_database_enrolled_students($courseid);
        foreach ($parentdatabaseenrolments as $user) {
            groups_add_member($parentgroupall->id, $user->id, 'local_obu_group_manager');
        }

        $children = local_obu_metalinking_child_courses($parent->id);
        foreach ($children as $childid) {
            $childgroupall = local_obu_group_manager_get_all_group($childid);
            $childcurrentenrolments = groups_get_members($childgroupall->id);
            foreach ($childcurrentenrolments as $user) {
                groups_remove_member($childgroupall, $user);
            }
            $childdatabaseenrolments = local_obu_group_manager_get_all_database_enrolled_students($childid);
            foreach ($childdatabaseenrolments as $user) {
                groups_add_member($childgroupall->id, $user->id, 'local_obu_group_manager');
            }
        }
    }
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

    $fullname = $prefix . $course->shortname;
    if(!empty($name)) {
        $fullname .= " - $name";
    }

    return $fullname;
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
