var ce = {};

/**
 * JSON Data Validation
 *
 * A fair amount of the code in this module deals with validation of the 
 *   JSON data to be uploaded to the Wordpress server.  
 *   1) We want the data to actually work with the survey
 *   2) We do NOT want to break the wordpress site
 *
 * Ideally, the data being uploaded comes from an unmodified dump of
 *   the data.  To this end, the data file (.tlctt extension) contains
 *   on its first line a keyword (tlctt) and a crc32b checksum.  If the
 *   file is modified the checksum will not match.  Of course, it is
 *   possible to regenerate the checksum, but this is going to take
 *   a deliberate effort and anyone capable of doing this will, 
 *   hopefully, be sophisticated enought to make the data changes
 *   correctly.
 *
 * Nonetheless, there are a number of levels of protection on the uploaded
 *   data.
 *   1) The first line contains the proper keyword and a checksum (browser/js)
 *   2) The data is not empty and is valid JSON (browser/js)
 *   3) That the JSON follows the proper TLCTT template (ajax/php)
 *
 * Once the data has passed the following, the user must acknowledge
 *   that uploading data will replace all survey data.  If the (ajax)
 *   validation reported any non-error warnings, the user must also
 *   acknowledge the fact that the data may have some issues.
 *
 * Only after all of these checks have been met will the submit buton
 *   be enabled.
 *
 * In addition, the upload link will not work while validation of the 
 *   prervious upload attempt is in progress.
 *
 * The DOM state is updated to reflect the current validaiton state.
 *   The validation factors that determine the state are:
 *   - is_empty: no uploaded
 *   - in_progress: waiting on AJAX validation to complete
 *   - found_errors: validation found errors with the data
 *   - found_warnings: validation found potential issues with the data
 **/

var validation = {
  is_empty: true,
  in_progress: false,
  retrying: false,
  complete: false,
  found_errors: false,
  found_warnings: false,
  checksum: null,
};

function clear_data_file()
{
  validation.is_empty = true;
  validation.checksum = null;
  ce.json_data.val("");
  ce.data_file_name.html("");
  ce.data_file_input.val("");
  clear_findings()
  clear_acknowledgements();
}

function clear_findings()
{
  validation.found_errors = false;
  validation.found_warnings = false;
  ce.validation_warnings.find('ul').empty();
  ce.validation_errors.find('ul').empty();
}

function clear_acknowledgements()
{
  ce.acknowledge_upload_cb.prop('checked',false);
  ce.acknowledge_warnings_cb.prop('checked',false);
}

function validate_json_data()
{
  clear_findings();
  clear_acknowledgements();
  validation.in_progress = true;
  validation.complete = false;
  update_dom();
  
  jQuery.ajax( {
    method: "POST",
    url: form_vars['ajaxurl'],
    data: {
      action: 'tlc_ttsurvey',
      nonce: form_vars['nonce'],
      query: 'admin/validate_survey_data',
      survey_data: ce.json_data.val(),
      checksum: validation.checksum,
    },
    timeout: 15000, // milliseconds
    dataType: 'json',
    success: handle_validation_response,
    error: handle_validation_failure,
  });
}


function handle_validation_response(response,status,jqHXR)
{

  validation.in_progress = false;
  validation.retrying = false;
  validation.complete = true;

  if(response.bad_checksum) {
    const filename = ce.data_file_name.html();
    alert(`Cannot load ${filename}: invalid checksum`);
    clear_data_file(); // will also clear findings
    update_dom();
    return;
  }

  if(response.warnings) {
    validation.found_warnings = true;
    const ul = ce.validation_warnings.find('ul');
    for( const warning of response.warnings) {
      ul.append(jQuery('<li>').append(warning));
    }
  }

  if(response.errors) {
    validation.found_errors = true;
    const ul = ce.validation_errors.find('ul');
    for( const error of response.errors) {
      ul.append(jQuery('<li>').append(error));
    }
  }

  update_dom();
}


function handle_validation_failure(jqHXR,status,error)
{
  validation.retrying = true;
  validate_json_data();
}


/**
 * handle updates to json data textarea
 **/

async function handle_load_json_data(e)
{
  e.preventDefault();

  const files = ce.data_file_input.prop('files');
  
  // pre-validate uploaded file
  //   cannot be empty
  //   cannot be too big
  //   must have proper signature
  //   must have properly formatted JSON data

  if(files.length == 0) { return }
  const file = files[0];

  ce.data_file_input.val('');

  if(file.size > 5*1024*1024) {
    handle_bad_file(`Cannot load ${file.name} (too big)`);
    return;
  }

  const tlctt = await file.text();
  const eol = tlctt.indexOf("\n");
  if(eol<0) {
    handle_bad_file(`Cannot load ${file.name}: missing signature line`);
    return;
  }
  const firstLine = tlctt.substring(0,eol);
  const eos = firstLine.indexOf(":");
  if(eos < 0) {
    handle_bad_file(`Cannot load ${file.name}: invalid signature line`);
    return;
  }
  const signature = firstLine.substring(0,eos);
  if( signature !== "tlctt") {
    handle_bad_file(`Cannot load ${file.name}: invalid signature`);
    return;
  }

  const json = tlctt.substring(eol+1);
  if(json.length == 0) {
    handle_bad_file(`Cannot load ${file.name}: no survey data`);
    return;
  }

  try {
    const data = JSON.parse(json);
  }
  catch(e) {
    handle_bad_file(`Cannot load ${file.name}: invalid JSON data`);
    return;
  }

  // add the data to the form and begin AJAX validation

  validation.is_empty = false;
  validation.checksum = firstLine.substring(eos+1);

  ce.json_data.val(json.trim());
  ce.data_file_name.html(file.name);

  validate_json_data();
}

function handle_bad_file(error)
{
  alert(error);

}

/**
 * Data upload
 **/

function handle_submit(e)
{
  e.preventDefault();

  clear_findings();
  clear_acknowledgements();
  validation.in_progress = true;
  validation.complete = false;
  update_dom();

  jQuery.ajax( {
    method: "POST",
    url: form_vars['ajaxurl'],
    data: {
      action: 'tlc_ttsurvey',
      nonce: form_vars['nonce'],
      query: 'admin/upload_survey_data',
      survey_data: ce.json_data.val(),
    },
    timeout: 15000, // milliseconds
    dataType: 'json',
    success: handle_submit_response,
    error: handle_submit_failure,
  });
}

function handle_submit_response(response,status,jqHXR)
{
  validation.in_progress = false;
  if(response.success) {
    window.location.href = form_vars.overview;
  } else {
    alert("Upload failed: " + response.error);
    handle_validation_response(response);
  }
}

function handle_submit_failure(jqHXR,status,error)
{
  validation.in_progress = false;
  alert("No response from server");
  clear_data_file();
  update_dom();
}

/**
 * DOM management
 **/

function update_dom()
{
  // validaiton status
  ce.validation_status.removeClass(['validating','retry']);
  if(validation.in_progress) {
    ce.validation_status.html('validating').addClass('validating');
    if(validation.retrying) {
      ce.validation_status.html('reattempting validation').addClass('retry');
    }
  }

  // json_body
  ce.json_data.removeClass(['warning','error','dirty']);
  if(validation.in_progress)    { ce.json_data.addClass('dirty');   }
  if(validation.found_warnings) { ce.json_data.addClass('warning'); }
  if(validation.found_errors)   { ce.json_data.addClass('error');   }

  // warnings and errors
  if(validation.found_warnings) {
    ce.validation_warnings.show();
  } else {
    ce.validation_warnings.hide();
  }
  if(validation.found_errors) {
    ce.validation_errors.show();
  } else {
    ce.validation_errors.hide();
  }

  // acknowlege checkboxes
  if(validation.is_empty || validation.found_errors || !validation.complete) {
    ce.acknowledge_upload.hide();
    ce.acknowledge_warnings.hide();
  }
  else {
    ce.acknowledge_upload.show();
    if(validation.found_warnings) {
      ce.acknowledge_warnings.show();
    } else {
      ce.acknowledge_warnings.hide();
    }
  }

  // submit button
  var ok_to_submit = false;
  if(validation.complete && !validation.found_errors) {
    const upload_acknowledged = ce.acknowledge_upload_cb.prop('checked');
    if(upload_acknowledged) {
      if(validation.found_warnings) {
        ok_to_submit = ce.acknowledge_warnings_cb.prop('checked');
      } else {
        ok_to_submit = true;
      }
    }
  }
  ce.submit.attr('disabled',!ok_to_submit);
}

/**
 * prepare data form scripting
 **/

jQuery(document).ready(
  function($) {
    const upload_form = $('#tlc-ttsurvey-admin form.data.upload');
    const data_load_link = upload_form.find('#data-load');

    ce.data_file_input = upload_form.find('#data-file-input');
    ce.data_file_name = upload_form.find('#data-file-name');
    ce.json_data = upload_form.find('#json-data');
    ce.validation_status = upload_form.find('#validation-status');
    ce.validation_warnings = upload_form.find('.validation.warnings');
    ce.validation_errors = upload_form.find('.validation.errors');
    ce.acknowledge_upload = upload_form.find('#acknowledge-upload');
    ce.acknowledge_upload_cb = upload_form.find('#acknowledge-upload input');
    ce.acknowledge_warnings = upload_form.find('#acknowledge-warnings');
    ce.acknowledge_warnings_cb = upload_form.find('#acknowledge-warnings input');
    ce.submit = upload_form.find('#data-upload');

    data_load_link.on('click',function(e) {
      e.preventDefault();
      if(!validation.in_progress) { ce.data_file_input.click(); }
    });

    ce.data_file_input.on('change',handle_load_json_data);
    ce.acknowledge_upload.on('change',update_dom);
    ce.acknowledge_warnings.on('change',update_dom);
    ce.submit.on('click',handle_submit);

    update_dom();
  }
);
