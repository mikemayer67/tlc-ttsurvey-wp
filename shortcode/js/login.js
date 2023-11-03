var ce = {};

function evaluate_register_inputs()
{
  submit = ce_register.find('button.submit').prop('disabled',true);
  submit.prop('disabled',true);

  data = {
    'action':'tlc_ttsurvey',
    'nonce':login_vars['nonce'],
    'query':'validate_register_form',
    'firstname':ce_register.find('.input.name input.first').val(),
    'lastname':ce_register.find('.input.name input.last').val(),
    'userid':ce_register.find('.input.userid input').val(),
    'password':ce_register.find('.input.password input.primary').val(),
    'pw-confirm':ce_register.find('.input.password input.confirm').val(),
    'email':ce_register.find('.input.email input').val(),
  };

  jQuery.post(
    login_vars['ajaxurl'],
    data,
    function(response) {
      let keys = ['userid','password','name','email'];
      var all_ok = true;
      keys.forEach( function(key) {
        var error_box = ce.register_form.find('.input .error.'+key);
        var input = ce.register_form.find('.input.'+key+' input');
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

function setup_register_validation()
{
  var keyup_timer = null;

  ce.register_form.find('button.submit').prop('disabled',true);
  ce.register_form.find('.error').hide()

  submit = ce.register_form.find('button.submit').prop('disabled',true);

  inputs = ce.register_form.find('input').not('input[type=checkbox]');

  inputs.on('input',function() {
    submit.prop('disabled',true);
    if(keyup_timer) { clearTimeout(keyup_timer); }
    keyup_timer = setTimeout( function() {
        keyup_timer = null;
        evaluate_register_inputs();
      },
      500,
    );
  });
}

jQuery(document).ready(
  function($) {
    ce.register_form = $('div#tlc-ttsurvey div.register form');
    ce.sendlogin_content = $('div#tlc-ttsurvey div.sendlogin');

    ce.sendlogin_content.show();
    
    $('div#tlc-ttsurvey-login form.login div.sendlogin').show();

    if( ce.register_form.length ) { setup_register_validation(); }
  }
);

