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

namespace local_obu_group_manager\task;

/**
 * Adhoc task to perform group synchronization
 *
 * @package    local_obu_group_manager
 * @copyright  2024 Joe Souch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/obu_group_manager/locallib.php');

class synchronisebycourse extends \core\task\adhoc_task {

    public function execute() {
        $courseid = get_course($this->get_custom_data()->courseid);

        $trace = new \text_progress_trace();
        local_obu_group_manager_all_group_sync($trace, $courseid);
        $trace->finished();
    }
}
