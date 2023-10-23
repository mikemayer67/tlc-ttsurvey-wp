

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


jQuery(document).ready(
  function($) {
    const ns_form = $('form.new-survey');
    const ns_new_name = ns_form.find('input.new-name'); 
    const ns_error = ns_form.find('span.error');
    const ns_submit = ns_form.find('input.submit');

    hold_lock();
    setInterval(hold_lock,15000);

    ns_new_name.on('keyup',function() {
      ns_existing_names = ns_form.find('input.existing')[0].value;
      ns_existing_names = JSON.parse(ns_existing_names);
      new_name = ns_new_name.val()
      err = "";
      if(new_name.length<4) {
        err = "too short";
      }
      else if($.inArray(new_name,ns_existing_names)>=0) {
        err = "existing survey";
      }
      else if(!/^[a-zA-Z0-9., -]+$/.test(new_name)) {
        err = "invalid name";
      }
      ns_submit.prop('disabled',err.length>0);
      ns_error.html(err);
    });
  }
);

