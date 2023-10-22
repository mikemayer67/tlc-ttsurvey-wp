function watch_lock()
{
  jQuery.post(
    watch_vars.ajaxurl,
    {
      action:'tlc_ttsurvey',
      nonce:watch_vars.nonce,
      query:'watch_content_lock',
    },
    function(response) {
      if(response.has_lock) {
        window.location.href = watch_vars.content_url;
      }
    },
    'json',
  );
}

jQuery(document).ready( function($) {
  setInterval(watch_lock,10000);
});
