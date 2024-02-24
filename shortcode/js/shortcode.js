

function info_setup()
{
  jQuery('#tlc-ttsurvey .info-box').hide();
  jQuery('#tlc-ttsurvey .info-trigger').each(
    function() {
      var trigger = jQuery(this)
      var tgt_id = trigger.data('target');
      var tgt = jQuery('#'+tgt_id);
      trigger.on( 'click', function() { 
        if(tgt.hasClass('visible')) {
          tgt.removeClass('visible').slideUp(100)
        } else {
          jQuery('#tlc-ttsurvey .info-box').removeClass('visible').slideUp(100);
          tgt.addClass('visible').slideDown(100);
        }
      });

      tgt.on( 'click', function() { 
        tgt.removeClass('visible').slideUp(100) ;
      });
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
