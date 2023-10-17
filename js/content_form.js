
function update_content_form(pid)
{
  alert("update_content_form: " + pid);
}

function validate_survey_input(survey,error,submit)
{
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
        submit.prop('disabled',false);
        survey.removeClass('invalid');
        error.hide();
      } else {
        submit.prop('disabled',true);
        survey.addClass('invalid');
        error.html(response.error).show();
      }
      return response.ok;
    },
    'json',
  );
}

function submit_survey_edits()
{
  alert("submit_survey_edits");
}

jQuery(document).ready(
  function($) {
    const form = $('#tlc-ttsurvey-admin form.content');
    const inputs = form.find('textarea');

    const pid = form.find('input[name=pid]').eq(0).val();

    const editable = form.hasClass('edit');

    /**
     * We're updating the form content here rather than in php to avoid
     * dual maintenance and possible inconsistency that could result from that
     **/

    update_content_form(pid);

    if(!editable) {
      inputs.prop('readonly',true);
      return;
    }

    /**
     * The rest of the setup only applies if the form is editable
     **/

    const lock_info = $('.content .info.lock').eq(0);
    const submit = form.find('input[type=submit]').eq(0);
    const survey = form.find('textarea.survey').eq(0);
    const sendmail = form.find('textarea.sendmail');
    const error = form.find('div.invalid.survey').eq(0);

    var has_lock = form.find('input[name=lock]').eq(0).val() == 0;
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

    /**
     * content validation setup
     **/

     var keyup_timer = null;
     var dirty = false;
     var valid_survey = validate_survey_input(survey,error,submit);

     survey.on('keyup', function() {
       dirty = true;
       submit.prop('disabled',true);
       if(keyup_timer) { clearTimeout(keyup_timer); }
       keyup_timer = setTimeout( function() {
           keyup_timer = null;
           valid_survey = validate_survey_input(survey,error,submit);
         },
         500,
       );
     });

     survey.on('change', function() {
       valid_survey = validate_survey_input(survey,error,submit);
     });

     /**
      * dirty tracking
      **/

      sendmail.on('keyup',function() {
        dirty = true;
        submit.prop('disabled',!valid_survey);
      });

      sendmail.on('change',function() {
        dirty = true;
        submit.prop('disabled',!valid_survey);
      });

     /**
      * form submission
      **/

     form.on('submit', function(event) {
       submit_survey_edits();
       event.preventDefault();
     });
  }
);


