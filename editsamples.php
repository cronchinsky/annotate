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
 * Creates new pieces of student work and edits existing ones.
 *
 * @package    mod
 * @subpackage annotate
 * @copyright  2012 EdTech Leaders Online
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

// Include the edit form.
require_once(dirname(__FILE__) . '/annotate_edit_sample_form.php');

// Pull the aid and/or sid from the url.
$aid = optional_param('aid', 0, PARAM_INT); // annotate ID
$sid = optional_param('sid', 0, PARAM_INT); // sample ID

// Get the annotate activity from the aid.
$annotate = $DB->get_record('annotate', array('id' => $aid));
if (!$annotate) {
  print_error('That annotate activity does not exist!');
}

// Get the annotate activity, course, etc from the problem.
$course = $DB->get_record('course', array('id' => $annotate->course));
if ($course->id) {
  $cm = get_coursemodule_from_instance('annotate', $annotate->id, $course->id, false, MUST_EXIST);
}
else {
  error('Could not find the course / annotate activity!');
}

// Moodley goodness.
require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
add_to_log($course->id, 'annotate', 'view', "editsamples.php?aid=$aid", $annotate->name, $cm->id);

// Set the page header.
$PAGE->set_url('/mod/annotate/editsamples.php', array('aid' => $aid, 'sid' => $sid));
$PAGE->set_title(format_string("Editing Sample"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->add_body_class('annotate-sample-edit-form');

// annotate CSS styles.
$PAGE->requires->css('/mod/annotate/css/annotate.css');

annotate_set_display_type($annotate);

// Only editors can see this page.
require_capability('mod/annotate:edit', $context);

// All student samples for the activity.
$samples = $DB->get_records('annotate_sample', array('aid' => $annotate->id),'name');
$sample = NULL;

// If there's a sid in the url, we're editing an exisitng sample
if ($sid != 0) {
  // Get a piece of existing student work to load in the draft area
  $sample = $DB->get_record('annotate_sample', array('id' => $sid));
  // If there is no sample, the sid is funky.
  if (!$sample) {
    print_error('Can not find any samples that match that ID');
  }
  // This helps with the form.  samplename is the form element's name
  $sample->samplename = $sample->name;
}


// Load the form.
$mform = new annotate_new_sample_form("/mod/annotate/editsamples.php?aid=$aid&sid=$sid", array('samples' => $samples, 'this_sample' => $sample));

// If the form was cancelled, redirect.
if ($mform->is_cancelled()) {
  redirect("annotate.php?id=$aid");
}
else {
  
  if ($sample) {
  //Set up the draft area.
  $draftitemid = file_get_submitted_draft_itemid('attachments');
  file_prepare_draft_area($draftitemid, $context->id, 'mod_annotate', 'sample', $sample->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 50));

  // This also helps with the form, since attachments is the form element name.
  $sample->attachments = $draftitemid;

  // Put the existing data into the form.
  $mform->set_data($sample);
  }
  // If there's data in the form...
  if ($results = $mform->get_data()) {
    
    // If the the data is for a new sample...
    if ($sid == 0) {
      // Save the new sample as a new record.
      $sample->aid = $aid;
      $sample->name = $results->samplename;
      $sample = $DB->insert_record('annotate_sample', $sample);
      file_save_draft_area_files($results->attachments, $context->id, 'mod_annotate', 'sample', $sample);
    }
    else {
      // We're updaing an existing sample.
      $sample->name = $results->samplename;
      $updated_record = $DB->update_record('annotate_sample', $sample);
      file_save_draft_area_files($results->attachments, $context->id, 'mod_annotate', 'sample', $sample->id);
    }
    // Now redirect back to this page with the updated data.
    redirect("editsamples.php?aid=$aid");
  }
}



// Begin page output
echo $OUTPUT->header();
echo $OUTPUT->heading("Manage Student Work Samples for {$annotate->name}");

echo "<div class='annotate-wrapper'>";
echo "<div class='annotate-sample-pager'>";
echo "<h3>Select a sample to edit, or click \"Add New\" to create a new sample to annotate.</h3>";
echo "<ul class='annotate-sample-pager'>";
foreach ($samples as $sample) {
  $class = ($sid == $sample->id) ? "class=\"annotate-pager-current\"" : ""; 
  echo '<li ' . $class . '><a href="' . $CFG->wwwroot . '/mod/annotate/editsamples.php?aid=' . $annotate->id . '&amp;sid=' . $sample->id . '">' . $sample->name . '</a></li>';
}
$class = (!$sid) ? ' class="annotate-pager-current" ' : "";
echo '<li' . $class . '><a href="' . $CFG->wwwroot . '/mod/annotate/editsamples.php?aid=' . $annotate->id . '">Add New</a></li>';
echo "</ul>";
echo "</div>";
echo "<div class='annotate-manage-form-wrapper'>";
if ($sid) echo "<p class='annotate-delete-link'><a href='deletesample.php?sid=$sid'>Delete this sample</a></p>";
if ($sid) echo "<h4>Editing sample $sample->name:</h4>";
else echo "<h4>Adding a new sample:</h4>";



//displays the form
$mform->display();

echo "</div>";
echo "</div>";

echo "<div class='annotate-action-links'>";
echo "<span class='annotate-action-link'><a href='view.php?a=$annotate->id'>Back to the Activity</a></span>";
echo "</div>";
// Finish the page
echo $OUTPUT->footer();









