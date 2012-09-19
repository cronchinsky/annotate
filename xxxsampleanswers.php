<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$sid = optional_param('sid', 0, PARAM_INT);

$this_sample = $DB->get_record('annotate_sample', array('id' => $sid));
if (!$this_sample) {
  print_error("That sample doesn't exist!");
}

$annotate = $DB->get_record('annotate', array('id' => $this_sample->aid));
$course = $DB->get_record('course',array('id' => $annotate->course));
$cm = get_coursemodule_from_instance('annotate', $annotate->id, $course->id, false, MUST_EXIST);

if (!$annotate || !$course || !$cm) {
  print_error('Could not find annotate, course, or cm objects!');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
add_to_log($course->id, 'sort', 'view', "sampleanswers.php?sid=$sid", $annotate->name, $cm->id);

$samples = $DB->get_records('annotate_sample', array('aid' => $annotate->id),'name');
$questions = $DB->get_records('annotate_question', array('aid' => $annotate->id), 'weight');
$qids = array_keys($questions);
$answers = $DB->get_records_select('annotate_answer', "sid = $this_sample->id AND qid IN (" . implode(",",$qids) . ") ");

$files = $DB->get_records_select('files', "filesize <> 0 AND component = 'mod_annotate' AND contextid = '$context->id' AND filearea= 'sample' AND itemid = $sid");
foreach ($files as $file) {
  $image_url = annotate_get_image_file_url($file);
}

$has_mc = false;
$has_open = false;
foreach ($questions as &$question) {
 $question->options = unserialize($question->options);
 if ($question->type == 'M') $has_mc = true;
 else $has_open = true;
}

$uids = array();
$answer_index = array(array());
foreach ($answers as $answer) {
  if ($questions[$answer->qid]->type == 'O') {
    $answer_index[$answer->uid][$answer->qid] = $answer;
  }
  else {
    $answer_index[$answer->uid][$answer->qid][$answer->answer] = 1;
  }
  
  $uids[] = $answer->uid;
}

$users = $DB->get_records_list('user','id', $uids);

$PAGE->set_url('/mod/annotate/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($annotate->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->js('/mod/annotate/scripts/jquery.min.js');
$PAGE->requires->js('/mod/annotate/scripts/annotate-table.js');
$PAGE->requires->css('/mod/annotate/css/annotate.css');
annotate_set_display_type($annotate);

echo $OUTPUT->header();

echo "<div class='annotate-sample-pager responses'><h4>Select a student work sample to view responses</h4><ul>";
foreach ($samples as $sample) {
  $class = ($sample->id == $this_sample->id) ? " class='annotate-sample-current'" : "";
  echo "<li$class><a href='sampleanswers.php?sid=$sample->id'>$sample->name</a></li>";
}
echo "</ul>";
echo "</div>";
echo "<div class='annotate-wrapper'>";
echo "<div class='annotate-sample-image'>";
echo "<h4>Student work sample $this_sample->name</h4>";
echo "<img src='$image_url' alt='sample student work' />";
echo "</div>";
//echo "<div class='annotate-sample'><img src='$image_url' alt='student sample' /></div>";

if ($has_open) {
  echo "<h4>Open Ended Responses</h4>";
  echo "<table class='annotate-sample-responses-table annotate-table annotate-open-table'>";
  echo "<tr>";
  echo "<th>Participant</th>";
  echo "<th>Responses</th>";
  echo "</tr>";
  foreach ($users as $user) {
    echo "<tr>";
    echo "<td class='annotate-row-label-user'>$user->username</td>";
    echo "<td>";
      foreach ($questions as &$question) {
        if ($question->type == "O") {
          echo "<div>";
          echo "<a href='#' class='annotate-table-question-label'>$question->prompt</a>";
          echo "<div class='annotate-table-question-response'>" . $answer_index[$user->id][$question->id]->answer . "</div>";
          echo "</div>";
        }
      }
    echo "</td>";
    echo "</tr>";
  }
  echo "</table>";
}
if ($has_mc) {
  echo "<h4>Multiple Choice Responses</h4>";
  echo "<table class='annotate-my-responses-table annotate-table annotate-mc-table'>";
  echo "<tr>";
  echo "<th rowspan='2'>Participant</th>";
  
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
  foreach ($users as $user) {
    echo "<tr>";
    echo "<td class='annotate-row-label-user'>$user->username</td>";
    foreach ($questions as &$question) {
      $option_index = 0;
      foreach ($question->options as $option) {
        if (isset($answer_index[$user->id][$question->id][$option_index]) && $answer_index[$user->id][$question->id][$option_index] == 1) {
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
echo "<span class='annotate-action-link'><a href='sample.php?sid=$this_sample->id'>Back to the sample</a></span>";
echo "<span class='annotate-action-link'><a href='view.php?a=$annotate->id'>Annotate activity index</a></span>";
echo "</div>";
echo "</div>";

echo $OUTPUT->footer();