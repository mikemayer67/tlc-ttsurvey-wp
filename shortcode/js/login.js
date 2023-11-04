var ce = {};

function evaluate_register_inputs()
{
  ce.register_submit.prop('disabled',true);

  const form = ce.register_form;

  data = {
    'action':'tlc_ttsurvey',
    'nonce':login_vars['nonce'],
    'query':'validate_register_form',
    'firstname':form.find('.input.name input.first').val(),
    'lastname':form.find('.input.name input.last').val(),
    'userid':form.find('.input.userid input').val(),
    'password':form.find('.input.password input.primary').val(),
    'pw-confirm':form.find('.input.password input.confirm').val(),
    'email':form.find('.input.email input').val(),
  };

  jQuery.post(
    login_vars['ajaxurl'],
    data,
    function(response) {
      let keys = ['userid','password','name','email'];
      var all_ok = true;
      keys.forEach( function(key) {
        var error_box = form.find('.input .error.'+key);
        var input = form.find('.input.'+key+' input');
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
    },
    'json',
  );
}

function setup_register_validation()
{
  var keyup_timer = null;

  ce.register_submit = ce.register_form.find('button.submit');
  ce.register_error = ce.register_form.find('.error');

  ce.register_submit.prop('disabled',true);
  ce.register_error.hide();

  ce.register_inputs = ce.register_form.find('input').not('input[type=checkbox]');

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
}

function setup_info_triggers()
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
  ce.container = jQuery('#tlc-ttsurvey-login');
  ce.form = ce.container.find('form.login');

  // info trigger/box handling
  ce.info_triggers = ce.form.find('.info-trigger');
  ce.info_boxes = ce.form.find('.info-box');
  ce.info_triggers.show();
  ce.info_boxes.hide();
  if(ce.info_triggers.length) { setup_info_triggers(); }

  // userid/password form
  ce.login_form = ce.container.filter('.login');
  ce.login_recovery_link = ce.login_form.find('div.links div.recovery');
  ce.login_recovery_link.show();

  // recovery form
  ce.recovery = ce.container.filter('.recovery');
  ce.recovery.show();

  // register form
  ce.register_form = ce.container.filter('.register').find('form.login');

  if(ce.register_form.length) { setup_register_validation(); }
}

jQuery(document).ready(
  function($) { 
    setup_elements(); 
  }
);
