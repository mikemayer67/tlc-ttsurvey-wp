var ce = {};

function ajax_query( query, data, response_handler )
{
  data.action = 'tlc_ttsurvey';
  data.nonce = login_vars.nonce;
  data.query = 'shortcode/' + query;

  jQuery.post(login_vars.ajaxurl, data, response_handler, 'json');
}

//----------------------------------------
// Userid/Password Login Form
//----------------------------------------

function login_form_setup()
{
  ce.login_inputs = ce.login_form.find('input');
  ce.login_resume_buttons = ce.login_form.find('button.resume');
  ce.login_forget = ce.login_form.find('.forget a');

  ce.login_userid = ce.login_inputs.filter('[name=userid]');
  ce.login_password = ce.login_inputs.filter('[name=password]');
  ce.login_remember = ce.login_inputs.filter('[name=remember]');

  ce.login_recovery_link = ce.login_form.find('div.links-bar div.recovery');
  ce.login_recovery_link.show();

  ce.login_inputs.on('input',function() {
    ce.status_message.hide(400,'linear');
  });

  ce.login_resume_buttons.on('click',login_with_token);
  ce.login_form.on('submit',login_with_password);
  ce.login_forget.on('click',forget_userid);
}

function login_with_token(event)
{
  event.preventDefault();
  ajax_query( 'login_with_token',
    { 'token':jQuery(this).val() },
    login_response_handler,
  );
}

function login_with_password(event)
{
  event.preventDefault();

  ajax_query(
    'login_with_password',
    {
      userid:ce.login_userid.val(),
      password:ce.login_password.val(),
      remember:ce.login_remember.is(':checked'),
    },
    login_response_handler,
  );
}

function login_response_handler(response)
{
  if(response.success) {
    response.cookies.forEach(function(cookie) {
      const key = cookie[0];
      const value = cookie[1];
      const expires = 1000*cookie[2];
      var new_cookie = key + '=' + value + '; path=/';
      if(expires > 0) {
        new_cookie += '; ' + (new Date(expires)).toUTCString();
      }
      document.cookie = new_cookie;
    });
    window.location.href = login_vars.survey_url;
  } else {
    ce.status_message.removeClass(['info','error']).addClass('warning');
    ce.status_message.html(response.error);
    ce.status_message.show(200,'linear');
  }
}

function forget_userid(event)
{
  event.preventDefault();
  ajax_query(
    'forget_userid',
    { userid:this.dataset.userid },
    login_response_handler,
  );
}

//----------------------------------------
// New User Registration Form
//----------------------------------------

function register_setup()
{
  var keyup_timer = null;

  ce.register_userid = ce.register_form.find('.input.userid input');
  ce.register_password = ce.register_form.find('.input.password input.primary');
  ce.register_pwconfirm = ce.register_form.find('.input.password input.confirm');
  ce.register_fullname = ce.register_form.find('.input.fullname input');
  ce.register_email = ce.register_form.find('.input.email input');
  ce.register_remember = ce.register_form.find('input[name=remember]');
  ce.register_submit = ce.register_form.find('button.submit');
  ce.register_cancel = ce.register_form.find('button.cancel');
  ce.register_error = ce.register_form.find('.error');

  ce.register_submit.prop('disabled',true);
  ce.register_error.hide();

  ce.register_inputs = ce.register_form.find('input').not('input[type=checkbox]');
  ce.register_inputs.val('');

  ce.register_inputs.on('input',function() {
    ce.register_submit.prop('disabled',true);
    if(keyup_timer) { clearTimeout(keyup_timer); }
    keyup_timer = setTimeout( function() {
        keyup_timer = null;
        evaluate_register_inputs();
      },
      500,
    );
  });

  ce.register_cancel.on('click',cancel_register);
  ce.register_form.on('submit',register_new_user);
  ce.register_inputs.val('');
}

function cancel_register(event)
{
  event.preventDefault();
  window.location.href = login_vars.survey_url;
}


function evaluate_register_inputs()
{
  ajax_query(
    'validate_register_form', 
    {
      userid:ce.register_userid.val(),
      password:ce.register_password.val(),
      pwconfirm:ce.register_pwconfirm.val(),
      fullname:ce.register_fullname.val(),
      email:ce.register_email.val(),
    },
    function(response) {
      let keys = ['userid','password','fullname','email'];
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
      ce.register_submit.prop("disabled",!all_ok);
    }
  );
}

function register_new_user(event)
{
  event.preventDefault();

  ajax_query(
    'register_new_user',
    {
      userid:ce.register_userid.val(),
      password:ce.register_password.val(),
      pwconfirm:ce.register_pwconfirm.val(),
      fullname:ce.register_fullname.val(),
      email:ce.register_email.val(),
      remember:ce.register_remember.is(':checked'),
    },
    login_response_handler,
  );
}

//----------------------------------------
// Login Info Recovery Form
//----------------------------------------

function recovery_setup()
{
  ce.recovery_form = ce.recovery.find('form.login');
  ce.recovery_email = ce.recovery_form.find('input[name=email]');
  ce.recovery_submit = ce.recovery_form.find('button.submit');
  ce.recovery_cancel = ce.recovery_form.find('button.cancel');
  ce.recovery_error = ce.recovery_form.find('.error');

  ce.recovery_error.hide();
  ce.recovery.show();

  ce.recovery_cancel.on('click',cancel_recovery);
  ce.recovery_form.on('submit',send_recovery_email)

  ce.recovery_email.on('input',function() {
    ce.status_message.hide(400,'linear')
  });

}

function cancel_recovery(event)
{
  event.preventDefault();
  window.location.href = login_vars.survey_url;
}

function send_recovery_email(event)
{
  event.preventDefault();

  localStorage.removeItem('pwreset');

  const email = ce.recovery_email.val()

  ajax_query(
    'send_recovery_email', 
    {
      email:email,
    },
    function(response) {
      if(response.success) {
        pwreset = {
          expires: response.data.expires,
          tokens: response.data.tokens,
        }
        localStorage.pwreset = JSON.stringify(pwreset);
        ce.input_status.val("info::Login info sent to "+email);
        ce.recovery_form.off('submit');
        ce.recovery_submit.click();
      }
      if(response.data.error) {
        var [level,msg] = response.data.error.split('::');
        ce.status_message.removeClass(['info','warning','error'])
        ce.status_message.html(msg).addClass(level).show(200,'linear');
      }
    }
  );
}


//----------------------------------------
// Password Reset Form
//----------------------------------------

var pwreset_user_info = {}

function pwreset_failure(error)
{
  const status = encodeURIComponent("warning::"+error);
  const new_url = login_vars.survey_url+'&status=' + status;
  window.location.href = new_url;
}
function pwreset_setup()
{
  var keyup_timer = null;

  ce.pwreset_form = ce.pwreset.find('form.login');
  ce.pwreset_submit = ce.pwreset_form.find('button.submit');
  ce.pwreset_cancel = ce.pwreset_form.find('button.cancel');
  ce.pwreset_error = ce.pwreset_form.find('.input .error');
  ce.pwreset_userid_error = ce.pwreset_form.find('.input.userid .error');
  ce.pwreset_password_error = ce.pwreset_form.find('.input.password .error');
  ce.pwreset_inputs = ce.pwreset_form.find('.input input');
  ce.pwreset_userid = ce.pwreset_inputs.filter('[name=userid]');
  ce.pwreset_password = ce.pwreset_inputs.filter('[type=password]');

  ce.pwreset_error.hide();

  const reset_token = ce.pwreset_form.find('input[name=reset_token]').val();
  const server_time = Number(
    ce.pwreset_form.find('input[name=server_time]').val()
  );

  const pwreset = JSON.parse(localStorage.pwreset ?? "{}");
  const reset_tokens = pwreset.tokens ?? {};
  pwreset_user_info = reset_tokens[reset_token] ?? null;
  if(!pwreset_user_info) {
    pwreset_failure('Password recovery link not found');
  }
  if(server_time > pwreset.expires) {
    pwreset_failure("Password reset link expired");
  }
  
  ce.pwreset_password.on('input', function() {
    if(keyup_timer) { clearTimeout(keyup_timer) }
    keyup_timer = setTimeout( function() {
        keyup_timer = null;
        pwreset_validate_password();
      },
      500,
    )
  });

  ce.pwreset_inputs.on('input',function() {
    ce.status_message.hide(400,'linear');
  });

  ce.pwreset_cancel.on('click',cancel_pwreset);
  ce.pwreset_form.on('submit',send_pwreset);
}

function cancel_pwreset(event)
{
  event.preventDefault();
  window.location.href = login_vars.survey_url;
}

function send_pwreset(event)
{
  event.preventDefault();

  if(!pwreset_validate_password()) { return }

  const userid = ce.pwreset_userid.val();
  if( userid !== pwreset_user_info.userid ) {
    ce.status_message.removeClass(['info','warning']).addClass('error');
    ce.status_message.html('Incorrect userid for password reset link');
    ce.status_message.show(200,'linear');
    return;
  }

  ajax_query(
    'reset_password',
    {
      userid:userid,
      token:pwreset_user_info.reset,
      password:ce.pwreset_password.val(),
    },
    function(response) {
      localStorage.removeItem('pwreset');
    
      var status = '';
      if(response.success) {
        const fullname = pwreset_user_info.fullname;
        status = encodeURIComponent('info::password updated for '+fullname);
      } else {
        status = encodeURIComponent('warning::'+response.data);
      }
    
      window.location.href = login_vars.survey_url+'&status='+status;
    }
  );
}

function pwreset_validate_password()
{
  const password = ce.pwreset_password.get(0).value;
  const confirm = ce.pwreset_password.get(1).value;

  error = null;
  if( password.length == 0 ) { 
    error = '';
  } else if( password.length < 8 ) {
    error = 'too short';
  } else if( password.length > 128 ) {
    error = 'too long';
  } else if( !/[a-zA-Z]/.test(password) ) {
    error = 'must contain at least one letter';
  } else if(bad=/[^a-zA-Z0-9 !@%^*_=~,.-]/.exec(password)) {
    error = "cannot contain '" + bad + "'";
  } else if (confirm.length == 0) {
    error = "missing password confirmation";
  } else if (confirm !== password) {
    error = "passwords don't match";
  }

  if(error === null) {
    ce.pwreset_password_error.hide();
    return true;
  } else {
    ce.pwreset_password_error.html(error).show();
    return false;
  }
}



//----------------------------------------
// Common Login Forms Setup
//----------------------------------------

function info_setup()
{
  var info_trigger_timer = null;

  ce.info_triggers.each(
    function() {
      var trigger = jQuery(this)
      var tgt_id = trigger.data('target');
      var tgt = jQuery('#'+tgt_id);
      trigger.on('mouseenter', function(e) {
        if(info_trigger_timer) { 
          clearTimeout(info_trigger_timer); 
          info_trigger_timer=null;
        }
        info_trigger_timer = setTimeout(function() {tgt.slideDown(100)}, 500);
      });
      trigger.on('mouseleave',function(e) {
        if(info_trigger_timer) {
          clearTimeout(info_trigger_timer); 
          info_trigger_timer=null;
        }
        if(!tgt.hasClass('locked')) { tgt.slideUp(100) } 
      });
      trigger.on( 'click', function() { 
        if(tgt.hasClass('locked')) {
          tgt.removeClass('locked').slideUp(100)
        } else {
          tgt.addClass('locked').slideDown(100)
        }
      });

      tgt.on( 'click', function() { tgt.removeClass('locked').slideUp(100) });
    }
  );
}

function setup_elements()
{
  // clear old elements (needed for AJAX repopulation of login form)
  for( const key in ce ) { ce.off(); }
  ce = {};

  // populate the elements
  ce.status_message = jQuery('#tlc-ttsurvey #status-message');
  ce.container = jQuery('#tlc-ttsurvey #login');
  ce.form = ce.container.find('form.login');
  ce.input_status = ce.form.find('input[name=status]');

  // info trigger/box handling
  ce.info_triggers = ce.form.find('.info-trigger');
  ce.info_boxes = ce.form.find('.info-box');
  ce.info_triggers.show();
  ce.info_boxes.hide();
  if(ce.info_triggers.length) { info_setup(); }

  // userid/password form
  ce.login_form = ce.container.filter('.login');
  if(ce.login_form.length) { login_form_setup(); }

  // recovery form
  ce.recovery = ce.container.filter('.recovery');
  if(ce.recovery.length) { recovery_setup(); }

  // pwreset form
  ce.pwreset = ce.container.filter('.pwreset');
  if(ce.pwreset.length) { pwreset_setup(); }

  // register form
  ce.register_form = ce.container.filter('.register').find('form.login');
  if(ce.register_form.length) { register_setup(); }
}

jQuery(document).ready(
  function($) { 
    setup_elements(); 
  }
);
