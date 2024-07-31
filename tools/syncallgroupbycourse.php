<?php

require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once("$CFG->libdir/formslib.php");

admin_externalpage_setup('groupmanagertaskssynccourse');

global $PAGE, $OUTPUT;

$PAGE->set_heading("Sync Course All Group(s)");
echo $OUTPUT->header();

class syncallgroup_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'courseid', 'Course ID');
        $mform->setType('courseid', PARAM_NOTAGS);
        $mform->addRule('courseid', null, 'required', null, 'client');

        $mform->addElement('submit', 'resyncbutton', 'Submit');
    }
}

function reSyncAllGroup($courseid) {
    $task = new \local_obu_group_manager\task\synchronisebycourse();
    $task->set_custom_data(['courseid' => $courseid]);

    \core\task\manager::queue_adhoc_task($task);
}

$mform = new syncallgroup_form();

if ($data = $mform->get_data()) {
    $datacourseid = (int)$data->courseid;
    reSyncAllGroup($datacourseid);

    \core\notification::info("Ad hoc task created");

} else{
    if ($mform->is_submitted() && !$mform->is_validated()){
        \core\notification::error("Course ID required.");
    }
}

$mform->display();

echo $OUTPUT->footer();