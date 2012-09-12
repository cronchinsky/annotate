<?php



require_once($CFG->libdir . "/formslib.php");

class annotate_weight_form extends moodleform {
 
    function definition() {
         global $CFG;
        
        $mform =& $this->_form; // Don't forget the underscore! 
        $questions = $this->_customdata['questions'];
        
        
        $range = range(-50,50);
        $options = array_combine($range,$range);
        
        if (!empty($questions)) {
          $index = 1;
          foreach ($questions as $question) {
            annotate_add_question_to_form($question,$mform, $index);
            $mform->addElement('html',"<div class='annotate-question-edit-actions'><a href='editquestions.php?deleteid=$question->id&aid=$question->aid'>Delete</a> | <a href='editquestion.php?qid=$question->id&aid=$question->aid'>Edit</a>");
            $mform->setDefault("weight-$question->id",$question->weight);
            $mform->addElement('select',"weight-$question->id",'Weight',$options);
            $mform->addElement('html',"</div>");
            $index++;
          }
          $this->add_action_buttons(false, "Update Weights");
        }
    }                           
}                               
