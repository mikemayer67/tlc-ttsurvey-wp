
var ce = {}

function hold_lock()
{
  jQuery.post(
    form_vars.ajaxurl,
    {
      action:'tlc_ttsurvey',
      nonce: form_vars.nonce,
      query: 'obtain_content_lock',
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

  err = "";
  if(new_name.length<4) {
    err = "too short";
  }
  else if(jQuery.inArray(new_name,existing_names)>=0) {
    err = "existing survey";
  }
  else if(!/^[a-zA-Z0-9., -]+$/.test(new_name)) {
    err = "invalid name";
  }

  ce.error.html(err);
  ce.submit.prop('disabled',err.length>0);
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
  }
);

