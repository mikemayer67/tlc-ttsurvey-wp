
jQuery(document).ready(
  function($) {
    $('.requires-javascript').show();

    var new_survey_toggle = $('form.tlc div.new-survey input.toggle');
    var new_survey_name = $('form.tlc div.new-survey span.name');
    new_survey_toggle.on('change',function() {
      if( new_survey_toggle.is(':checked') ) {
        new_survey_name.show();
      } else {
        new_survey_name.hide();
      }
    });
  }
);
