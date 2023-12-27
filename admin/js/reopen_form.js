var pid = null;
var ce = {};

function handle_reopen_survey(event)
{
  event.preventDefault();

  jQuery.post(
    reopen_vars['ajaxurl'],
    {
      action:'tlc_ttsurvey',
      nonce:reopen_vars['nonce'],
      query:'admin/reopen_survey',
      pid:pid,
    },
    function(response) {
      if(response.success) {
        window.location.reload(true);
      }
    },
    'json',
  );
}

jQuery(document).ready( function($) {
  ce.form = $('#tlc-ttsurvey-admin form.reopen-survey');

  pid = ce.form.find('input[name=pid]').eq(0).val();

  ce.form.on('submit', handle_reopen_survey);
});
