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
$delete_id = optional_param('deleteid',0,PARAM_INT);

// If there's a delete_id, that means we're deleting a record
if ($delete_id && $aid) {
  $DB->delete_records('annotate_question', array('id' => $delete_id));
  redirect("editquestions.php?aid=$aid");
}

// Get the annotate activity from the aid.
$annotate = $DB->get_record('annotate', array('id' => $aid));
if (!$annotate) {
  print_error('That annotate activity does not exist!');
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

/// Print the page header
$PAGE->set_url('/mod/annotate/editquestions.php', array('aid' => $cm->id));
$PAGE->set_title("Manage Prompts for $annotate->name");
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->js('/mod/annotate/scripts/jquery.min.js');
$PAGE->requires->js('/mod/annotate/scripts/annotate-question-edit.js');
$PAGE->requires->css('/mod/annotate/css/annotate.css');
annotate_set_display_type($annotate);

// Only editors should be able to do this.
require_capability('mod/annotate:edit',$context);

// Pull down all existing questions tied to this problem.
$questions = $DB->get_records('annotate_question', array('aid' => $annotate->id), 'weight');


// Initialize the question weight form
$mform_weight = new annotate_weight_form("editquestions.php?aid=$annotate->id", array('questions'=>$questions));

// Check for data and adjust weights if the weight form has been submitted.
if ($weight_results = $mform_weight->get_data()) {
  foreach ($weight_results as $key => $result) {
    list($result_type,$qid) = explode('-',$key);
    if ($result_type == "weight") {
      if ($questions[$qid]->weight != $result) {
        $questions[$qid]->weight = $result;
        $DB->update_record('annotate_question',$questions[$qid]);
      }
    }
  }
  redirect("editquestions.php?aid=$annotate->id");
}

// Initialize the question form
$mform_question = new annotate_question_form("editquestions.php?aid=$annotate->id");

// Check for data and apply appropriate action if we have a new question.
if ($new_question = $mform_question->get_data()) {
  $new_question->aid = $annotate->id;
  
  if (empty($new_question->options) && $new_question->type == 'M') {
    $form_error = "You must enter some answer choices for a multiple choice question.";
  }
  else {
    $new_question->options = serialize(array_filter(explode("\r\n",$new_question->options)));

    $DB->insert_record('annotate_question',$new_question);
    redirect("editquestions.php?aid=$annotate->id");
  }
}


// Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading("Manage Prompts for $annotate->name");

$mform_weight->display();



if (isset($form_error) && $form_error != "") {
  echo "<p class='annotate-form-error'>$form_error</p>";
}

echo "<div class='mform'><fieldset class='annotate-add-question-fieldset'><legend>Add a New Prompt</legend><div class='annotate-add-question-content'></div>";
$mform_question->display();
echo "</div></fieldset>";

echo "<div class='annotate-action-links'>";
echo "<span class='annotate-action-link'><a href='view.php?a=$annotate->id'>Back to the Activity</a></span>";
echo "</div>";

annotate_add_attribution_line();

// Finish the page
echo $OUTPUT->footer();
