<?php
/**
 * @file - page for viewing a summary of your own resposnes.
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

// Load up the necessary objects based on the url parameters
$aid = optional_param('aid', 0, PARAM_INT);
$annotate = $DB->get_record('annotate', array('id' => $aid));
$course = $DB->get_record('course',array('id' => $annotate->course));
$cm = get_coursemodule_from_instance('annotate', $annotate->id, $course->id, false, MUST_EXIST);

if (!$annotate || !$course || !$cm) {
  print_error('Could not find annotate, course, or cm objects!');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
add_to_log($course->id, 'sort', 'view', "myanswers.php?aid=$annotate->id", $annotate->name, $cm->id);


// Load all of the samples tied to this annotate
$samples = $DB->get_records('annotate_sample', array('aid' => $annotate->id),'name');

// Load all of the prompts tied to this annotate
$questions = $DB->get_records('annotate_question', array('aid' => $annotate->id), 'weight');
$qids = array_keys($questions);
if (!$questions) {
  print_error("This activity has no prompts yet!");
}

// Get all of this users responses.
$answers = $DB->get_records_select('annotate_answer', "uid = $USER->id AND qid IN (" . implode(",",$qids) . ") ");

// Determine if the prompts contain any MC's or any Open Response types
$has_mc = false;
$has_open = false;
foreach ($questions as $id => $question) {
 $questions[$id]->options = unserialize($questions[$id]->options);
 if ($questions[$id]->type == 'M') $has_mc = true;
 else $has_open = true;
}

// Set up an array of answers keysed by the question id, and then the sample id.
$answer_index = array(array());
foreach ($answers as $answer) {
  if ($questions[$answer->qid]->type == 'O') {
    $answer_index[$answer->qid][$answer->sid] = $answer;
  }
  else {
    $answer_index[$answer->qid][$answer->sid][$answer->answer] = 1;
  }
}

$PAGE->set_url('/mod/annotate/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($annotate->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->css('/mod/annotate/css/annotate.css');
$PAGE->requires->js('/mod/annotate/scripts/jquery.min.js');
$PAGE->requires->js('/mod/annotate/scripts/annotate-table.js');
annotate_set_display_type($annotate);

echo $OUTPUT->header();

// If we have open response prompts, print a table of resposnes.
if ($has_open) {
  echo $OUTPUT->heading('Class Chart');
  echo "<div class='annotate-wrapper'>";
  echo '<h4>Open Ended Responses</h4>';
  echo "<table class='annotate-my-responses-table annotate-table annotate-open-table'>";
  foreach ($questions as $question) {
    if ($question->type == 'O') {
      echo "<tr>";
      echo "<th colspan='2' class='annotate-row-label'>$question->prompt</th></tr><tr>";
      echo "<th>" . get_string('samplename','annotate') . "</th>";
      echo "<th>Responses</th>";
      echo "</tr>";
      foreach ($answer_index[$question->id] as $sid => $answer) {
        echo "<tr><td class='annotate-row-label'>";
        echo $samples[$sid]->name;
        echo "</td><td>";
        echo $answer->answer;
        echo "</td></tr>";
      }
    }
  }
  echo "</table>";
}

// If we have MC types, print a table of responses.
if ($has_mc) {

  echo '<h4>Multiple Choice Responses</h4>';
  foreach ($questions as $question ) {
    if ($question->type == 'M') {
      echo "<table class='annotate-my-responses-table annotate-table annotate-mc-table'>";
      echo "<tr><th colspan='" . (sizeof($question->options) + 1) . "'>" . $question->prompt . "</th></tr>";
      echo "<tr>";
      echo "<th>" . get_string('samplename','annotate') . "</th>";
      foreach ($question->options as $option) {
        echo "<th>$option</th>";
      }
      foreach ($answer_index[$question->id] as $sid => $answer) {
        echo "<tr>";
        echo "<td class='annotate-row-label'>" . $samples[$sid]->name . "</td>";
        foreach ($question->options as $index => $option) {
          if (isset($answer[$index])) {
            echo "<td>Y</td>";
          }
          else {
            echo "<td></td>";
          }
        }
        echo "</tr>";
      }
      echo "</table>";
    }
  }
}
echo "<div class='annotate-action-links'>";
echo "<span class='annotate-action-link'><a href='view.php?a=$annotate->id'>Annotate activity index</a></span>";
echo "</div>";
echo "</div>";

annotate_add_attribution_line();

echo $OUTPUT->footer();