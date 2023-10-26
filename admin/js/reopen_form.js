var pid = null;
var ce = {};

function handle_reopen_survey(event)
{
  event.preventDefault();
  alert("Reopen " + pid);
}

jQuery(document).ready( function($) {
  ce.form = $('#tlc-ttsurvey-admin form.reopen-survey');

  pid = ce.form.find('input[name=pid]').eq(0).val();

  ce.form.on('submit', handle_reopen_survey);
});
