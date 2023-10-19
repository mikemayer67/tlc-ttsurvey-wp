var has_edit_lock = false;
var survey_error = null;
var current_content = null;

var ajax_lock = false;
var queue_timer = null;
var queue = [];

var pid = null;
var ce = {};


function update_content_form()
{
  ce.form_status.hide();

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
        ce.survey.val(response.survey);

        for(const key in response.sendmail) {
          ce.form.find('textarea.'+key).val(response.sendmail[key].md);
          ce.form.find('.sendmail.preview.'+key).html(response.sendmail[key].html);
        }

        current_content = {
          survey: response.survey,
          sendmail: response.sendmail,
        };
      }
    },
    'json',
  );
}

function reset_queue()
{
  console.log("reset_queue");
  queue.length=0
  if(queue_timer) {
    clearTimeout(queue_timer);
    queue_timer=null;
  }
  ajax_lock=false;
}

function add_change_event_to_queue()
{
  event = {input:this.name, type:'change'};
  console.log("add_change_event_to_queue: event="+JSON.stringify(event)+" ajax_lock="+ajax_lock+" queue:"+JSON.stringify(queue));

  // fire immediately if all queues are empty and no ajax lock
  if( queue.length == 0 && !ajax_lock ) {
    queue.push(event);
    run_queue_task();
    return;
  }

  // we're done if there's already an event on the queue for the current input
  if( queue.filter( e => e.input == event.input).length ) { return; }

  // add the event to the queue
  queue.push(event);

  // start the timer if it's not already running
  if(!queue_timer) {
    queue_timer = setTimeout(run_queue_task,2500);
  }
}

function add_keyup_event_to_queue()
{
  event = {input:this.name, type:'keyup'};
  console.log("add_change_event_to_queue: event="+JSON.stringify(event)+" ajax_lock="+ajax_lock+" queue:"+JSON.stringify(queue));

  // get events on the queue for the current input (count should be 0 or 1)
  input_queue = queue.filter( e => e.input == event.input );

  // we're done if there is already a change event on queue for current event
  if( input_queue.filter( e => e.type == 'change' ).length ) { 
    return; 
  }

  // replace any (keyup) event on queue for current input
  queue = queue.filter( e => e.input != event.input ) 
  queue.push(event);

 // start the timer if it's not already running
 if(!queue_timer) {
   queue_timer = setTimeout(run_queue_task,500);
 }
}

function run_queue_task()
{
  console.log("run_queue_task: queue="+JSON.stringify(queue)+" timer="+queue_timer+" ajax_lock="+ajax_lock);
  // clear the queue timer if it is running
  if( queue_timer ) {
    clearTimeout(queue_timer);
    queue_timer = null;
  }

  if(!ajax_lock) {
    // pick the event to run off of the queue
    event = queue.shift();
    if(!event) { return; }

    // take the appropriate action based on event input
    //   the action will include an ajax call
    //   the ajax lock must be cleared in the action response handler
    ajax_lock = true;
    if( event.input == "survey" ) {
      validate_survey_input();
    } else {
      refresh_sendmail_preview(event.input);
    }
  }

  // restrt the queue timer
  if(queue.length) {
    queue_timer = setTimeout( run_queue_task, 2500 );
  }
}

function validate_survey_input()
{
  console.log("validate_survey_input");
  jQuery.post(
    form_vars['ajaxurl'],
    {
      'action':'tlc_ttsurvey',
      'nonce':form_vars['nonce'],
      'query':'validate_content_form',
      'survey':ce.survey.eq(0).val(),
    },
    function(response) {
      console.log("validate_survey_input response="+JSON.stringify(response));
      survey_error = response.ok ? null : response.error;
      ajax_lock = false;
      update_state();
    },
    'json',
  );
}

function refresh_sendmail_preview(template)
{
  console.log("refresh_sendmail_preview");
  const preview = ce.form.find('.sendmail.preview.' + template);
  const markdown = ce.form.find('textarea.' + template).val();

  jQuery.post(
    form_vars['ajaxurl'],
    {
      'action':'tlc_ttsurvey',
      'nonce':form_vars['nonce'],
      'query':'render_sendmail_template',
      'markdown':markdown,
    },
    function(response) {
      console.log("refressh_sendmail_preview response="+JSON.stringify(response));
      if(response.ok) {
        preview.html(response.rendered);
        ajax_lock = false;
        update_state();
      }
    },
    'json',
  );
}


function handle_heartbeat_send(event,data) 
{
  // send the pid and the current lock value
  //   if we have the lock (lock==0), renew it
  //   if we don't have the lock (lock!=0), try to obtain it
  data.tlc_ttsurvey_lock = {
    'pid':pid, 
    'action': (has_edit_lock ? 'renew' : 'watch'),
  };
}

function handle_heartbeat_tick(event,data) 
{
  if(has_edit_lock) {
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
  has_edit_lock = true;

  // update the form accordingly
  ce.lock_info.html("You may now edit the survey content.");
  ce.lock_info.removeClass('lock').addClass('unlocked');

  update_content_form();
  ce.inputs.prop('readonly',false);
}


function content_has_changed()
{
  var rval = false;
  if(current_content) {
    if(current_content.survey != ce.survey.val()) {
      rval = true;
    } else {
      const sendmail = current_content.sendmail;
      ce.sendmail.each(function() {
        if( this.value != sendmail[this.name].md ) { rval = true; }
      });
    }
  }
  return rval;
}

function update_state()
{
  console.log("update_state: queue="+JSON.stringify(queue)+" ajax_lock="+ajax_lock);
  const has_change = content_has_changed();

  ce.submit.prop('disabled', !(has_edit_lock && has_change) || survey_error );
  ce.revert.prop('disabled', !(has_edit_lock && has_change) );

  if(survey_error) {
    ce.survey.addClass('invalid');
    ce.error.html(survey_error).show();
  } else {
    ce.survey.removeClass('invalid');
    ce.error.hide();
  }
}


function handle_form_submit(event)
{
  console.log("handle_form_submit");
  event.preventDefault();

  data = {
    'action':'tlc_ttsurvey',
    'nonce':form_vars['nonce'],
    'query':'submit_content_form',
    'pid':pid,
    'content':{},
  };

  ce.inputs.each(function() {
    data.content[this.name] = this.value;
  });

  jQuery.post(
    form_vars['ajaxurl'],
    data,
    function(response) {
      console.log("handle_form_submit response="+JSON.stringify(response));
      if(response.ok) {
        ce.form_status.html('updated').addClass('info').show();
        current_content = jQuery(true,{},data.content);
        reset_queue();
      } else {
        alert("failed to save content: " + response.error);
      }
    },
    'json',
  );
}

function handle_form_revert(event)
{
  console.log("handle_form_revert");
  event.preventDefault();

  ce.survey.val(current_content.survey);
  for(const key in current_content.sendmail) {
    ce.form.find('textarea.'+key).val(current_content.sendmail[key].md);
    ce.form.find('.sendmail.preview.'+key).html(current_content.sendmail[key].html);
  }
  update_state();
  reset_queue();
}


jQuery(document).ready(
  function($) {
    ce.form = $('#tlc-ttsurvey-admin form.content');
    ce.form_status = $('#tlc-ttsurvey-admin .tlc-status');
    ce.lock_info = $('.content .info.lock').eq(0);
    ce.inputs = ce.form.find('textarea');
    ce.survey = ce.form.find('textarea.survey').eq(0);
    ce.error = ce.form.find('div.invalid.survey').eq(0);
    ce.sendmail = ce.form.find('textarea.sendmail');
    ce.submit = ce.form.find('input.submit').eq(0);
    ce.revert = ce.form.find('button.revert').eq(0);

    pid = ce.form.find('input[name=pid]').eq(0).val();

    ce.form_status.hide();

    //------------------------------------------------------------
    // We're updating the form content here rather than in php to avoid
    // dual maintenance and possible inconsistency that could result from that
    //------------------------------------------------------------

    update_content_form();

    if(!form_vars['editable']) {
      ce.inputs.prop('readonly',true);
      return;
    }

    //------------------------------------------------------------
    // The rest of the setup only applies if the form is editable
    //------------------------------------------------------------

    has_edit_lock = ce.form.find('input[name=lock]').eq(0).val() == 0;
    ce.inputs.prop('readonly',!has_edit_lock);

    $(document).on( 'heartbeat-send', handle_heartbeat_send );
    $(document).on( 'heartbeat-tick', handle_heartbeat_tick );

    //------------------------------------------------------------
    // content validation setup
    //------------------------------------------------------------

    ce.inputs.on('keyup', add_keyup_event_to_queue );
    ce.inputs.on('change', add_change_event_to_queue );

    //------------------------------------------------------------
    // form submission
    //------------------------------------------------------------

    ce.form.on('submit', handle_form_submit);
    ce.revert.on('click', handle_form_revert);
  }
);
