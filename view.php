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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // annotate instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('annotate', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $annotate  = $DB->get_record('annotate', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($a) {
    $annotate  = $DB->get_record('annotate', array('id' => $a), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $annotate->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('annotate', $annotate->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

$samples = $DB->get_records('annotate_sample', array('aid' => $annotate->id),'name');

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'annotate', 'view', "view.php?id={$cm->id}", $annotate->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/annotate/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($annotate->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->css('/mod/annotate/css/annotate.css');
annotate_set_display_type($annotate);




// Output starts here
echo $OUTPUT->header();


// Replace the following lines with you own code
echo $OUTPUT->heading($annotate->name);
echo "<div class='annotate-wrapper'>";
if ($annotate->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('annotate', $annotate, $cm->id), 'generalbox mod_introbox', 'annotateintro');
}
echo "<div class='annotate-sample-pager'>";
echo "<h4>Select a " . get_string('samplename','annotate') . " to annotate</h4>";
if ($samples) {
  echo "<ul>";
  foreach ($samples as $sample) {
    echo "<li><a href='sample.php?sid=$sample->id'>$sample->name</a></li>";
  }
  echo "</ul>";
}
else {
  echo "<p>There are no samples in this activity yet!</p>";
}
echo "</div>";

echo "</div>";
echo "<div class='annotate-action-links'>";
echo "<span class='annotate-action-link'><a href='myanswers.php?aid=$annotate->id'>My class chart</a></span>";
if (has_capability('mod/annotate:edit', $context)) {
  echo "<span class='annotate-action-link'><a href='editsamples.php?aid=$annotate->id'>Manage " . get_string('samplename_plural','annotate') . "</a></span>";
}
if (has_capability('mod/annotate:edit', $context)) {
  echo "<span class='annotate-action-link'><a href='editquestions.php?aid=$annotate->id'>Manage prompts</a></span>";
}

echo "</div>";
// Finish the page
annotate_add_attribution_line();
echo $OUTPUT->footer();
