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
 * Prints a particular instance of annotate
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage annotate
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace annotate with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/annotate_question_form.php');
require_once(dirname(__FILE__).'/annotate_weight_form.php');

// Pull the aid and/or sid from the url.
$aid = optional_param('aid', 0, PARAM_INT); // annotate ID
$qid = optional_param('qid',0,PARAM_INT);

// Get the annotate activity from the aid.
$annotate = $DB->get_record('annotate', array('id' => $aid));
if (!$annotate) {
  print_error('That annotate activity does not exist!');
}

// Get the question from the qid
$question = $DB->get_record('annotate_question', array('id' => $qid));
if (!question) {
  print_error('That question does not exist!');
}

// Get the course from the annotate object.
$course = $DB->get_record('course', array('id' => $annotate->course));

if ($course->id) {  
  $cm = get_coursemodule_from_instance('annotate', $annotate->id, $course->id, false, MUST_EXIST);
}
else {
  error('Could not find the course / annotate activity!');
}



require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
add_to_log($course->id, 'annotate', 'view', "editquestions.php?aid=$aid", 'Add / Edit Prompts', $cm->id);

require_capability('mod/annotate:edit',$context);


$mform = new annotate_question_form("editquestion.php?aid=$aid&qid=$qid");

if ($updated_question = $mform->get_data()) {
  
  if ($updated_question->type == "M" && !$updated_question->options) {
    $form_error = "A multiple choice question must have answer choices.";
  }
  else {
    $updated_question->options = serialize(array_filter(explode("\r\n",$updated_question->options)));
    $updated_question->id = $qid;
    $DB->update_record('annotate_question',$updated_question);
    redirect("editquestions.php?aid=$aid");
  }
}
$question->options = implode("\r\n",unserialize($question->options));
$mform->set_data($question);


/// Print the page header
$PAGE->set_url('/mod/annotate/editquestion.php', array('aid' => $annotate->id, 'qid' => $question->id));
$PAGE->set_title("Editing Prompt $question->name");
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->js('/mod/annotate/scripts/jquery.min.js');
$PAGE->requires->js('/mod/annotate/scripts/annotate-question-edit.js');
annotate_set_display_type($annotate);

// Output starts here
echo $OUTPUT->header();


echo "<div class='mform'><fieldset class='annotate-add-question-fieldset'><legend>Edit Prompt</legend><div class='annotate-add-question-content'></div>";
if ($form_error) {
  echo "<p class='annotate-form-error'>$form_error</p>";
}

$mform->display();
echo "</div></fieldset>";
echo $OUTPUT->footer();
