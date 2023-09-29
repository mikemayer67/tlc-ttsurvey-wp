
jQuery(document).ready(
  function($) {
    var info_trigger_timer;
    $('div#tlc-ttsurvey form .info-trigger').show();
    $('div#tlc-ttsurvey form .info').hide();

    $('div#tlc-ttsurvey form .info-trigger').each(
      function() {
        var trigger = $(this)
        var tgt_id = trigger.data('target');
        var tgt = $('#'+tgt_id);
        trigger.hover(
          function(e) {
            if(info_trigger_timer) { 
              clearTimeout(info_trigger_timer); 
              info_trigger_timer=null;
            }
            info_trigger_timer = setTimeout(function() {
              tgt.slideDown(100)
            }, 100);
          },
          function(e) { 
            if(info_trigger_timer) {
              clearTimeout(info_trigger_timer); 
              info_trigger_timer=null;
            }
            if(!tgt.hasClass('locked')) { tgt.slideUp(100) } 
          },
        );
        trigger.on( 'click', function() { 
          if(tgt.hasClass('locked')) {
            tgt.removeClass('locked').slideUp(100)
          } else {
            tgt.addClass('locked').slideDown(100)
          }
        });
        tgt.on( 'click', function() { tgt.removeClass('locked').slideUp(100) });
      }
    );
            
  }
);

