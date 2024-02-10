var ce = {};
var page_top = 0;
var menubar_fixed = false;

function setup_menubar()
{
  jQuery(window).on('scroll',function(e) {
    var survey_top = ce.container.offset().top;
    var survey_height = ce.container.outerHeight();
    var survey_bottom = survey_top + survey_height;
    var menubar_height = ce.menubar.outerHeight(); 
    var scroll_top = jQuery(window).scrollTop() + page_top;

    if( scroll_top > survey_bottom - menubar_height ) {
      if(menubar_fixed) {
        menubar_fixed = false;
        ce.menubar.css({
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
        ce.menubar.css({
          'position':'fixed',
          'top':page_top,
          'left':survey_left + 'px',
          'width':survey_width + 'px',
        });
      }
    } else {
      if(menubar_fixed) {
        menubar_fixed = false;
        ce.menubar.css({
          'position':'absolute',
          'top':0,
          'left':0,
          'width':'100%',
        });
      }
    }
  });
}

function setup_user_profile()
{
  ce.profile_button.on('click', function(e) {
    e.preventDefault();
    ce.profile_modal.show();
  })

  ce.profile_cancel.on('click', function(e) {
    e.preventDefault();
    ce.profile_modal.hide();
  });

  jQuery(window).on('scroll',function(e) {
    if( ce.profile_modal.is(':visible') ) {
      const survey_top = ce.container.offset().top;
      const survey_width = ce.container.outerWidth();
      const survey_bottom = survey_top + ce.container.outerHeight();
      const menubar_top = ce.menubar.offset().top;
      const menubar_bottom = menubar_top + ce.menubar.outerHeight();
      const editor_top = menubar_bottom + 5;
      const editor_bottom = editor_top + ce.profile_editor.outerHeight();
      const editor_width = 0.8 * survey_width;

      if( editor_bottom > survey_bottom - 5 ) {
        ce.profile_editor.hide();
      } else {
        ce.profile_editor.show();
        if( menubar_fixed ) {
          ce.profile_editor.css( {
            'position':'fixed',
            'top':page_top + 35,
            'width':editor_width,
          });
        } else {
          ce.profile_editor.css( {
            'position':'absolute',
            'top': 35,
            'width':editor_width,
          });
        }
      }
    }
  });
}

function setup_elements()
{
  ce.container = jQuery('#survey');
  ce.menubar = ce.container.find('nav.menubar');
  ce.user_menu = ce.menubar.find('.menu.user');
  ce.profile_button = ce.menubar.find('a.user-profile');
  ce.profile_modal  = ce.container.find('.modal.user-profile');
  ce.profile_editor = ce.profile_modal.find('.dialog.user-profile');
  ce.profile_cancel = ce.profile_editor.find('.cancel');

  ce.wpadminbar = jQuery('#wpadminbar');
  if(ce.wpadminbar) {
    if(ce.wpadminbar.css('position') == 'fixed') {
      page_top = ce.wpadminbar.height();
    }
  }

  setup_menubar();
  setup_user_profile();
}

jQuery(document).ready(
  function($) {
    setup_elements();
  }
);
