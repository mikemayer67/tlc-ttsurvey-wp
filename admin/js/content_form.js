var survey_error = null;
var saved_content = null;
var autosave = {};

var ajax_lock = false;
var queue_timer = null;
var queue = [];

var pid = null;
var ce = {};


function populate_form()
{
  ce.form_status.hide();

  jQuery.post(
    form_vars['ajaxurl'],
    {
      action:'tlc_ttsurvey',
      nonce:form_vars['nonce'],
      query:'admin/populate_content_form',
      pid:pid,
    },
    function(response) {
      if(response.success) {
        ce.last_modified.val(response.data.last_modified);

        saved_content = {
          survey: response.data.survey,
          sendmail: response.data.sendmail,
          preview: response.data.preview,
        };

        var from_autosave = false;
        var current_content = saved_content;

        if(autosave[pid]) {
          // equality means that the autosave is for the current revision
          //   earlier means that autosave is no longer applicable
          //   later means what?  the post was somehow rolled back?
          //   either way, only want to use the autosave on equality
          if(autosave[pid].last_modified == response.data.last_modified) {
            from_autosave = true;
            current_content = autosave[pid];
            ce.form_status.html('autosave').addClass('info').show();
          }
        } 

        ce.survey.val(current_content.survey);

        for(const key in current_content.sendmail) {
          ce.sendmail.filter('.'+key).val(current_content.sendmail[key]);
        }
        for(const key in current_content.preview) {
          ce.preview.filter('.'+key).html(current_content.preview[key]);
        }

        if(from_autosave) { 
          update_state();
          for(const key in current_content.sendmail) {
            refresh_sendmail_preview(key);
          }
        }
      }
    },
    'json',
  );
}

function handle_pid_nav(event)
{
  event.preventDefault();
  var href = this.href + '&block=' + ce.active_block.val();
  window.location = href;
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

  ce.active_block.val(target);
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

 // restart the timer; clear it if it's not already running
 if(queue_timer) { clearTimeout(queue_timer); }
 queue_timer = setTimeout(watch_queue,500);
}

function watch_queue()
{
  if(ajax_lock) { 
    //waiting on ajax job to complete restart the timer and return
    queue_timer = setTimeout( watch_queue, 500 );
    return;
  }

  if(queue.length==0) {
    // nothing else on queue, quit timer, update state, and return
    clearTimeout(queue_timer);
    queue_timer = null;
    update_state();
    return;
  }

  // handle next queued event and restart the timer
  ajax_lock = true;
  queue_timer = null;

  const input = queue.shift();
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
      action:'tlc_ttsurvey',
      nonce:form_vars['nonce'],
      query:'admin/validate_content_form',
      survey:ce.survey.eq(0).val(),
    },
    function(response) {
      survey_error = response.success ? null : response.data;
      ajax_lock = false;
    },
    'json',
  );
}

function refresh_sendmail_preview(subject)
{
  const input = ce.sendmail.filter('.'+subject)
  const content = input.val()

  jQuery.post(
    form_vars['ajaxurl'],
    {
      action:'tlc_ttsurvey',
      nonce:form_vars['nonce'],
      query:'admin/render_sendmail_preview',
      pid:pid,
      subject:subject,
      content:content,
    },
    function(response) {
      if(response.success) {
        ce.preview.filter('.'+subject).html(response.data);
        ajax_lock = false;

      }
    },
    'json',
  );
}


function content_has_changed()
{
  if(!saved_content) { return false; }

  if(saved_content.survey != ce.survey.val()) { return true; }

  var rval = false;
  ce.sendmail.each(function() {
    const saved_sendmail = saved_content.sendmail[this.name];
    if( this.value != saved_sendmail ) { rval = true; }
  });
  return rval;
}

function update_state()
{
  const has_change = content_has_changed();

  ce.submit.prop('disabled', !has_change || survey_error );
  ce.revert.prop('disabled', !has_change );

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

  let data = {
    action:'tlc_ttsurvey',
    nonce:form_vars['nonce'],
    query:'admin/submit_content_form',
    pid:pid,
    content:{},
  };

  data.content.survey = ce.survey.val();
  data.content.sendmail = {}
  ce.sendmail.each(function() {
    data.content.sendmail[this.name] = this.value;
  });

  jQuery.post(
    form_vars['ajaxurl'],
    data,
    function(response) {
      if(response.success) {
        ce.form_status.html('saved').addClass('info').show();
        ce.last_modified.val(response.data.last_modified);
        saved_content.survey = ce.survey.val();
        for(const key in saved_content.sendmail) {
          saved_content.sendmail[key] = ce.sendmail.filter('.'+key).val();
        }
        for(const key in saved_content.preview) {
          saved_content.preview[key] = ce.preview.filter('.'+key).html();
        }
        delete autosave[pid];
        localStorage.autosave = JSON.stringify(autosave);
        reset_queue();
        update_state();
      } else {
        alert("failed to save content: " + response.data);
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
    ce.sendmail.filter('.'+key).val(saved_content.sendmail[key]);
  }
  for(const key in saved_content.preview) {
    ce.preview.filter('.'+key).html(saved_content.preview[key]);
  }
  // assume that the saved_content has been validated already
  survey_error = false

  delete autosave[pid];
  localStorage.autosave = JSON.stringify(autosave);
  reset_queue();
  update_state();
}

function do_autosave()
{
  if( content_has_changed() ) {
    autosave[pid] = {
      survey: ce.survey.val(),
      sendmail: {},
      preview: {},
      last_modified: ce.last_modified.val(),
    };
    ce.sendmail.each( function() {
      const name = this.name;
      const value = this.value;
      autosave[pid].sendmail[name] = value;
    });
  } else {
    delete autosave[pid];
  }
  localStorage.autosave = JSON.stringify(autosave);
}

function hold_lock()
{
  jQuery.post(
    form_vars.ajaxurl,
    {
      action:'tlc_ttsurvey',
      nonce:form_vars.nonce,
      query:'admin/obtain_content_lock',
    },
    function(response) {
      if(!response.has_lock) {
        window.location.reload(true);
      }
    },
    'json',
  );
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

  ce.pid_navtabs = $('#tlc-ttsurvey-admin div.content a.pid.nav-tab');
  ce.active_block = ce.form.find('input[name=active_block]');

  pid = ce.form.find('input[name=pid]').eq(0).val();

  ce.form_status.hide();

  ce.form.find('a.nav-tab.block').on('click', handle_block_nav);
  ce.form.find('.content-block div.block').hide();

  const active = ce.active_block.val();
  ce.form.find(`.content-block div.block.${active}`).show();

  ce.pid_navtabs.on('click', handle_pid_nav);

  //------------------------------------------------------------
  // We're updating the form content here rather than in php to avoid
  // dual maintenance and possible inconsistency that could result from that
  //------------------------------------------------------------

  populate_form();

  // setup up timer to hold edit lock
  hold_lock();
  setInterval(hold_lock,15000);


  if(!form_vars['editable']) {
    ce.inputs.prop('readonly',true);
    return;
  }

  if(localStorage.autosave) {
    autosave = JSON.parse(localStorage.autosave)
  }

  //------------------------------------------------------------
  // The rest of the setup only applies if the form is editable
  //------------------------------------------------------------

  ce.inputs.prop('readonly',false);

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
