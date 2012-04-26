$(document).ready(function () { 
    
   $('.annotate-table-question-response').hide();
   $('.annotate-table-question-label')
   .each(function () {
       if ($(this).next('.annotate-table-question-response').html() != '') {
           $(this).addClass('annotate-expandable');
       }
   })
   .click( function () {
       $(this).next('.annotate-table-question-response').toggle(400);
       if ($(this).hasClass('annotate-expanded')) {
           $(this).removeClass('annotate-expanded');
       }
       else {
           $(this).addClass('annotate-expanded');
       }
       return false;
   });
});