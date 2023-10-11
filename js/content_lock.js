
jQuery(document).ready(
  function($) {
    var ic = $('input.counter');

    $(document).on( 'heartbeat-send', function(event,data) {
      data.tlc_ttsurvey_counter = Number(ic.val());
    });

    $(document).on( 'heartbeat-tick', function(event,data) {
      var n = data.tlc_ttsurvey_new_counter;
      ic.val(n);
    });
  }
);
