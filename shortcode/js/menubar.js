var ce = {};

var menubar_top = -1;
var menubar_fixed = false;
var profile_keyup_timer = null;


function setup_user_menu()
{
  ce.user_menu.find('.edit-user-name').on('click', function(e) {
    e.preventDefault();
    ce.profile_modal.show();
    ce.profile_modal.find('.editor-body').hide();
    ce.profile_modal.find('.editor-body.name').show();
    update_layout(e);
  });
  ce.user_menu.find('.edit-user-email').on('click', function(e) {
    e.preventDefault();
    ce.profile_modal.show();
    ce.profile_modal.find('.editor-body').hide();
    ce.profile_modal.find('.editor-body.email').show();
    update_layout(e);
  });
  ce.user_menu.find('.add-user-email').on('click', function(e) {
    e.preventDefault();
    ce.profile_modal.show();
    ce.profile_modal.find('.editor-body').hide();
    ce.profile_modal.find('.editor-body.email').show();
    update_layout(e);
  });
  ce.user_menu.find('.change-password').on('click', function(e) {
    e.preventDefault();
    ce.profile_modal.show();
    ce.profile_modal.find('.editor-body').hide();
    ce.profile_modal.find('.editor-body.password').show();
    update_layout(e);
  });
}

function setup_profile_editor()
{
  ce.profile_cancel.on('click', function(e) {
    e.preventDefault();
    ce.profile_modal.hide();
    update_layout(e);
  });

  ce.name_entry.on('input',function() {
    ce.name_submit.prop('disabled',true);
    validate_profile_input(validate_name);
  });

  ce.email_entry.on('input',function() {
    ce.name_submit.prop('disabled',true);
    validate_profile_input(validate_email);
  });

  ce.name_submit.on('click',update_name);
  ce.email_submit.on('click',update_email);
  ce.password_submit.on('click',update_password);
}

function validate_profile_input(validation_function)
{
  if(profile_keyup_timer) { clearTimeout(profile_keyup_timer); }
  profile_keyup_timer = setTimeout(
    function() {
      profile_keyup_timer = null;
      validation_function();
    },
    500,
  );
}

function validate_name()
{
  console.log('validate_name');
}

function validate_email()
{
  console.log('validate_email');
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
        'top':page_top + menubar_height + 5,
        'width':editor_width,
      });
    } else {
      ce.profile_editor.css( {
        'position':'relative',
        'top': menubar_height + 5,
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
  ce.profile_submit = ce.profile_editor.find('.submit');

  ce.name_editor = ce.profile_editor.find('.editor-body.name');
  ce.email_editor = ce.profile_editor.find('.editor-body.email');
  ce.password_editor = ce.profile_editor.find('.editor-body.password');

  ce.name_entry = ce.name_editor.find('.text-entry');
  ce.email_entry = ce.email_editor.find('.text-entry');
  ce.password_primary_entry = ce.password_editor.find('.text-entry.primary');
  ce.password_confirm_entry = ce.password_editor.find('.text-entry.confirm');

  ce.name_cancel = ce.name_editor.find('.cancel');
  ce.email_cancel = ce.email_editor.find('.cancel');
  ce.password_cancel = ce.password_editor.find('.cancel');

  ce.name_submit = ce.name_editor.find('.submit');
  ce.email_submit = ce.email_editor.find('.submit');
  ce.password_submit = ce.password_editor.find('.submit');

  ce.matchMedia = window.matchMedia("(max-width:480px)");
  ce.matchMedia.addEventListener('change',watch_media);
  watch_media(null);

  jQuery(window).on('scroll',update_layout);
  jQuery(window).on('resize',update_layout);
}


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
          tgt.removeClass('visible').hide();
          jQuery('#tlc-ttsurvey .modal .dialog').css('z-index',15);
        } else {
          jQuery('#tlc-ttsurvey .info-box').removeClass('visible').hide();
          tgt.addClass('visible').show();
          jQuery('#tlc-ttsurvey .modal .dialog').css('z-index',14);
        }
      });

      tgt.on( 'click', function() { 
        tgt.removeClass('visible').hide();
        jQuery('#tlc-ttsurvey .modal .dialog').css('z-index',15);
      });
    }
  );
}

jQuery(document).ready(
  function($) {
    setup_elements();
    setup_user_menu();
    setup_profile_editor();
    info_setup();
  }
);
