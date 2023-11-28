var ce = {};

var validation_timer = null;

function start_validation_timer()
{
  stop_validation_timer();
  validation_timer = setTimeout(validate_json,500);
}

function stop_validation_timer()
{
  if(validation_timer) {
    clearTimeout(validation_timer);
    validation_timer = null;
  }
}

function validate_json()
{
  console.log("validate json");
  jQuery.post(
    form_vars['ajaxurl'],
    {
      action:'tlc_ttsurvey',
      nonce:form_vars['nonce'],
      query:'admin/validate_json_data',
      json_data:ce.json_data.val(),
    },
    function(response) {
      if( 'error' in response ) {
        set_error_status(response.error);
      }
      else if( 'warning' in response ) {
        set_warning_status(response.warning);
      }
      else {
        if(!ce.data_status.hasClass('info')) { clear_status(); }
      }
    },
    'json',
  );
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
    const data = JSON.parse(json,true);
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
