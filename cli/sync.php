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
 * CLI script for performing manual group synchronization
 *
 * @package    local_obu_group_manager
 * @copyright  2024 Joe Souch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

global $CFG;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/local/obu_group_manager/locallib.php');

// Ensure errors are well explained.
set_debugging(DEBUG_DEVELOPER, true);

// Now get cli options.
list($options, $unrecognised) = cli_get_params(
    ['course' => null, 'endafter' => null, 'verbose' => false, 'help' => false],
    ['c' => 'course', 'e' => 'endafter', 'v' => 'verbose', 'h' => 'help']
);

if ($unrecognised) {
    $unrecognised = implode(PHP_EOL . '  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if ($options['help']) {
    $help =
        "Execute initial system all-group synchronization.

This is recommended if installing plugin into a site with existing courses and groups (use the --course switch).

Options:
-c, --course          Course ID (if not specified, then all courses will be synchronized)
-e, --endafter        Courses which end after this date (if not specified, then today's date used)
-v, --verbose         Print verbose progess information
-h, --help            Print out this help

Example:
\$ sudo -u www-data /usr/bin/php local/obu_group_manager/cli/sync.php
\$ sudo -u www-data /usr/bin/php /var/www/poodle/moodle/local/obu_group_manager/cli/sync.php
\$ sudo -u www-data /usr/bin/php local/obu_group_manager/cli/sync.php -c=7 -e=1722294000

Windows
\$ php local/obu_group_manager/cli/sync.php -e=1722294000
";

    echo $help;
    exit(2);
}

if (empty($options['verbose'])) {
    $trace = new null_progress_trace();
} else {
    $trace = new text_progress_trace();
}

$endafter = empty($options['endafter'])
    ? time()
    : (int)$options['endafter'];

local_obu_group_manager_all_group_sync($trace, $options['course'], $endafter);
$trace->finished();

exit(0);
