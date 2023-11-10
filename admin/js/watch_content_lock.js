function watch_lock()
{
  jQuery.post(
    watch_vars.ajaxurl,
    {
      action:'tlc_ttsurvey',
      nonce:watch_vars.nonce,
      query:'admin/obtain_content_lock',
    },
    function(response) {
      if(response.has_lock) {
        window.location.reload(true);
      }
    },
    'json',
  );
}

jQuery(document).ready( function($) {
  setInterval(watch_lock,10000);
});
