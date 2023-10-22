var has_edit_lock = false;
var survey_error = null;
var saved_content = null;

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
        ce.last_modified.val(response.last_modified);

        saved_content = {
          survey: response.survey,
          sendmail: response.sendmail,
        };

        update_state_needed = false;
        current_content = saved_content;

        if(localStorage.autosave) {
          autosave = JSON.parse(localStorage.autosave);
          // equality means that the autosave is for the current revision
          //   earlier means that autosave is no longer applicable
          //   later means what?  the post was somehow rolled back?
          //   either way, only want to use the autosave on equality
          if(autosave.last_modified == response.last_modified) {
            update_state_needed = true;
            current_content = autosave;
            ce.form_status.html('autosave').addClass('info').show();
          }
        } 

        ce.survey.val(current_content.survey);

        for(const key in saved_content.sendmail) {
          ce.sendmail.filter('.'+key).val(current_content.sendmail[key].md);
          ce.preview.filter('.'+key).html(current_content.sendmail[key].html);
        }

        if(update_state_needed) { update_state() }
      }
    },
    'json',
  );
}

function handle_block_nav()
{
  const target = this.dataset.target;
  const nav_tabs = ce.form.find('a.block.nav-tab');
  const active_nav_tab = nav_tabs.filter("[data-target='"+target+"']");

  const blocks = ce.form.find('.content-block .block');
  const active_block = blocks.filter('.'+target);

  nav_tabs.removeClass('nav-tab-active');
  active_nav_tab.addClass('nav-tab-active');

  blocks.hide();
  active_block.show();
}

function reset_queue()
{
  queue.length=0
  if(queue_timer) {
    clearTimeout(queue_timer);
    queue_timer=null;
  }
  ajax_lock=false;
}

function handle_input_event()
{
  ce.form_status.hide();
  ce.submit.prop('disabled', true);
  ce.revert.prop('disabled', true);

  // remove entries (should only be 0 or 1) from queue for the current input
  // and add input to the end of the queue
  queue = queue.filter( input => input != this.name );
  queue.push(this.name);

 // start the timer if it's not already running
 if(!queue_timer) {
   queue_timer = setTimeout(watch_queue,500);
 }
}

function watch_queue()
{
  if(ajax_lock) { 
    //waiting on ajax job to complete restart the timer and return
    queue_timer = setTimeout( watch_queue, 500 );
    return;
  }

  if(queue.length==0) {
    // nothing else on queue, quite timer, update state, and return
    clearTimeout(queue_timer);
    queue_timer = null;
    update_state();
    return;
  }

  // handle next queued event and restart the timer
  ajax_lock = true;

  input = queue.shift();
  if( input == "survey" ) {
    validate_survey_input();
  } else {
    refresh_sendmail_preview(input);
  }

  queue_timer = setTimeout( watch_queue, 500 );
}

function validate_survey_input()
{
  jQuery.post(
    form_vars['ajaxurl'],
    {
      'action':'tlc_ttsurvey',
      'nonce':form_vars['nonce'],
      'query':'validate_content_form',
      'survey':ce.survey.eq(0).val(),
    },
    function(response) {
      survey_error = response.ok ? null : response.error;
      ajax_lock = false;
    },
    'json',
  );
}

function refresh_sendmail_preview(template)
{
  jQuery.post(
    form_vars['ajaxurl'],
    {
      'action':'tlc_ttsurvey',
      'nonce':form_vars['nonce'],
      'query':'render_sendmail_template',
      'markdown':ce.sendmail.filter('.'+template).val(),
    },
    function(response) {
      if(response.ok) {
        ce.preview.filter('.'+template).html(response.rendered);
        ajax_lock = false;
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
  if(saved_content) {
    if(saved_content.survey != ce.survey.val()) {
      rval = true;
    } else {
      const sendmail = saved_content.sendmail;
      ce.sendmail.each(function() {
        if( this.value != sendmail[this.name].md ) { rval = true; }
      });
    }
  }
  return rval;
}

function update_state()
{
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
  ce.form_status.hide();
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
      if(response.ok) {
        ce.form_status.html('updated').addClass('info').show();
        ce.last_modified.val(response.last_modified);
        saved_content.survey = ce.survey.val();
        for(const key in saved_content.sendmail) {
          saved_content.sendmail[key].md = ce.sendmail.filter('.'+key).val();
          saved_content.sendmail[key].html = ce.preview.filter('.'+key).html();
        }
        localStorage.removeItem('autosave');
        update_state();
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
  ce.form_status.hide();
  event.preventDefault();

  ce.survey.val(saved_content.survey);
  for(const key in saved_content.sendmail) {
    ce.sendmail.filter('.'+key).val(saved_content.sendmail[key].md);
    ce.preview.filter('.'+key).html(saved_content.sendmail[key].html);
  }
  // assume that the saved_content has been validated already
  survey_error = false

  localStorage.removeItem('autosave');
  reset_queue();
  update_state();
}

function do_autosave()
{
  if( content_has_changed() ) {
    var autosave_data = {
      survey: ce.survey.val(),
      sendmail: {},
      last_modified: ce.last_modified.val(),
    };
    ce.sendmail.each( function() {
      autosave_data.sendmail[this.name] = {
        md:this.value,
        html:ce.preview.filter('.'+this.name).html(),
      }
    });
    localStorage.autosave = JSON.stringify(autosave_data);
  } else {
    localStorage.removeItem('autosave');
  }
}


jQuery(document).ready( function($) {
  ce.form = $('#tlc-ttsurvey-admin form.content');
  ce.form_status = $('#tlc-ttsurvey-admin .tlc-status');
  ce.lock_info = $('.content .info.lock').eq(0);
  ce.last_modified = ce.form.find('input[name=last-modified]').eq(0);
  ce.inputs = ce.form.find('textarea');
  ce.survey = ce.form.find('textarea.survey').eq(0);
  ce.error = ce.form.find('div.invalid.survey').eq(0);
  ce.sendmail = ce.form.find('textarea.sendmail');
  ce.preview = ce.form.find('.sendmail.preview');
  ce.submit = ce.form.find('input.submit').eq(0);
  ce.revert = ce.form.find('button.revert').eq(0);

  pid = ce.form.find('input[name=pid]').eq(0).val();

  ce.form_status.hide();

  const active_block = form_vars['active_block'];
  ce.form.find('a.nav-tab.block').on('click', handle_block_nav);
  ce.form.find('.content-block div.block').hide();
  ce.form.find(`.content-block div.block.${active_block}`).show();

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

  ce.inputs.on('input', handle_input_event );
  ce.inputs.on('input', do_autosave );

  //------------------------------------------------------------
  // form submission
  //------------------------------------------------------------

  ce.form.on('submit', handle_form_submit);
  ce.revert.on('click', handle_form_revert);
});
