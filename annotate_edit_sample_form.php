<?php
/**
 * @file form for editing sames.
 */
require_once($CFG->libdir . "/formslib.php");

class annotate_new_sample_form extends moodleform {
 
    function definition() {
        global $CFG;
 
        $mform =& $this->_form; // Don't forget the underscore! 

        // Pull in custom data.
        $samples = $this->_customdata['samples'];
        $this_sample = $this->_customdata['this_sample'];
       
        // Create a range of letters from A-Z for the sample name drop down
        $letters = range('A','Z');
        $options = array_combine($letters,$letters);
        
        
        // Remove a sample from the drop down if we're making a new sample
        // And the letter is already taken.
        foreach ($samples as $sample) {
          if (isset($options[$sample->name]) && (isset($this_sample) && $this_sample->name != $sample->name) || !isset($this_sample))  {
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
