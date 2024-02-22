var ce = {};

var menubar_top = -1;
var menubar_fixed = false;


function setup_user_menu()
{
  ce.user_menu.find('.edit-user-name').on('click', function(e) {
    e.preventDefault();
    ce.profile_modal.show();
    ce.profile_modal.find('form').hide();
    ce.profile_modal.find('form.name').show();
    update_layout(e);
  });
  ce.user_menu.find('.edit-user-email').on('click', function(e) {
    e.preventDefault();
    ce.profile_modal.show();
    ce.profile_modal.find('form').hide();
    ce.profile_modal.find('form.email').show();
    update_layout(e);
  });
  ce.user_menu.find('.add-user-email').on('click', function(e) {
    e.preventDefault();
    ce.profile_modal.show();
    ce.profile_modal.find('form').hide();
    ce.profile_modal.find('form.email').show();
    update_layout(e);
  });
  ce.user_menu.find('.change-password').on('click', function(e) {
    e.preventDefault();
    ce.profile_modal.show();
    ce.profile_modal.find('form').hide();
    ce.profile_modal.find('form.password').show();
    update_layout(e);
  });

  ce.profile_cancel.on('click', function(e) {
    e.preventDefault();
    ce.profile_modal.hide();
    update_layout(e);
  });

  ce.profile_editor.find('form.name .submit').on('click',update_name);
  ce.profile_editor.find('form.email .submit').on('click',update_email);
  ce.profile_editor.find('form.password .submit').on('click',update_password);
}

function update_name(e) {
  e.preventDefault();
  alert("Update name");
}

function update_email(e) {
  e.preventDefault();
  alert("Update email");
}

function update_password(e) {
  e.preventDefault();
  alert("Update password");
}


function update_layout(e) {
  var page_top = 0;
  if(ce.wpadminbar && ce.wpadminbar.css('position') == 'fixed') {
    page_top = ce.wpadminbar.height();
  }
  const scroll_top = jQuery(window).scrollTop() + page_top;

  const survey_height = ce.container.outerHeight();
  const survey_width = ce.container.outerWidth();
  const survey_top = ce.container.offset().top;
  const survey_left = ce.container.offset().left;
  const survey_bottom = survey_top + survey_height;

  // Menubar

  const menubar_height = ce.menubar.outerHeight(); 

  const pos = ce.wpadminbar.css('position');
  
  if( scroll_top < survey_top ) {
    ce.menubar.css({
      'position':'absolute',
      'top':0,
      'left':0,
      'width':'100%',
    });
    menubar_fixed = false;
    menubar_top = survey_top;
  }
  else if( scroll_top > survey_bottom - menubar_height ) {
    ce.menubar.css({
      'position':'absolute',
      'top':survey_height - menubar_height,
      'left':0,
      'width':'100%',
    });
    menubar_fixed = false;
    menubar_top = survey_bottom - menubar_height;
  }
  else {
    ce.menubar.css({
      'position':'fixed',
      'top':page_top,
      'left':survey_left,
      'width':survey_width,
    });
    menubar_fixed = true;
    menubar_top = scroll_top;
  }

  if(e.type == "resize" && menubar_fixed) {
    ce.menubar.css({
      'left':survey_left,
      'width':survey_width,
    });
  }

  // Profile Editor
  
  if( ! ce.profile_modal.is(':visible') ) { return; }

  const menubar_bottom = menubar_top + menubar_height;

  const editor_height = ce.profile_editor.outerHeight();
  const editor_width = 0.8 * survey_width;

  const editor_top = menubar_bottom + 5;
  const editor_bottom = editor_top + editor_height;

  if( editor_bottom > survey_bottom - 5 ) {
    if( menubar_fixed || (editor_height < survey_height - menubar_height - 5 )) {
      ce.profile_editor.hide();
    } else {
      ce.profile_editor.show();
      ce.container.css({'height': editor_height + menubar_height + 5});
    }
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

function watch_media(e)
{
  var menubox;
  if( ce.matchMedia.matches ) {
    menubox = ce.menubar.find('.menubox.mobile');
  } else {
    menubox = ce.menubar.find('.menubox.main');
  }
  const status_item = ce.menubar.find('.menubar-item.status');
  const user_item = ce.menubar.find('.menubar-item.user');
  menubox.append(status_item);
  menubox.append(user_item);
}


function setup_elements()
{
  ce.container = jQuery('#survey');
  ce.wpadminbar = jQuery('#wpadminbar');
  ce.menubar = ce.container.find('nav.menubar');
  ce.user_menu = ce.menubar.find('.menu.user');
  ce.profile_modal  = ce.container.find('.modal.user-profile');
  ce.profile_editor = ce.profile_modal.find('.dialog');
  ce.profile_cancel = ce.profile_editor.find('.cancel');

  ce.matchMedia = window.matchMedia("(max-width:480px)");
  ce.matchMedia.addEventListener('change',watch_media);
  watch_media(null);

  jQuery(window).on('scroll',update_layout);
  jQuery(window).on('resize',update_layout);

  setup_user_menu();
}

jQuery(document).ready(
  function($) {
    setup_elements();
  }
);
