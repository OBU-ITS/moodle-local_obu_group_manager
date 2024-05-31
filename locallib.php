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

const SYSTEM_IDENTIFIER = 'obuSys';

function local_obu_group_manager_get_system_name(string $courseshortname, string $semester = null, string $set = null) : string
{
    $suffix = "";
    if(isset($semester)) {
        $suffix .= " - $semester";
    }

    if(isset($set)) {
        $suffix .= " - $set";
    }

    return $courseshortname . $suffix;
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
