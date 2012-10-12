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


$sid = optional_param('sid', 0, PARAM_INT); // sample ID
$confirm = optional_param('confirm', 0, PARAM_INT); // student work ID


// Get the sample from the sid
$sample = $DB->get_record('annotate_sample', array('id' => $sid));
if (!$sample) {
  print_error('That student work does not exist.  It cannot be deleted');
}

// Get the annotate activity, course, etc from the sample.
$annotate = $DB->get_record('annotate', array('id' => $sample->aid));
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
add_to_log($course->id, 'annotate', 'view', "deletesample.php?sid=$sid", "Deleting student work", $cm->id);


// Only editors can see this page.
require_capability('mod/annotate:edit', $context);


if ($confirm && $sid) {
  $DB->delete_records('annotate_answer', array('sid' => $sid));
  $DB->delete_records('annotate_sample', array('id' => $sid));
  redirect("editsamples.php?aid=$annotate->id");
}

// Set the page header.
$PAGE->set_url('/mod/annotate/deletesample.php', array('sid' => $sid));
$PAGE->set_title(format_string("Deleting Sample"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->add_body_class('annotate-sample-delete');
annotate_set_display_type($annotate);
// annotate CSS styles.
$PAGE->requires->css('/mod/annotate/css/annotate.css');

// Begin page output
echo $OUTPUT->header();



echo $OUTPUT->confirm("Are you sure you want to delete sample $sample->name from $annotate->name?  Any participant data will be lost.","deletesample.php?sid=$sid&confirm=1","editsamples.php?aid=$annotate->id&sid=$sid");

echo $OUTPUT->footer();










