var ce = {};
var validation_timer = null;
var json_data_is_validated = false;

function start_validation_timer()
{
  stop_validation_timer();
  validation_timer = setTimeout(validate_json_data,500);
}

function stop_validation_timer()
{
  if(validation_timer) {
    clearTimeout(validation_timer);
    validation_timer = null;
  }
}

function validate_json_data()
{
  // performs minimal validation:
  //  - is the data valid JSON
  //  - do we have all the required primary keys
  //  - do we have any extra primary keys
  // all other validation happens on the server when we submit the form

  const json_data = ce.json_data.val();
  try {
    var data = JSON.parse(json_data);
  } catch(e) {
    set_error_status(e.toString());
    return;
  }

  const data_keys = Object.keys(data);
  const expected_keys = ['userids','surveys','responses'];

  const extra_keys = data_keys.filter(x => !expected_keys.includes(x));
  if(extra_keys.length > 0) {
    set_warning_status(`Contains invalid key '${extra_keys[0]}'`);
    return false;
  }

  const missing_keys = expected_keys.filter(x => !data_keys.includes(x));
  if(missing_keys.length > 0) {
    set_warning_status(`Missing required key '${missing_keys[0]}'`);
    return false;
  }

  json_data_is_validated = true;
  if(!ce.data_status.hasClass('info')) { clear_status(); }

  return true;
}

function clear_status()
{
  ce.data_status.removeClass(['info','warning','error']);
}

function set_status(msg,level)
{
  clear_status()
  ce.data_status.html(msg).addClass(level);
}

function set_info_status(msg) { set_status(msg,'info'); }
function set_warning_status(msg) { set_status(msg,'warning'); }
function set_error_status(msg) { set_status(msg,'error'); }


async function load_json_data(file)
{
  const json = await (new Response(file)).text();
  ce.json_data_file.val('');
  try {
    const data = JSON.parse(json);
  }
  catch(e) {
    set_error_status("Not a valid JSON file");
    return;
  }

  ce.json_data.val(json);
  set_info_status("Loaded " + file.name);
  start_validation_timer();
}

function handle_load_json_data(e)
{
  e.preventDefault();
  clear_validation();
  const files = ce.json_data_file.prop('files');
  if(files) {
    stop_validation_timer();
    ce.json_data.val("");
    load_json_data(files[0]);
  }
}

function handle_json_input(e)
{
  clear_validation();
  clear_status();
  start_validation_timer();
}

function handle_confirmation(e)
{
  if(ce.confirm_upload.is(':checked')) {
    if(json_data_is_validated) {
      ce.submit.attr('disabled',false);
    }
  } else {
    ce.submit.attr('disabled',true);
  }
}

function clear_validation()
{
  json_data_is_validated=false;
  ce.confirm_upload.prop('checked',false);
  ce.submit.attr('disabled',true);
}

function handle_submit(e)
{
  e.preventDefault();

  jQuery.post(
    form_vars['ajaxurl'],
    {
      action:'tlc_ttsurvey',
      nonce:form_vars['nonce'],
      query:'admin/upload_survey_data',
      survey_data:ce.json_data.val(),
    },
    function(response) {
      if(response.ok) {
        window.location.href = form_vars.overview;
      }
      else if(response.error) {
        set_error_status(response.error);
      }
      else if(response.warning) {
        set_warning_status(response.warning);
      }
      clear_validation();
    },
    'json',
  );
}


jQuery(document).ready(
  function($) {
    ce.upload_form = $('#tlc-ttsurvey-admin form.data.upload');
    ce.json_data_file = ce.upload_form.find('#json-data-file');
    ce.load_json_trigger = ce.upload_form.find('a.data.load');
    ce.data_status = ce.upload_form.find('span.status');
    ce.json_data = ce.upload_form.find('textarea');
    ce.confirm_upload = ce.upload_form.find('#confirm-upload');
    ce.submit = ce.upload_form.find('input.data.upload');

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
