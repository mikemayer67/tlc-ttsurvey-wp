var ce = {};

function handle_form_submit(event)
{
  event.preventDefault();

  var url = '';
  var data = {
    'action':'tlc_ttsurvey',
    'nonce':form_vars['nonce'],
    'query':'submit_settings_form',
  };

  const inputs = ce.form.serializeArray();
  for(const input of inputs) {
    data[input.name] = input.value;
  }

  jQuery.post(
    form_vars['ajaxurl'],
    data,
    function(response) {
      if(response.ok) {
        const url = form_vars.overview;
        window.location.href = url;
      } else {
        alert("failed to save settings: " + response.error);
      }
    },
    'json',
  );
}

jQuery(document).ready(
  function($) {
    ce.form = $('#tlc-ttsurvey-admin div.settings form');

    ce.form.on('submit', handle_form_submit);
  }
);
