<?php
echo "GO";
require_once("../../config.php");
$id   = required_param('id', PARAM_INT); // Course module ID

$PAGE->set_url('/mod/annotate/grade.php', array('id'=>$id));

if (! $cm = get_coursemodule_from_id('annotate', $id)) {
    print_error('invalidcoursemodule');
}

if (! $annotate = $DB->get_record("annotate", array("id"=>$cm->instance))) {
    print_error('invalidid', 'annotate');
}

if (! $course = $DB->get_record("course", array("id"=>$annotate->course))) {
    print_error('coursemisconf', 'annotate');
}

require_login($course, false, $cm);

if (has_capability('mod/annotate:edit', get_context_instance(CONTEXT_MODULE, $cm->id))) {
    redirect('showgrades.php?id='.$cm->id);
} else {
    redirect('view.php?id='.$cm->id);
}