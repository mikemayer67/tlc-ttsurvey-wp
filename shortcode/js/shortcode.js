

function info_setup()
{
  var info_trigger_timer = null;

  jQuery('#tlc-ttsurvey .info-box').hide();

  jQuery('#tlc-ttsurvey .info-trigger').each(
    function() {
      var trigger = jQuery(this)
      var tgt_id = trigger.data('target');
      var tgt = jQuery('#'+tgt_id);
      trigger.on('mouseenter', function(e) {
        if(info_trigger_timer) { 
          clearTimeout(info_trigger_timer); 
          info_trigger_timer=null;
        }
        info_trigger_timer = setTimeout(function() {
          tgt.slideDown(100)
        }, 500);
      });
      trigger.on('mouseleave',function(e) {
        if(info_trigger_timer) {
          clearTimeout(info_trigger_timer); 
          info_trigger_timer=null;
        }
        if(!tgt.hasClass('locked')) { 
          tgt.slideUp(100) 
        } 
      });
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


jQuery(document).ready(
  function($) { 
    // show javascript required elements
    jQuery('#tlc-ttsurvey .javascript-required').show();

    if(shortcode_vars.scroll) {
      const container = jQuery('#tlc-ttsurvey').get(0);
      const position = container.getBoundingClientRect();
      const top = position.top + window.scrollY - 50;
      window.scrollTo(0,top);
    }

    info_setup();
  }
);
