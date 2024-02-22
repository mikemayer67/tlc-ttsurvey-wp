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
  }
);
