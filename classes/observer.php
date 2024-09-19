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
 * Event observers
 *
 * @package    local_obu_group_manager
 * @copyright  2024 Joe Souch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

require_once($CFG->dirroot . '/local/obu_group_manager/locallib.php');

class local_obu_group_manager_observer {

    /**
     * User enrolment created
     *
     * @param \core\event\user_enrolment_created $event
     * @return bool
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event) {
        $enabled = get_config('local_obu_group_manager', 'enableevents');
        if(!$enabled) {
            return false;
        }

        if($event->other['enrol'] != 'database') {
            return false;
        }

        $user = \core_user::get_user($event->relateduserid, '*', MUST_EXIST);
        $group = local_obu_group_manager_get_all_group($event->courseid);

        groups_add_member($group, $user);

        return true;
    }
}
