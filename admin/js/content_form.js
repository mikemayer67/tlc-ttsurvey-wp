
var has_lock = false;
var valid_survey = true;
var survey_error = '';
var dirty = false;

var form = null;
var form_status = null;
var lock_info = null;
var inputs = null;
var sendmail = null;
var survey = null;
var error = null;
var submit = null;

function prep_for_change()
{
  dirty = true;
  submit.prop('disabled',true);
  form_status.hide();
  update_state();
}

function update_state()
{
  can_submit = has_lock && valid_survey && dirty;
  submit.prop('disabled', !can_submit);

  if(valid_survey) {
    survey.removeClass('invalid');
    error.hide();
  } else {
    survey.addClass('invalid');
    error.html(survey_error).show();
  }
}

function update_content_form(pid)
{
  dirty = false;
  form_status.hide();
  update_state();

  jQuery.post(
    form_vars['ajaxurl'],
    {
      'action':'tlc_ttsurvey',
      'nonce':form_vars['nonce'],
      'query':'populate_content_form',
      'pid':pid,
    },
    function(response) {
      if(response.ok) {
        form.find('textarea.survey').html(response.survey);

        for(const key in response.sendmail) {
          form.find('textarea.'+key).html(response.sendmail[key].md);
          form.find('.sendmail.preview.'+key).html(response.sendmail[key].html);
        }
      }
      validate_survey_input(pid);
    },
    'json',
  );
}

function validate_survey_input(pid)
{
  if(!form_vars['editable']) { return; }

  jQuery.post(
    form_vars['ajaxurl'],
    {
      'action':'tlc_ttsurvey',
      'nonce':form_vars['nonce'],
      'query':'validate_content_form',
      'survey':survey[0].value,
    },
    function(response) {
      if(response.ok) {
        valid_survey = true;
        survey_error = '';
      } else {
        valid_survey = false;
        survey_error = response.error;
      }
      update_state();
    },
    'json',
  );
}

function refresh_sendmail_preview(template)
{
  const preview = form.find('.sendmail.preview.' + template);
  const markdown = form.find('textarea.' + template).val();

  jQuery.post(
    form_vars['ajaxurl'],
    {
      'action':'tlc_ttsurvey',
      'nonce':form_vars['nonce'],
      'query':'render_sendmail_template',
      'markdown':markdown,
    },
    function(response) {
      if(response.ok) {
        preview.html(response.rendered);
      }
    },
    'json',
  );
}


jQuery(document).ready(
  function($) {
    form = $('#tlc-ttsurvey-admin form.content');
    form_status = $('#tlc-ttsurvey-admin .tlc-status');
    lock_info = $('.content .info.lock').eq(0);

    inputs = form.find('textarea');
    survey = form.find('textarea.survey').eq(0);
    sendmail = form.find('textarea.sendmail');
    error = form.find('div.invalid.survey').eq(0);
    submit = form.find('input[type=submit]').eq(0);

    const pid = form.find('input[name=pid]').eq(0).val();
    const editable = form_vars['editable'];

    form_status.hide();

    //------------------------------------------------------------
    // We're updating the form content here rather than in php to avoid
    // dual maintenance and possible inconsistency that could result from that
    //------------------------------------------------------------

    update_content_form(pid);

    if(!editable) {
      inputs.prop('readonly',true);
      return;
    }

    //------------------------------------------------------------
    // The rest of the setup only applies if the form is editable
    //------------------------------------------------------------

    has_lock = form.find('input[name=lock]').eq(0).val() == 0;
    inputs.prop('readonly',!has_lock);

    $(document).on( 'heartbeat-send', function(event,data) {
      // send the pid and the current lock value
      //   if we have the lock (lock==0), renew it
      //   if we don't have the lock (lock!=0), try to obtain it
      data.tlc_ttsurvey_lock = {
        'pid':pid, 
        'action': (has_lock ? 'renew' : 'watch'),
      };
    });

    $(document).on( 'heartbeat-tick', function(event,data) {
      if(has_lock) {
        // if we already have the lock, we no longer need to wait for it
        // to become available.
        return;
      }
      const rc = data.tlc_ttsurvey_lock;
      if(!rc.got_lock) {
        // we failed to get the lock.. keep waiting
        return;
      }

      // we just acquired the lock
      has_lock = true;

      // update the form accordingly
      lock_info.html("You may now edit the survey content.");
      lock_info.removeClass('lock').addClass('unlocked');

      update_content_form(pid);
      inputs.prop('readonly',false);
    });

    //------------------------------------------------------------
    // content validation setup
    //------------------------------------------------------------

    var keyup_timer = null;

    survey.on('keyup', function() {
      prep_for_change();
      if(keyup_timer) { clearTimeout(keyup_timer); }
      keyup_timer = setTimeout( function() {
          keyup_timer = null;
          validate_survey_input(pid);
        },
        500,
      );
    });

    survey.on('change', function() {
      prep_for_change();
      validate_survey_input(pid);
    });

    //------------------------------------------------------------
    // sendmail template tracking
    //------------------------------------------------------------

    sendmail.on('keyup',function() {
      prep_for_change();
      template = this.name;
      if(keyup_timer) { clearTimeout(keyup_timer); }
      keyup_timer = setTimeout( function() {
          keyup_timer = null;
          refresh_sendmail_preview(template);
          validate_survey_input(pid);
        },
        500,
      );
    });

    sendmail.on('change',function() {
      prep_for_change();
      refresh_sendmail_preview(this.name);
    });

    //------------------------------------------------------------
    // form submission
    //------------------------------------------------------------

    form.on('submit', function(event) {
      prep_for_change();
      event.preventDefault();
      data = {
        'action':'tlc_ttsurvey',
        'nonce':form_vars['nonce'],
        'query':'submit_content_form',
        'pid':pid,
        'content':{},
      };
      inputs.each(function() {
        data.content[this.name] = this.value;
      });
      $.post(
        form_vars['ajaxurl'],
        data,
        function(response) {
          if(response.ok) {
            form_status.html('updated').addClass('info').show();
            dirty = false;
          } else {
            alert("failed to save content: " + response.error);
          }
          update_state();
        },
        'json',
      );
    });
  }
);


