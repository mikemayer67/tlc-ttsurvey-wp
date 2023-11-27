var ce = {};
var page_top = 0;
var menubar_fixed = false;

function setup_menubar()
{
  jQuery(window).on('scroll',function(e) {
    var survey_top = ce.container.offset().top;
    var survey_height = ce.container.outerHeight();
    var survey_bottom = survey_top + survey_height;
    var menubar_height = ce.menubar_box.outerHeight(); 
    var scroll_top = jQuery(window).scrollTop() + page_top;

    if( scroll_top > survey_bottom - menubar_height ) {
      if(menubar_fixed) {
        menubar_fixed = false;
        ce.menubar_box.css({
          'position':'absolute',
          'top':(survey_height-menubar_height) + 'px',
          'left':0,
          'width':'100%',
        });
      }
    } else if(scroll_top > survey_top ) {
      if(!menubar_fixed) {
        var survey_left = ce.container.offset().left;
        var survey_width = ce.container.outerWidth();
        menubar_fixed = true;
        ce.menubar_box.css({
          'position':'fixed',
          'top':page_top,
          'left':survey_left + 'px',
          'width':survey_width + 'px',
        });
      }
    } else {
      if(menubar_fixed) {
        menubar_fixed = false;
        ce.menubar_box.css({
          'position':'absolute',
          'top':0,
          'left':0,
          'width':'100%',
        });
      }
    }
  });
}

function setup_elements()
{
  ce.container = jQuery('#survey');
  ce.menubar_box = ce.container.find('.menubar-box');

  ce.wpadminbar = jQuery('#wpadminbar');
  if(ce.wpadminbar) {
    if(ce.wpadminbar.css('position') == 'fixed') {
      page_top = ce.wpadminbar.height();
    }
  }

  setup_menubar();
}

jQuery(document).ready(
  function($) {
    setup_elements();
  }
);
