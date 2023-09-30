
function evaluate_register_inputs($,form)
{
}

function register_validation($,form)
{
  submit = $(form).find('button.submit').prop('disabled',true);

  inputs = $(form).find('input').not('input[type=checkbox]');
  inputs.on(
    'change',
    function() {
      $.post(
        login_vars['ajaxurl'],
        {
          'action':'tlc_ttsurvey',
          'nonce':login_vars['nonce'],
          'username':$(form).find('.input.username input').val(),
          'userid':$(form).find('.input.userid input').val(),
          'password':$(form).find('.input.password input').val(),
          'email':$(form).find('.input.email input').val(),
        },
        function(response) {
          inputs.css('background-color','green');
          setTimeout(function() { inputs.css('background-color','white') }, 1000);
        },
        'json',
      );
      inputs.css('background-color','yellow');
      setTimeout(function() { inputs.css('background-color','white') }, 1000);
    }
  );
}




jQuery(document).ready(
  function($) {
    register_form = $('div#tlc-ttsurvey div.register form')
    if( register_form.length ) { register_validation($,register_form); }
  }
);

