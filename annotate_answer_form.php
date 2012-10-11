<?php
/**
 * @file form for submitting annotations.
 */


require_once($CFG->libdir . "/formslib.php");

class annotate_answer_form extends moodleform {
 
    function definition() {
         global $CFG;
        
        $mform =& $this->_form; // Don't forget the underscore! 
        
        // Pull in the prompts.
        $questions = $this->_customdata['questions'];
        
        // Add each question to the form.
        if (!empty($questions)) {
          $index = 1;
          foreach ($questions as $question) {
            annotate_add_question_to_form($question,$mform, $index);
            $index++;
          }
          $this->add_action_buttons(false, "Submit Responses");
        }
    }                           
}                               
