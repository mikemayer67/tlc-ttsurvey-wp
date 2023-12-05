
export function hold()
{
  jQuery.post(
    form_vars.ajaxurl,
    {
      action:'tlc_ttsurvey',
      nonce: form_vars.nonce,
      query: 'admin/obtain_content_lock',
    },
    function(response) {
      if(!response.has_lock) {
        window.location.reload(true);
      }
    },
    'json',
  );
}

