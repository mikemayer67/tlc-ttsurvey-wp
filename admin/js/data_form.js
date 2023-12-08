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
   has_warnings: false,
   has_errors: false,
 };

function queue_validation()
{
  console.log("queue_validation");
  validation.needed = true
  ce.json_data.addClass('dirty');
  disable_submit();
  start_validation_timer();
}

function start_validation_timer()
{
  console.log("start_validation_timer");
  stop_validation_timer();
  if(validation.in_progress==false) { 
    validation.timer = setTimeout(validate_json_data,500);
  }
}

function stop_validation_timer()
{
  console.log("stop_validation_timer");
  if(validation.timer) {
    clearTimeout(validation.timer);
    validation.timer = null;
  }
}

function validate_json_data()
{
  console.log("validate_json_data");
  console.log("validate_json_data in_progress=true");
  validation.in_progress=true;
  validation.needed=false;
  ce.validation_status.addClass('validating');

  validation.has_warnings = false;
  validation.has_errors = false;
  ce.validation_warnings.hide();
  ce.validation_errors.hide();
  ce.validation_warnings_ul.empty();
  ce.validation_errors_ul.empty();

  const json_data = ce.json_data.val().trim();

  if(json_data.length == 0) {
    add_validation_error("Nothing to upload");
    finalize_validation();
    return;
  }

  try {
    const parsed_data = JSON.parse(json_data);
  } catch(e) {
    add_validation_error(e.toString());
    finalize_validation();
    return;
  }

  jQuery.ajax( {
    method: "POST",
    url: form_vars['ajaxurl'],
    data: {
      action: 'tlc_ttsurvey',
      nonce: form_vars['nonce'],
      query: 'admin/validate_survey_data',
      survey_data: json_data,
    },
    timeout: 15000, // milliseconds
    dataType: 'json',
    success: handle_validation_response,
    error: handle_validation_failure,
  });
}

function finalize_validation()
{
  console.log("finalize_validation");
  console.log("finalize_validation in_progress=false");
  validation.in_progress = false;

  ce.validation_status.html('validating').removeClass(['validating','retry']);

  ce.json_data.removeClass(['dirty','warning','error']);
  if(validation.has_errors) {
    ce.json_data.addClass('error');
    return;
  } 

  if(validation.has_warnings) {
    ce.json_data.addClass('warning');
  }

  enable_confirm();
}

function handle_validation_response(response,status,jqHXR)
{
  console.log("handle_validation_response");
  ce.json_data.removeClass(['warning','error']);

  if(response.warnings) {
    ce.json_data.addClass('warning');
    for( const warning of response.warnings) {
      add_validation_warning(warning);
    }
  }

  if(response.errors) {
    ce.json_data.addClass('error');
    for( const error of response.errors) {
      add_validation_error(error);
    }
  }

  if(validation.needed) 
  {
    validate_json_data();
  }
  else
  {
    finalize_validation();
  }
}

function handle_validation_failure(jqHXR,status,error)
{
  console.log("handle_validation_failure");
  ce.validation_status.html('reattempting validation').addClass('retry');
  validate_json_data();
}

function add_validation_error(error)
{
  console.log("add_validation_error");
  validation.has_errors = true;
  ce.validation_errors.show()
  ce.validation_errors_ul.append(jQuery('<li>').append(error));
}

function add_validation_warning(warning)
{
  console.log("add_validation_warning");
  validation.has_warnings = true;
  ce.validation_warnings.show()
  ce.validation_warnings_ul.append(jQuery('<li>').append(warning));
}


/**
 * handle updates to json data textarea
 **/

function handle_json_input(e)
{
  console.log("handle_json_input");
  ce.data_file.html("");
  queue_validation();
}

async function handle_load_json_data(e)
{
  console.log("handle_load_json_data");
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
  ce.data_file.html(file.name);
  queue_validation();
}

/**
 * Data upload
 **/

function handle_confirmation(e)
{
  console.log("handle_confirmation");
  if(ce.confirm_upload.is(':checked')) {
    if(!validation.has_errors) {
      ce.submit.attr('disabled',false);
    }
  } else {
    ce.submit.attr('disabled',true);
  }
}

function handle_submit(e)
{
  console.log("handle_submit");
  e.preventDefault();

  const json_data = ce.json_data.val().trim();

  jQuery.ajax( {
    method: "POST",
    url: form_vars['ajaxurl'],
    data: {
      action: 'tlc_ttsurvey',
      nonce: form_vars['nonce'],
      query: 'admin/upload_survey_data',
      survey_data: json_data,
    },
    timeout: 15000, // milliseconds
    dataType: 'json',
    success: handle_upload_response,
    error: handle_validation_failure,
  });
}

function handle_upload_response(response,status,jqHXR)
{
  console.log("handle_upload_response");
  if(response.success) {
    window.location.href = form_vars.overview;
  } else {
    handle_valiation_response(response);
  }
}

/**
 * submit/conform status
 **/

function disable_submit()
{
  console.log("disable_submit");
  ce.submit.attr('disabled',true);
  ce.confirm_upload.prop('checked',false).attr('disabled',true);
}

function enable_confirm()
{
  console.log("enable_confirm");
  ce.confirm_upload.prop('checked',false).attr('disabled',false);
}

function enable_submit()
{
  console.log("enable_submit");
  ce.submit.attr('disabled',false);
}

/**
 * prepare data form scripting
 **/

jQuery(document).ready(
  function($) {
    ce.upload_form = $('#tlc-ttsurvey-admin form.data.upload');
    ce.json_data_file = ce.upload_form.find('#json-data-file');
    ce.load_json_trigger = ce.upload_form.find('#data-load');
    ce.data_file = ce.upload_form.find('#data-file');
    ce.json_data = ce.upload_form.find('#json-data');
    ce.validation_status = ce.upload_form.find('#validation-status');
    ce.validation_warnings = ce.upload_form.find('.validation.warnings');
    ce.validation_warnings_ul = ce.validation_warnings.find('ul');
    ce.validation_errors = ce.upload_form.find('.validation.errors');
    ce.validation_errors_ul = ce.validation_errors.find('ul');
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
