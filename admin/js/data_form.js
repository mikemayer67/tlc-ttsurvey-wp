var ce = {};

function handle_upload_file(e)
{
  e.preventDefault();
  const files = ce.upload_file.prop('files');
  if(files) {
    const file = files[0];
    if(file.type != 'application/json') {
      alert("The select file must be a JSON file");
      return;
    }
  }
}

jQuery(document).ready(
  function($) {
    ce.upload_form = $('#tlc-ttsurvey-admin form.data.upload');
    ce.upload_file = ce.upload_form.find('div.upload-file input');
    ce.json_data   = ce.upload_form.find('textarea');

    ce.upload_file.on('change',handle_upload_file);
  }
);
