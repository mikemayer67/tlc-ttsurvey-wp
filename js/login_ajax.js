
function evaluate_register_inputs($,form)
{
  submit = $(form).find('button.submit').prop('disabled',true);
  submit.prop('disabled',true);

  data = {
    'action':'tlc_ttsurvey',
    'nonce':login_vars['nonce'],
    'query':'validate_register_form',
    'firstname':$(form).find('.input.name input.first').val(),
    'lastname':$(form).find('.input.name input.last').val(),
    'userid':$(form).find('.input.userid input').val(),
    'password':$(form).find('.input.password input.primary').val(),
    'pw-confirm':$(form).find('.input.password input.confirm').val(),
    'email':$(form).find('.input.email input').val(),
  };

  $.post(
    login_vars['ajaxurl'],
    data,
    function(response) {
      let keys = ['userid','password','name','email'];
      all_ok = true;
      keys.forEach( function(key) {
        error_box = form.find('.input .error.'+key);
        input = form.find('.input.'+key+' input');
        input.removeClass(['invalid','empty']);
        if( key in response ) {
          all_ok = false;
          if(response[key]==="#empty") {
            error_box.hide();
            error_box.html("");
            input.addClass('empty');
          } else {
            error_box.show();
            error_box.html(response[key]);
            input.addClass('invalid');
          }
        } else {
          error_box.hide();
          error_box.html("");
        }
      });
      submit.prop("disabled",!all_ok);
    },
    'json',
  );
}

function setup_register_validation($,form)
{
  var keyup_timer = null;

  $(form).find('button.submit').prop('disabled',true);
  $(form).find('.error').hide()

  submit = $(form).find('button.submit').prop('disabled',true);

  inputs = $(form).find('input').not('input[type=checkbox]');
  inputs.on('change', function() {
    if(keyup_timer) {
      clearTimeout(keyup_timer);
      keyup_timer = null;
    }
    evaluate_register_inputs($,form);
  });

  inputs.on('keyup',function() {
    submit.prop('disabled',true);
    if(keyup_timer) { clearTimeout(keyup_timer); }
    keyup_timer = setTimeout( function() {
        keyup_timer = null;
        evaluate_register_inputs($,form);
      },
      500,
    );
  });
}

jQuery(document).ready(
  function($) {
    register_form = $('div#tlc-ttsurvey div.register form')

    if( register_form.length ) { 
      setup_register_validation($,register_form);
    }
  }
);

