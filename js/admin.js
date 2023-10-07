
jQuery(document).ready(
  function($) {
    $('.requires-javascript').show();

    var new_survey_select = $('form.tlc.new-survey select');
    var new_survey_name = $('form.tlc.new-survey span.new-name');
    var new_survey_submit = $('form.tlc.new-survey input.submit');
    new_survey_select.on('change',function() {
      var v = new_survey_select.val();
      if( v == "create" ) {
        new_survey_name.show();
        new_survey_submit.show();
      } else if(v) {
        new_survey_name.hide();
        new_survey_submit.show();
      } else {
        new_survey_submit.hide();
        new_survey_name.hide();
      }
    });
  }
);
