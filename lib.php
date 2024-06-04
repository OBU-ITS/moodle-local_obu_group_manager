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
 * OBU Group manager -  library
 *
 * @package    local_obu_group_manager
 * @author     Joe Souch
 * @category   local
 * @copyright  2024 Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/local/obu_group_manager/locallib.php');


function local_obu_group_manager_create_system_group($courseorid,
                                                     string $name = null,
                                                     string $idnumber = null,
                                                     string $semester = null,
                                                     string $set = null) {
    global $DB;

    if((isset($semester) || isset($set)) && !(isset($semester) && isset($set))) {
        throw new coding_exception("Both semester and set are required when provided.");
    }

    $course = is_object($courseorid) ? $courseorid : get_course($courseorid);

    $idnumber = $idnumber ?? local_obu_group_manager_get_system_idnumber($course->idnumber, $semester, $set);

    if (!($group = $DB->get_record('groups', ['courseid' => $course->id, 'idnumber' => $idnumber]))) {
        $group->courseid = $course->id;
        $group->name = $name ?? local_obu_group_manager_get_system_name($course->shortname, $semester, $set);
        $group->idnumber = $idnumber;
        $group->id = groups_create_group($group);

        local_obu_group_manager_link_system_grouping($group);
    }

    return $group;
}

function local_obu_group_manager_link_system_grouping($group) : bool {
    $idnumber = trim(SYSTEM_IDENTIFIER);

    if (!($grouping = groups_get_grouping_by_idnumber($group->courseId, $idnumber))) {

        $grouping = new stdClass();
        $grouping->name = get_string('groupingname', 'local_obu_group_manager');
        $grouping->courseid = $group->courseid;
        $grouping->idnumber = $idnumber;

        $grouping->id = groups_create_grouping($grouping);

    }

    groups_assign_grouping($grouping->id, $group->id);

    return true;
}

function local_obu_group_manager_is_system_group($idnumber) : bool {
    return substr( $idnumber, 0, 6 ) === SYSTEM_IDENTIFIER;
}