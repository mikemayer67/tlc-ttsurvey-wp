var survey_error = null;
var saved_content = null;
var autosave = {};

var ajax_lock = false;
var queue_timer = null;
var queue = [];

var ce = {
  pid:null,
  last_modified:0,
}


function populate_form()
{
  ce.form_status.hide();

  jQuery.post(
    form_vars['ajaxurl'],
    {
      action:'tlc_ttsurvey',
      nonce:form_vars['nonce'],
      query:'admin/populate_content_form',
      pid:ce.pid,
    },
    function(response) {
      if(response.success) {
        ce.last_modified = response.data.last_modified;

        saved_content = {
          survey: response.data.survey,
          sendmail: response.data.sendmail,
          preview: response.data.preview,
        };

        var from_autosave = false;
        var current_content = saved_content;

        if(autosave[ce.pid]) {
          // equality means that the autosave is for the current revision
          //   earlier means that autosave is no longer applicable
          //   later means what?  the post was somehow rolled back?
          //   either way, only want to use the autosave on equality
          if(autosave[ce.pid].last_modified == response.data.last_modified) {
            from_autosave = true;
            current_content = autosave[ce.pid];
            ce.form_status.html('autosave').addClass('info').show();
          }
        } 


        console.log("@@@ Add logic to populate survey content");

        for(const key in current_content.sendmail) {
          ce.sendmail_textarea.filter('.'+key).val(current_content.sendmail[key]);
        }
        for(const key in current_content.preview) {
          ce.sendmail_preview.filter('.'+key).html(current_content.preview[key]);
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

function handle_pid_nav(e)
{
  e.preventDefault();
  window.location = this.href;
}

function handle_editor_nav(e)
{
  const target = this.dataset.target;

  ce.editor_navtabs.removeClass('nav-tab-active');
  jQuery(this).addClass('nav-tab-active');

  ce.editors.hide();
  ce.editors.filter('.'+target).show();
  if(target == 'survey') {
    ce.focuses.hide();
    ce.focuses.filter('.'+sessionStorage.active_focus).show();
  }
  if(target == 'sendmail') {
    ce.templates.hide();
    ce.templates.filter('.'+sessionStorage.active_template).show();
  }

  sessionStorage.active_editor = target;
}

function handle_focus_nav(e)
{
  const target = this.dataset.target;

  ce.focus_navtabs.removeClass('nav-tab-active');
  jQuery(this).addClass('nav-tab-active');

  ce.focuses.hide();
  ce.focuses.filter('.'+target).show();

  if( ce.submit.length > 0 ) {
    sessionStorage.active_focus = target;
  }
}

function handle_sendmail_nav(e)
{
  const target = this.dataset.target;

  ce.sendmail_navtabs.removeClass('nav-tab-active');
  jQuery(this).addClass('nav-tab-active');

  ce.templates.hide();
  ce.templates.filter('.'+target).show();

  sessionStorage.active_template = target;
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
      survey:ce.survey.val(),
    },
    function(response) {
      survey_error = response.success ? null : response.data;
      ajax_lock = false;
    },
    'json',
  );
}

function refresh_sendmail_preview(template)
{
  const content = ce.sendmail_textarea.filter('.'+template).val();

  jQuery.post(
    form_vars['ajaxurl'],
    {
      action:'tlc_ttsurvey',
      nonce:form_vars['nonce'],
      query:'admin/render_sendmail_preview',
      pid:ce.pid,
      subject:template,
      content:content,
    },
    function(response) {
      if(response.success) {
        ce.sendmail_preview.filter('.'+template).html(response.data);
        ajax_lock = false;
      }
    },
    'json',
  );
}

function content_has_changed()
{
  if(!saved_content) { return false; }

  console.log("@@@ Add logic to see if survey content has changed");

  var rval = false;
  ce.sendmail_textarea.each(function() {
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

  console.log("@@@ Update logic for displaying error state");
  if(survey_error) {
    console.log("@@@ ... has error: " + survey_error);
  } else {
    console.log("@@@ ... no error at this point");
  }
}

function handle_form_submit(e)
{
  ce.form_status.hide();
  e.preventDefault();

  let data = {
    action:'tlc_ttsurvey',
    nonce:form_vars['nonce'],
    query:'admin/submit_content_form',
    pid:ce.pid,
    content:{},
  };

  console.log("@@@ Add logic to add survey content to data structure");

  data.content.sendmail = {}
  ce.sendmail_textarea.each(function() {
    data.content.sendmail[this.name] = this.value;
  });

  jQuery.post(
    form_vars['ajaxurl'],
    data,
    function(response) {
      if(response.success) {
        ce.form_status.html('saved').addClass('info').show();
        ce.last_modified = response.data.last_modified;
        console.log("@@@ Add logic to copy survey content to saved_content");
        for(const key in saved_content.sendmail) {
          saved_content.sendmail[key] = ce.sendmail_textarea.filter('.'+key).val();
        }
        for(const key in saved_content.preview) {
          saved_content.preview[key] = ce.sendmail_preview.filter('.'+key).html();
        }
        delete autosave[ce.pid];
        sessionStorage.autosave = JSON.stringify(autosave);
        reset_queue();
        update_state();
      } else {
        alert("failed to save content: " + response.data);
      }
    },
    'json',
  );
}

function handle_form_revert(e)
{
  ce.form_status.hide();
  e.preventDefault();

  console.log("@@@ Add logic to revert survey content to saved content");
  for(const key in saved_content.sendmail) {
    ce.sendmail_textarea.filter('.'+key).val(saved_content.sendmail[key]);
  }
  for(const key in saved_content.preview) {
    ce.sendmail_preview.filter('.'+key).html(saved_content.preview[key]);
  }
  // assume that the saved_content has been validated already
  survey_error = false

  delete autosave[ce.pid];
  sessionStorage.autosave = JSON.stringify(autosave);
  reset_queue();
  update_state();
}

function do_autosave()
{
  if( content_has_changed() ) {
    autosave[ce.pid] = {
      survey: {},
      sendmail: {},
      preview: {},
      last_modified: ce.last_modified,
    };
    console.log("@@@ Add logic to add survey content to autosave");
    ce.sendmail_textarea.each( function() {
      const name = this.name;
      const value = this.value;
      autosave[ce.pid].sendmail[name] = value;
    });
  } else {
    delete autosave[ce.pid];
  }
  sessionStorage.autosave = JSON.stringify(autosave);
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

jQuery(document).ready( function() {
  ce.pid = form_vars['pid'];

  if(!sessionStorage.active_editor)   { sessionStorage.active_editor='survey'; }
  if(!sessionStorage.active_template) { sessionStorage.active_template='welcome'; }
  if(!sessionStorage.active_focus)  { sessionStorage.active_focus='_blank_'; }

  ce.body = jQuery('#tlc-ttsurvey-admin div.content');
  ce.form = ce.body.find('form.content');
  ce.form_status = ce.body.find('.tlc-status');
  ce.lock_info = ce.body.find('.info.lock');

  ce.editors = ce.form.find('div.editor');
  ce.inputs = ce.editors.find('textarea');

  ce.survey = ce.editors.filter('.survey');

  ce.sendmail = ce.editors.filter('.sendmail');
  ce.sendmail_textarea = ce.sendmail.find('textarea');
  ce.sendmail_preview = ce.sendmail.find('.preview');

  ce.submit = ce.form.find('input.submit');
  ce.revert = ce.form.find('button.revert');

  ce.pid_navtabs = ce.body.find('.pid.nav-tab');
  ce.pid_navtabs.removeClass('nav-tab-active');
  ce.pid_navtabs.filter('.'+ce.pid).addClass('nav-tab-active');
  ce.pid_navtabs.on('click',handle_pid_nav);

  ce.editor_navtabs = ce.form.find('.editor.nav-tab');
  ce.editor_navtabs.removeClass('nav-tab-active');
  ce.editor_navtabs.filter('.'+sessionStorage.active_editor).addClass('nav-tab-active');
  ce.editor_navtabs.on('click',handle_editor_nav);

  ce.focus_navtabs = ce.survey.find('.focus.nav-tab');
  ce.focus_navtabs.removeClass('nav-tab-active');
  var active_focus_tab = ce.focus_navtabs.filter('.'+sessionStorage.active_focus);
  if( active_focus_tab.length == 0 ) {
    active_focus_tab = ce.focus_navtabs.first();
  }
  active_focus_tab.addClass('nav-tab-active');
  ce.focus_navtabs.on('click',handle_focus_nav);

  ce.sendmail_navtabs = ce.sendmail.find('.template.nav-tab');
  ce.sendmail_navtabs.removeClass('nav-tab-active');
  ce.sendmail_navtabs.filter('.'+sessionStorage.active_template).addClass('nav-tab-active');
  ce.sendmail_navtabs.on('click',handle_sendmail_nav);

  ce.focuses = ce.survey.find('div.focus');
  ce.templates = ce.sendmail.find('div.template');

  ce.form_status.hide();
  ce.editors.hide();
  ce.editors.filter('.'+sessionStorage.active_editor).show();
  ce.focuses.hide();
  ce.focuses.filter('.'+sessionStorage.active_focus).show();
  ce.templates.hide();
  ce.templates.filter('.'+sessionStorage.active_template).show();

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

  if(sessionStorage.autosave) {
    autosave = JSON.parse(sessionStorage.autosave)
  }

  //------------------------------------------------------------
  // The rest of the setup only applies if the form is editable
  //------------------------------------------------------------

  ce.inputs.prop('readonly',false);

  ce.inputs.on('input', handle_input_event );
  ce.inputs.on('input', do_autosave );

  ce.form.on('submit', handle_form_submit);
  ce.revert.on('click', handle_form_revert);
});
