import * as validate from './validation.js';

var ce = {}

function hold_lock()
{
  jQuery.post(
    form_vars.ajaxurl,
    {
      action:'tlc_ttsurvey',
      nonce: form_vars.nonce,
      query: 'admin/obtain_content_lock',
    },
    function(response) {
      if(!response.has_lock) {
        window.location.reload(true);
      }
    },
    'json',
  );
}

function handle_change()
{
  var existing_names = ce.form.find('input.existing').eq(0).val();
  existing_names = JSON.parse(existing_names);

  const new_name = ce.new_name.val()

  var err = "";
  const result = validate.survey_name(new_name);
  if(!result.ok) {
    err = result.error;
  }
  else if(jQuery.inArray(new_name,existing_names)>=0) {
    err = "existing survey";
  }

  ce.error.html(err);
  ce.submit.prop('disabled',err.length>0);
}


function handle_new_survey(event)
{
  event.preventDefault();

  new_name = ce.new_name.val();

  jQuery.post(
    form_vars['ajaxurl'],
    {
      action:'tlc_ttsurvey',
      nonce:form_vars['nonce'],
      query:'admin/new_survey',
      name:new_name,
    },
    function(response) {
      if(response.success) {
        window.location.reload(true);
      }
    },
    'json',
  );
}


jQuery(document).ready(
  function($) {
    ce.form = $('form.new-survey');
    ce.new_name = ce.form.find('input.new-name'); 
    ce.error = ce.form.find('span.error');
    ce.submit = ce.form.find('input.submit');

    hold_lock();
    setInterval(hold_lock,15000);

    ce.new_name.on('keyup',handle_change);
    ce.form.on('submit',handle_new_survey);
  }
);

