
jQuery(document).ready(
  function($) {
    email_info = $("div.tlc-ttsurvey form .email-info");
    email_info_trigger = $("div.tlc-ttsurvey form .email-info-trigger");
    email_info.hide();
    email_info_trigger.hover(
      function(e) {email_info.slideDown(100)},
      function(e) {
        if(!email_info.hasClass('locked')) {
          email_info.slideUp(100);
        }
      }
    );
    email_info_trigger.on('click',function() {
      email_info.addClass('locked').slideDown(100)
    });
    email_info.on('click',function() {
      email_info.removeClass("locked").slideUp(100);
    });
  }
);

