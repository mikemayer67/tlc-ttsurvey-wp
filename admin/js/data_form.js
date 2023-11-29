var ce = {};

var validation_timer = null;

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


async function upload_file(file)
{
  const json = await (new Response(file)).text();
  ce.upload_file.val('');
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

function handle_upload_file(e)
{
  e.preventDefault();
  const files = ce.upload_file.prop('files');
  if(files) {
    stop_validation_timer();
    ce.json_data.val("");
    upload_file(files[0]);
  }
}

function handle_json_input(e)
{
  start_validation_timer();
  clear_status();
}

jQuery(document).ready(
  function($) {
    ce.upload_form = $('#tlc-ttsurvey-admin form.data.upload');
    ce.upload_file = ce.upload_form.find('#upload-file');
    ce.upload_file_trigger = ce.upload_form.find('a.data.upload');
    ce.data_status = ce.upload_form.find('span.status');
    ce.json_data   = ce.upload_form.find('textarea');

    ce.upload_file_trigger.on('click',function(e) {
      e.preventDefault();
      ce.upload_file.click();
    });

    ce.upload_file.on('change',handle_upload_file);
    ce.json_data.on('input',handle_json_input);
  }
);
