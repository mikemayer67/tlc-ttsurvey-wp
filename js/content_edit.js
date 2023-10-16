
function validate_content()
{
  $ = jQuery;
  var survey = $('form.edit-survey textarea.survey');
  var survey_error = $('form.edit-survey div.invalid.survey');
  var submit = $('form.edit-survey input[type=submit]');

  data = {
    'action':'tlc_ttsurvey',
    'nonce':edit_vars['nonce'],
    'query':'validate_content_form',
    'survey':survey[0].value,
  };

  $.post(
    edit_vars['ajaxurl'],
    data,
    function(response) {
      if(response.ok) {
        submit.prop('disabled',false);
        survey.removeClass('invalid');
        survey_error.hide();
      } else {
        submit.prop('disabled',true);
        survey.addClass('invalid');
        survey_error.html(response.error).show();
      }
    },
    'json',
  );
}

jQuery(document).ready(
  function($) {
    var lock = $('input[name=lock]');
    var submit = $('form.edit-survey input[type=submit]');
    var inputs = $('form.edit-survey textarea');
    var survey = $('form.edit-survey textarea.survey');

    if(lock.val() == 0) {
      inputs.prop('readonly',false);
    }

    var keyup_timer = null;
    survey.on('keyup',function() {
      submit.prop('disabled',true);
      if(keyup_timer) { clearTimeout(keyup_timer); }
      keyup_timer = setTimeout( function() {
          keyup_timer = null;
          validate_content();
        },
        500,
      )
    });

    survey.on('change',function(event,data) {
      validate_content();
    });
  }
);
