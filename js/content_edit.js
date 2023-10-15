
function validate_content()
{
  $ = jQuery;
  var inputs = $('form.edit-survey textarea');
  var submit = $('form.edit-survey input[type=submit]');

  data = {
    'action':'tlc_ttsurvey',
    'nonce':edit_vars['nonce'],
    'query':'validate_content_form',
  };

  inputs.each(function() {
    data[this.className] = this.value;
  });

  $.post(
    edit_vars['ajaxurl'],
    data,
    function(response) {
      elements = ['survey','welcome'];
      elements.forEach( function(key,index) {
        ta = $('form.edit-survey textarea.'+key);
        ed = $('form.edit-survey div.invalid.'+key);
        if( key in response ) {
          ta.addClass('invalid');
          ed.html(response[key]).show();
        }
        else
        {
          ta = $('form.edit-survey textarea.'+key);
          ta.removeClass('invalid');
          ed.hide();
        }
      })
      if(response.ok) {
        submit.prop('disabled',false);
      }
    },
    'json',
  );
}

jQuery(document).ready(
  function($) {
    var lock = $('input[name=lock]');
    var pid = $('input[name=pid]');
    var submit = $('form.edit-survey input[type=submit]');
    var inputs = $('form.edit-survey textarea');
    var lock_info = $('#tlc-ttsurvey-admin .content .info.lock');

    if(lock.val() == 0) {
      inputs.prop('readonly',false);
    }

    var keyup_timer = null;
    inputs.on('keyup',function() {
      submit.prop('disabled',true);
      if(keyup_timer) { clearTimeout(keyup_timer); }
      keyup_timer = setTimeout( function() {
          keyup_timer = null;
          validate_content();
        },
        1000,
      )
    });

    inputs.on('change',function(event,data) {
      validate_content();
    });
  }
);
