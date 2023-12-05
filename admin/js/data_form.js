import * as validate from './validation.js';

var ce = {};

/**
 * JSON Data Validation
 *
 * A fair amount of the code in this module deals with validation of the 
 *   JSON data to be uploaded to the Wordpress server.  
 *   1) We want the data to actually work with the survey
 *   2) We do NOT want to break the wordpress site
 *
 * The only validation that occurs on the client (js) is a quick
 *   validation that the data is valid JSON. The actual validation 
 *   will be handled on the servier via AJAX requests to the wordpress site.
 *
 * Validation is needed whenever data is loaded from file or entered
 *   by the user into the textview input block.  To avoid locking 
 *   performing validation after each and every keystroke, a timer
 *   is set/reset with each input event that modifies the input.
 *   Validation occurs only when the timer fires.
 *
 * Because validation is handled via AJAX calls, it is inherently
 *   asynchronous.  This means that the returned status may be out
 *   of date with the actual content of the input box.  We must either
 *   live with this possibility or block edits while the validation
 *   is happening.  As the former seems to be the lesser of the two
 *   evils, that is the approach taken below.  
 *
 *   When a change is made to the input data:
 *     - a flag is set to indicate that validation is needed
 *     - decorate the textarea (via css) to show need for validation
 *     - disable the submit button
 *     - clear the acknowlege checkbox
 *     - any existing validation timer is cleared
 *     - a validtion timer is started unless we are waiting on
 *         the result from an (ajax) validation request
 *
 *   When the validation timer fires:
 *     - a (different) flag is set to indicate that validation is in progress
 *     - decorate the textarea (via css) to show validation in progress
 *     - post the (ajax) validation request to the wordpress server
 *        (set a timeout on the request to avoid infinite hang
 *     - clear the validation needed flag
 *
 *   When the validation result is received (status code == 200)
 *     - update the data status field
 *       - if the json data has a problem, show the error
 *       - otherwise, if the json data is unmodified, show the filename
 *       - otherwise, clear the status
 *
 *     - if the validation is needed flag is set
 *       - post a new (ajax) validation request
 *       - clear the validation needed flag
 *     - otherwise (no more validation needed)
 *       - remove the validation decoration from the textarea
 *       - clear the validation in progress flag
 *       - if the json data is good, enable the submit button`
 *
 *   When the validation result fails (status code != 200)
 *     - decorate the textarea (via css) to show validation reattempt
 *     - post a new validation request to the wordpress server
 *
 **/

 var validation = {
   timer: null,
   needed: false,
   in_progress: false,
 };
 var json_data = {
   is_good:false,
   file:null,
 };

function queue_validation()
{
  start_validation_timer();
  validation.needed = true
  json_data.is_good = false;
  ce.json_data.addClass('dirty');
  ce.submit.attr('disabled',true);
  ce.confirm_upload.prop('checked',false);
}

function start_validation_timer()
{
  stop_validation_timer();
  if(validation.in_progress==false) { 
    validation.timer = setTimeout(validate_json_data,500);
  }
}

function stop_validation_timer()
{
  if(validation.timer) {
    clearTimeout(validation.timer);
    validation.timer = null;
  }
}

function validate_json_data()
{
  validation.in_progress=true;
  validation.needed=false;
  ce.validation_status.addClass('validating');

  const data = ce.json_data.val().trim();

  var result = prevalidate_json_data(data);
  if( !result.success ) {
    handle_validation_response(result);
    return;
  }

  send_json_data(
    data,
    'validation',
    handle_validation_response,
  );

}

function prevalidate_json_data(data)
{
  // perform minimal validation:
  //  - is the data valid JSON
  //  - do we have all the required primary keys
  //  - do we have any extra primary keys

  if(data.length == 0) {
    return {
      ok:false,
      warning:"Nothing to upload",
    };
  }

  try {
    data = JSON.parse(data);
  } catch(e) {
    return {
      ok: false,
      error: e.toString(),
    };
  }

  const keys = Object.keys(data);
  const expected = ['userids','surveys','responses'];

  const extra = keys.filter(x => !expected.includes(x));
  if(extra.length > 0) {
    return {
      success: false,
      warning: `Contains invalid key '${extra[0]}'`,
    };
  }

  const missing = expected.filter(x => !keys.includes(x));
  if(missing.length > 0) {
    return {
      success: false,
      warning: `Missing required key '${missing[0]}'`,
    };
  }

  var result = prevalidate_survey_data(data.surveys); 
  if(!result.success) {
    return result;
  }

  return {
    success: true,
  }
}

function prevalidate_survey_data(surveys)
{
  for ( let name in surveys ) {
    var result = validate.survey_name(name);
    if(!result.ok) {
      return {
        success: false,
        warning: `'${name}' is not a valid survey name (${result.error})`,
      };
    }
  }
  return {success:true};
}

function handle_validation_response(response,status,jqHXR)
{
  json_data.is_good = response.success;

  if(response.error) { set_error_status(response.error); }
  else if(response.warning) { set_warning_status(response.warning); }
  else if(json_data.file) { set_info_status(json_data.file); }
  else {clear_status(); }

  ce.validation_status.html('validating').removeClass('retry');

  if(validation.needed) 
  {
    validate_json_data();
  }
  else
  {
    validation.in_progress = false;
    ce.json_data.removeClass('dirty');
    ce.validation_status.removeClass('validating');
  }
}

function handle_validation_failure(jqHXR,status,error)
{
  clear_status();
  ce.validation_status.html('reattempting validation').addClass('retry');
  validate_json_data();
}

/**
 * Data status
 **/

function clear_status()
{
  ce.data_status.removeClass(['info','warning','error']);
  ce.json_data.removeClass(['info','warning','error']);
}

function set_status(msg,level)
{
  clear_status()
  ce.data_status.html(msg).addClass(level);
  ce.json_data.addClass(level);
}

function set_info_status(msg) { set_status(msg,'info'); }
function set_warning_status(msg) { set_status(msg,'warning'); }
function set_error_status(msg) { set_status(msg,'error'); }

/**
 * handle updates to json data textarea
 **/

function handle_json_input(e)
{
  json_data.file = null;
  queue_validation();
}

async function handle_load_json_data(e)
{
  e.preventDefault();
  const files = ce.json_data_file.prop('files');

  if(files.length == 0) { return }
  const file = files[0]

  if(file.size > 5*1024*1024) {
    alert(`Cannot load ${file.name} (too big)`);
    return;
  }

  const json = await file.text();
  try {
    const data = JSON.parse(json);
  }
  catch(e) {
    alert(`Cannot load ${file.name} (not valid JSON)`);
    return;
  }

  ce.json_data.val(json);
  ce.json_data_file.val('');
  set_info_status(file.name);
  json_data.file = file.name;
  queue_validation();
}

/**
 * Data upload
 **/

function handle_confirmation(e)
{
  if(ce.confirm_upload.is(':checked')) {
    if(json_data.is_good) {
      ce.submit.attr('disabled',false);
    }
  } else {
    ce.submit.attr('disabled',true);
  }
}

function handle_submit(e)
{
  e.preventDefault();

  send_json_data(
    ce.json_data.val(),
    'upload',
    function(response,status) {
      if(response.success) {
        window.location.href = form_vars.overview;
      } else {
        handle_validation_response(response);
      }
    }
  );
}

/**
 * AJAX call to upload/validate json_data
 **/

function send_json_data(data, scope, success_callback)
{
  jQuery.ajax( {
    method: "POST",
    url: form_vars['ajaxurl'],
    data: {
      action: 'tlc_ttsurvey',
      nonce: form_vars['nonce'],
      query: 'admin/upload_survey_data',
      scope: scope,
      survey_data: data,
    },
    timeout: 15000, // milliseconds
    dataType: 'json',
    success: success_callback,
    error: handle_validation_failure,
  });
}

/**
 * prepare data form scripting
 **/

jQuery(document).ready(
  function($) {
    ce.upload_form = $('#tlc-ttsurvey-admin form.data.upload');
    ce.json_data_file = ce.upload_form.find('#json-data-file');
    ce.load_json_trigger = ce.upload_form.find('#data-load');
    ce.data_status = ce.upload_form.find('#data-status');
    ce.json_data = ce.upload_form.find('#json-data');
    ce.validation_status = ce.upload_form.find('#validation-status');
    ce.confirm_upload = ce.upload_form.find('#confirm-upload');
    ce.submit = ce.upload_form.find('#data-upload');

    ce.load_json_trigger.on('click',function(e) {
      e.preventDefault();
      ce.json_data_file.click();
    });

    ce.json_data_file.on('change',handle_load_json_data);
    ce.json_data.on('input',handle_json_input);
    ce.confirm_upload.on('change',handle_confirmation);
    ce.submit.on('click',handle_submit);
  }
);
