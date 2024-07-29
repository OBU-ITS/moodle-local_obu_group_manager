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
 * Standard lib
 *
 * @package    obu_group_manager
 * @author     Joe Souch
 * @copyright  2024, Oxford Brookes University {@link http://www.brookes.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage(get_string('pluginname', 'local_obu_group_manager'), get_string('plugintitle', 'local_obu_group_manager'));
    $ADMIN->add('localplugins', $settings);
    $settings->add(new admin_setting_configtextarea(
        'local_obu_group_manager/obusys_group_desc',
        get_string('obusysgroupdescname', 'local_obu_group_manager'),
        get_string('obusysgroupdescdesc', 'local_obu_group_manager'),
        ''));
    $settings->add(new admin_setting_configtext(
        'local_obu_group_manager/obusys_group_name_prefix',
        get_string('groupnameprefix', 'local_obu_group_manager'),
        get_string('groupnameprefixdesc', 'local_obu_group_manager'),
        '&#9888; (DO NOT EDIT) '));
}