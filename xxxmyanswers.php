<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

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

$samples = $DB->get_records('annotate_sample', array('aid' => $annotate->id),'name');
$questions = $DB->get_records('annotate_question', array('aid' => $annotate->id), 'weight');
$qids = array_keys($questions);
if (!$questions) {
  print_error("This activity has no prompts yet!");
}
$answers = $DB->get_records_select('annotate_answer', "uid = $USER->id AND qid IN (" . implode(",",$qids) . ") ");

$has_mc = false;
$has_open = false;
foreach ($questions as &$question) {
 $question->options = unserialize($question->options);
 if ($question->type == 'M') $has_mc = true;
 else $has_open = true;
}

$answer_index = array(array());
foreach ($answers as $answer) {
  if ($questions[$answer->qid]->type == 'O') {
    $answer_index[$answer->sid][$answer->qid] = $answer;
  }
  else {
    $answer_index[$answer->sid][$answer->qid][$answer->answer] = 1;
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
if ($has_open) {
  echo $OUTPUT->heading('Class Chart');
  echo "<div class='annotate-wrapper'>";
  echo '<h4>Open Ended Responses</h4>';
  echo "<table class='annotate-my-responses-table annotate-table annotate-open-table'>";
  echo "<tr>";
  echo "<th>Student Work Sample</th>";
  echo "<th>Responses</th>";
  echo "</tr>";
  foreach ($samples as $sample) {
    echo "<tr>";
    echo "<td class='annotate-row-label'><a href='sample.php?sid=$sample->id'>$sample->name</a></td>";
    echo "<td>";
      foreach ($questions as &$question) {
        if ($question->type == "O") {
          echo "<div>";
          echo "<a href='#' class='annotate-table-question-label'>$question->prompt</a>";
          echo "<div class='annotate-table-question-response'>";
          if (isset($answer_index[$sample->id][$question->id]->answer)) {
            echo $answer_index[$sample->id][$question->id]->answer;
          }
          echo "</div>";
          
          echo "</div>";
        }
      }
    echo "</td>";
    echo "</tr>";
  }
  echo "</table>";
}
if ($has_mc) {
  echo '<h4>Multiple Choice Responses</h4>';
  echo "<table class='annotate-my-responses-table annotate-table annotate-mc-table'>";
  echo "<tr>";
  echo "<th rowspan='2'>Student Work Sample</th>";
  
  foreach ($questions as &$question) {
    if ($question->type == "M") {
      echo "<th colspan='" . sizeof($question->options) . "'>$question->prompt</th>";
    }
  }
  echo "</tr>";
  echo "<tr>";
  foreach ($questions as &$question) {
    if ($question->type == "M") {
      foreach ($question->options as $option) {
        echo "<th>" . $option . "</th>";
      }
    }
  }
  echo "</tr>";
  foreach ($samples as $sample) {
    echo "<tr>";
    echo "<td class='annotate-row-label'><a href='sample.php?sid=$sample->id'>$sample->name</a></td>";
    foreach ($questions as &$question) {
      $option_index = 0;
      foreach ($question->options as $option) {
        if (isset($answer_index[$sample->id][$question->id][$option_index]) && $answer_index[$sample->id][$question->id][$option_index] == 1) {
          echo "<td class='yes'>Y</td>";
        }
        else {
          echo "<td></td>";
        }
        $option_index++;
      }
    }
    
    echo "</tr>";
  }
  echo "</table>";
}
echo "<div class='annotate-action-links'>";
echo "<span class='annotate-action-link'><a href='view.php?a=$annotate->id'>Annotate activity index</a></span>";
echo "</div>";
echo "</div>";

echo $OUTPUT->footer();