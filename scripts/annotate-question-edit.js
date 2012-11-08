/**
 * Shows / hides the answer options textbox based on whether or not this is MC
 * or open response.
 */
$(document).ready( function () {
   annotateShowHideOptions();
   
   $('.annotate-add-question-fieldset #id_type').change(function () {
       annotateShowHideOptions(300);
   });
    
});

function annotateShowHideOptions(duration) {
    
    if (typeof duration === 'undefined') { duration = 0; }
    
    if ($('.annotate-add-question-fieldset #id_type').val() == "M") {
        $('.annotate-add-question-fieldset #id_options').parent().parent().show(duration);
    }
    else {
        $('.annotate-add-question-fieldset #id_options').parent().parent().hide(duration);
    }
}


