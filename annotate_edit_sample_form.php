<?php


require_once($CFG->libdir . "/formslib.php");

class annotate_new_sample_form extends moodleform {
 
    function definition() {
        global $CFG;
 
        $mform =& $this->_form; // Don't forget the underscore! 
        
        $samples = $this->_customdata['samples'];
        $this_sample = $this->_customdata['this_sample'];
       
        $letters = range('A','Z');
        $options = array_combine($letters,$letters);
        
        foreach ($samples as $sample) {
          if (isset($options[$sample->name]) && isset($this_sample) && $this_sample->name != $sample->name) {
              unset($options[$sample->name]);
          }
        }
        
        

        $mform->addElement('select', 'samplename', 'Name', $options);
        $mform->addRule('samplename', 'This field is required', 'required');
        
        $mform->addElement('filemanager', 'attachments', 'Sample image', null,
                    array('subdirs' => 0, 'maxbytes' => 33554432, 'maxfiles' => 1,
                          ));
        $mform->addRule('attachments', 'This field is required', 'required');
        $this->add_action_buttons(false);
    }                           
}                               
