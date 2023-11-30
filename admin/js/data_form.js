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

 var validation_timer = null
 var validation_needed = false
 var validation_in_progress = false
 var json_data_is_good = false
 var json_data_file = null

function queue_validation()
{
  console.log('queue_validation');
  start_validation_timer();
  validation_needed = true
  json_data_is_good = false;
  ce.json_data.addClass('dirty');
  ce.submit.attr('disabled',true);
  ce.confirm_upload.prop('checked',false);
}

function start_validation_timer()
{
  console.log('start_validation_timer');
  stop_validation_timer();
  if(validation_in_progress==false) { 
    console.log("setTimeout");
    validation_timer = setTimeout(validate_json_data,500);
  }
}

function stop_validation_timer()
{
  console.log('stop_validation_timer');
  if(validation_timer) {
    console.log("clearTimeout");
    clearTimeout(validation_timer);
    validation_timer = null;
  }
}

function validate_json_data()
{
  console.log('validate_json_data');
  validation_in_progress=true;
  validation_needed=false;
  ce.validation_status.addClass('validating');

  json_data = ce.json_data.val();

  result = prevalidate_json_data(json_data);
  if( result ) {
    handle_validation_result(result);
  }

  send_json_data(
    json_data,
    'validation',
    function(response,status) {
      if(response.ok) {
        window.location.href = form_vars.overview;
      } else {
        handle_validation_result(response);
      }
    }
  );

}

function prevalidate_json_data(json_data)
{
  console.log('prevalidate_json_data');
  // perform minimal validation:
  //  - is the data valid JSON
  //  - do we have all the required primary keys
  //  - do we have any extra primary keys

  try {
    const data = JSON.parse(json_data);
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
      ok: false,
      warning: `Contains invalid key '${extra_keys[0]}',
    };
  }

  const missing = expected.filter(x => !keys.includes(x));
  if(missing.length > 0) {
    return {
      ok: false,
      warning: `Missing required key '${missing_keys[0]}',
    return false;
  }

  return null;
}

function handle_validation_result(result)
{
  console.log('handle_validation_result');
  json_data_is_good = result.ok;

  if(result.error) { set_error_status(result.error); }
  else if(result.warning) { set_warning_status(result.warning); }
  else if(json_data_file) { set_info_status(json_data_file); }
  else {clear_status(); }

  ce.validation_status.html('validating').removeClass('retry');

  if(validation_needed) 
  {
    validate_json_data();
  }
  else
  {
    validation_in_progress = false;
    ce.json_data.removeClass('dirty');
    ce.validation_status.removeClass('validating');
  }
}

function handle_validation_failure()
{
  console.log('handle_validation_failure');
  ce.validation_status.html('reattempting validation').addClass('retry');
  validate_json_data();
}

/**
 * Data status
 **/

function clear_status()
{
  console.log('clear_status');
  ce.data_status.removeClass(['info','warning','error']);
}

function set_status(msg,level)
{
  console.log(`set_status(${msg},${level})`);
  clear_status()
  ce.data_status.html(msg).addClass(level);
}

function set_info_status(msg) { set_status(msg,'info'); }
function set_warning_status(msg) { set_status(msg,'warning'); }
function set_error_status(msg) { set_status(msg,'error'); }

/**
 * handle updates to json data textarea
 **/

function handle_json_input(e)
{
  console.log('handle_json_input');
  json_data_file = null;
  queue_validation();
}

async function handle_load_json_data(e)
{
  console.log('handle_load_json_data');
  e.preventDefault();
  const files = ce.json_data_file.prop('files');

  if(files.length == 0) { return }
  file = files[0]

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
  set_info_status(file.name);
  json_data_file = file.name;
  queue_validation();
}

/**
 * Data upload
 **/

function handle_confirmation(e)
{
  console.log('handle_confirmation');
  if(ce.confirm_upload.is(':checked')) {
    if(json_data_is_good) {
      ce.submit.attr('disabled',false);
    }
  } else {
    ce.submit.attr('disabled',true);
  }
}

function handle_submit(e)
{
  console.log('handle_submit');
  e.preventDefault();

  send_json_data(
    ce.json_data.val(),
    'upload',
    hanlde_validation_result,
  );
}

/**
 * AJAX call to upload/validate json_data
 **/

function send_json_data(data, scope, success_callback)
{
  console.log('send_json_data');
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
