
jQuery(document).ready(
  function($) {
    var lock = $('input[name=lock]');

    $(document).on( 'heartbeat-send', function(event,data) {
      data.tlc_ttsurvey_lock = lock.val();
    });

    $(document).on( 'heartbeat-tick', function(event,data) {
      var rc = data.tlc_ttsurvey_lock;
    });
  }
);
