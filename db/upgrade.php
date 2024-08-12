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
 * OBU Application - Database upgrade
 *
 * @package    obu_group_manager
 * @category   local
 * @author     Joe Souch
 * @copyright  2024, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
global $CFG;
require_once($CFG->dirroot.'/group/lib.php');

function xmldb_local_obu_group_manager_upgrade($oldversion = 0) {
    global $DB;
    $dbman = $DB->get_manager();

    $result = true;

    if ($oldversion < 2024072901) {
        $sql = "UPDATE {groups}
                SET name = CONCAT('⚠ (DO NOT EDIT) ', name)
                WHERE idnumber LIKE 'obuSys.%'
                    AND name not like '⚠ (DO NOT EDIT) %'";

        $DB->execute($sql);

        upgrade_plugin_savepoint(true, 2024072901, 'local', 'obu_group_manager');
    }

    if ($oldversion < 2024081201) {
        $sql = "SELECT g.id
                FROM {groups} g
                JOIN {course} c ON c.id = g.courseid
                JOIN {course_categories} cat ON cat.id = c.category AND cat.idnumber NOT LIKE 'SRS%'
                WHERE g.idnumber LIKE 'obuSys.%' 
                AND c.shortname LIKE '% (%:%)'
                AND c.idnumber LIKE '%.%'";

        $groups = $DB->get_records_sql($sql);
        foreach($groups as $group) {
            groups_delete_group($group->id);
        }

        upgrade_plugin_savepoint(true, 2024081201, 'local', 'obu_group_manager');
    }

    return $result;
}