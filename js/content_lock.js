
jQuery(document).ready(
  function($) {
    var lock = $('input[name=lock]');
    var pid = $('input[name=pid]');
    var submit = $('form.edit-survey input[type=submit]');
    var inputs = $('form.edit-survey textarea');
    var lock_info = $('#tlc-ttsurvey-admin .content .info.lock');

    if(lock.val() == 0) {
      submit.prop('disabled',false);
      inputs.prop('readonly',false);
    }

    $(document).on( 'heartbeat-send', function(event,data) {
      data.tlc_ttsurvey_lock = {'pid':pid.val(), 'lock':lock.val()};
    });

    $(document).on( 'heartbeat-tick', function(event,data) {
      var rc = data.tlc_ttsurvey_lock;
      if(rc.has_lock) {
        lock.val(0);
        submit.prop('disabled',false);
        inputs.prop('readonly',false);
        lock_info.removeClass('lock').addClass('unlocked');
        lock_info.html("You may now edit the survey content.");
      }
    });
  }
);
