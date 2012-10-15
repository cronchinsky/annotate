<?php
/**
 * Shows all users responses for this particular sample.
 */


require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

// Load all necessary objects from the sid parameter
$sid = optional_param('sid', 0, PARAM_INT);
$this_sample = $DB->get_record('annotate_sample', array('id' => $sid));
if (!$this_sample) {
  print_error("That sample doesn't exist!");
}
$annotate = $DB->get_record('annotate', array('id' => $this_sample->aid));
$course = $DB->get_record('course', array('id' => $annotate->course));
$cm = get_coursemodule_from_instance('annotate', $annotate->id, $course->id, false, MUST_EXIST);

if (!$annotate || !$course || !$cm) {
  print_error('Could not find annotate, course, or cm objects!');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
add_to_log($course->id, 'sort', 'view', "sampleanswers.php?sid=$sid", $annotate->name, $cm->id);

// Get all samples / questions / answers / files.
$samples = $DB->get_records('annotate_sample', array('aid' => $annotate->id), 'name');
$questions = $DB->get_records('annotate_question', array('aid' => $annotate->id), 'weight');
$qids = array_keys($questions);
$answers = $DB->get_records_select('annotate_answer', "sid = $this_sample->id AND qid IN (" . implode(",", $qids) . ") ");
$files = $DB->get_records_select('files', "filesize <> 0 AND component = 'mod_annotate' AND contextid = '$context->id' AND filearea= 'sample' AND itemid = $sid");
foreach ($files as $file) {
  $image_url = annotate_get_image_file_url($file);
}


// Determine if there are MCs or Open Responses or both.
$has_mc = false;
$has_open = false;
foreach ($questions as $id => $question) {
  $questions[$id]->options = unserialize($questions[$id]->options);
  if ($questions[$id]->type == 'M')
    $has_mc = true;
  else
    $has_open = true;
}

// Create an index of responses keyed on the id of the questions and then the id of the user who's answer it is.
$uids = array();
$answer_index = array(array());
foreach ($answers as $answer) {
  if ($questions[$answer->qid]->type == 'O') {
    $answer_index[$answer->qid][$answer->uid] = $answer;
  }
  else {
    $answer_index[$answer->qid][$answer->uid][$answer->answer] = 1;
  }

  $uids[] = $answer->uid;
}

$users = $DB->get_records_list('user', 'id', $uids);

$PAGE->set_url('/mod/annotate/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($annotate->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->js('/mod/annotate/scripts/jquery.min.js');
$PAGE->requires->js('/mod/annotate/scripts/annotate-table.js');
$PAGE->requires->css('/mod/annotate/css/annotate.css');
$PAGE->requires->css('/mod/annotate/css/jquery-ui.css');
$PAGE->requires->js('/mod/annotate/scripts/jquery.min.js');
$PAGE->requires->js('/mod/annotate/scripts/jquery-ui.min.js');
$PAGE->requires->js('/mod/annotate/scripts/annotate-image-preview.js');
annotate_set_display_type($annotate);

echo $OUTPUT->header();

echo "<div class='annotate-sample-pager responses'><h4>Select " . get_string('samplename','annotate') . " to view responses</h4><ul>";
foreach ($samples as $sample) {
  $class = ($sample->id == $this_sample->id) ? " class='annotate-sample-current'" : "";
  echo "<li$class><a href='sampleanswers.php?sid=$sample->id'>$sample->name</a></li>";
}
echo "</ul>";
echo "</div>";
echo "<div class='annotate-wrapper'>";
echo "<div class='annotate-sample-image'>";
echo "<h4>" . get_string('samplename','annotate') . " $this_sample->name</h4>";

echo "<div class='annotate-sample-image-wrapper'><img src='$image_url' alt='sample student work' />";
echo '<a href="' . $image_url . '" title="View larger image" class="ui-icon ui-icon-magnifying">View larger</a></div>';
echo "</div>";
//echo "<div class='annotate-sample'><img src='$image_url' alt='student sample' /></div>";

// Create the open response table.
if ($has_open) {
  echo "<h4>Open Ended Responses</h4>";
  echo "<table class='annotate-sample-responses-table annotate-table annotate-open-table'>";
  foreach ($questions as $question) {
    if ($question->type == 'O') {
      if (isset($answer_index[$question->id])) {
        echo "<tr><th colspan='2'>$question->prompt</th></tr>";
        echo "<tr><th>Participant</th><th>Response</th></tr>";
        foreach ($answer_index[$question->id] as $uid => $answer) {
          echo "<tr><td class='annotate-row-label'>" . $users[$uid]->username . "</td>";
          echo "<td>" . $answer->answer . "</td>";
          echo "</tr>";
        }
      }
    }
  }
  echo "</table>";
}

// Create the MC table.
if ($has_mc) {
  echo "<h4>Multiple Choice Responses</h4>";
  foreach ($questions as $question) {
    if ($question->type == 'M') {
      echo "<table class='annotate-sample-responses-table annotate-table annotate-mc-table'>";
      echo "<tr><th colspan='" . (sizeof($question->options) + 1) . "'>" . $question->prompt . "</th></tr>";
      echo "<tr>";
      echo "<th>Participant</th>";
      foreach ($question->options as $option) {
        echo "<th>$option</th>";
      }
      if (isset($answer_index[$question->id])) {
        foreach ($answer_index[$question->id] as $uid => $answer) {
          echo "<tr>";
          echo "<td class='annotate-row-label'>" . $users[$uid]->username . "</td>";
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
      }
      echo "</table>";
    }
  }
}
echo "<div class='annotate-action-links'>";
echo "<span class='annotate-action-link'><a href='sample.php?sid=$this_sample->id'>Back to the sample</a></span>";
echo "<span class='annotate-action-link'><a href='view.php?a=$annotate->id'>Annotate activity index</a></span>";
echo "</div>";
echo "</div>";
annotate_add_attribution_line();
echo $OUTPUT->footer();