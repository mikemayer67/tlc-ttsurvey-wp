
jQuery(document).ready(
  function($) {
    $('div.tlc-ttsurvey form .info-trigger').show();
    $('div.tlc-ttsurvey form .info').hide();

    rml_info = $("div.tlc-ttsurvey .remember-me .info");
    rml_trigger = $("div.tlc-ttsurvey .remember-me .info-trigger");
    rml_trigger.hover(
      function(e) {rml_info.slideDown(100)},
      function(e) {
        if(!rml_info.hasClass('locked')) {
          rml_info.slideUp(100);
        }
      });
      rml_trigger.on('click',function() {
        rml_info.addClass('locked').slideDown(100)
      });
      rml_info.on('click',function() {
        rml_info.removeClass('locked').slideUp(100);
      });
            
    rmr_info = $("div.tlc-ttsurvey form.register .info.remember-me");
    rmr_trigger = $("div.tlc-ttsurvey form.register .info-trigger.remember-me");
    rmr_trigger.hover(
      function(e) {rmr_info.slideDown(100)},
      function(e) {
        if(!rmr_info.hasClass('locked')) {
          rmr_info.slideUp(100);
        }
      });
      rmr_trigger.on('click',function() {
        rmr_info.addClass('locked').slideDown(100)
      });
      rmr_info.on('click',function() {
        rmr_info.removeClass('locked').slideUp(100);
      });
            
    emailinfo = $("div.tlc-ttsurvey form .info.email");
    emailtrigger = $("div.tlc-ttsurvey form .info-trigger.email");
    emailtrigger.hover(
      function(e) {emailinfo.slideDown(100)},
      function(e) {
        if(!emailinfo.hasClass('locked')) {
          emailinfo.slideUp(100);
        }
      });
      emailtrigger.on('click',function() {
        emailinfo.addClass('locked').slideDown(100)
      });
      emailinfo.on('click',function() {
        emailinfo.removeClass('locked').slideUp(100);
      });
            
  }
);

