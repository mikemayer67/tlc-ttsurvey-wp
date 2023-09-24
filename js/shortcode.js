
jQuery(document).ready(
  function($) {
    $('div#tlc-ttsurvey form .info-trigger').show();
    $('div#tlc-ttsurvey form .info').hide();

    $('div#tlc-ttsurvey form .info-trigger').each(
      function() {
        var trigger = $(this)
        var tgt_id = trigger.data('target');
        var tgt = $('#'+tgt_id);
        trigger.hover(
          function(e) { tgt.slideDown(100) },
          function(e) { if(!tgt.hasClass('locked')) { tgt.slideUp(100) } },
        );
        trigger.on( 'click', function() { tgt.addClass('locked').slideDown(100) });
        tgt.on( 'click', function() { tgt.removeClass('locked').slideUp(100) });
      }
    );
            
  }
);

