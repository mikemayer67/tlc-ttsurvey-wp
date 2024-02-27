var ce = {};

var menubar_top = -1;
var menubar_fixed = false;
var profile_keyup_timer = null;

/******************************************************************************
* Layout handlers
******************************************************************************/

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


/******************************************************************************
* Editor handlers
******************************************************************************/

function ajax_query( query, data, response_handler )
{
  data.action = 'tlc_ttsurvey';
  data.nonce = menubar_vars.nonce;
  data.query = 'shortcode/' + query;

  jQuery.post(menubar_vars.ajaxurl, data, response_handler, 'json');
}


function start_editor(e,key,action)
{
  e.preventDefault();

  ce.pe_submit.prop('disabled',true);
  ce.profile_editor[0].dataset.target = key;
  ce.profile_editor[0].dataset.action = action;

  var editor = ce.profile_editor.find(`.editor-body.${key}`);
  var entry = editor.find('.text-entry');
  var error = editor.find('.error');

  if(action == 'edit') {
    entry.val(entry[0].dataset.default);
    entry.removeClass(['invalid','empty']);
  } else {
    entry.val('');
    entry.removeClass('invalid').addClass('empty');
  }

  error.html('');

  ce.profile_modal.show();
  ce.profile_modal.find('.editor-body').hide();
  editor.show();

  update_layout(e);
}

function validate_profile_entry(entry)
{
  var key = entry.attr('name');

  console.log(`validate ${key}`);

  var entry = ce.pe_entry[key];
  var error = ce.profile_editor.find(`.editor-body.${key}`).find('.error');

  ajax_query(
    'validate_profile_update',
    {
      key:(key=='name'?'fullname':key),
      value:entry.val(),
    },
    function(response) {
      entry.removeClass(['invalid','empty']);
      if(response.success) {
        error.html('');
        default_val = entry[0].dataset.default;
        if(entry.val() != default_val) {
          ce.pe_submit.prop('disabled',false);
        }
      }
      else if(response.data == "#empty") 
      {
        error.html('');
        entry.addClass('empty');
      }
      else 
      {
        error.html(response.data);
        entry.addClass('invalid');
      }
    }
  );
}

function update_profile_entry(e)
{
  console.log('update profile entry');
}


function handle_pe_cancel(e)
{
  e.preventDefault();
  ce.profile_modal.hide();
  update_layout(e);
}

function handle_pe_input(e)
{
  var entry = jQuery(this);
  if(profile_keyup_timer) {
    clearTimeout(profile_keyup_timer);
  }
  profile_keyup_timer = setTimeout( 
    function() {
      profile_keyup_timer = null;
      validate_profile_entry(entry);
    }, 
    500
  );
}

function setup_info_trigger()
{
  var trigger = jQuery(this);
  var tgt = jQuery('#' + trigger.data('target'));
  trigger.on('click', function() {
    if(tgt.hasClass('visible')) {
      tgt.removeClass('visible').hide();
      jQuery('#tlc-ttsurvey .modal .dialog').css('z-index',15);
    } else {
      jQuery('#tlc-ttsurvey .info-box').removeClass('visible').hide();
      tgt.addClass('visible').show();
      jQuery('#tlc-ttsurvey .modal .dialog').css('z-index',14);
    }
  });
}

/******************************************************************************
* general setup
******************************************************************************/

function setup_elements()
{
  ce.container = jQuery('#survey');
  ce.wpadminbar = jQuery('#wpadminbar');
  ce.menubar = ce.container.find('nav.menubar');
  ce.user_menu = ce.menubar.find('.menu.user');

  ce.profile_modal  = ce.container.find('.modal.user-profile');
  ce.profile_editor = ce.profile_modal.find('.dialog');

  ce.pe_cancel = ce.profile_editor.find('.cancel');
  ce.pe_submit = ce.profile_editor.find('.submit');
  ce.pe_editor = ce.profile_editor.find('.editor-body');
  ce.pe_entry = ce.profile_editor.find('.text-edit');

  ce.matchMedia = window.matchMedia("(max-width:480px)");
  ce.matchMedia.addEventListener('change',watch_media);
  watch_media(null);

  jQuery(window).on('scroll',update_layout);
  jQuery(window).on('resize',update_layout);

  // menubar

  ce.user_menu.find('.edit-user-name').on('click', function(e) { start_editor(e,'name','edit'); } );
  ce.user_menu.find('.edit-user-email').on('click', function(e) { start_editor(e,'email','edit'); } );
  ce.user_menu.find('.add-user-email').on('click', function(e) { start_editor(e,'email','add'); } );
  ce.user_menu.find('.change-password').on('click', function(e) { start_editor(e,'password','change'); } );

  // profile editor

  ce.pe_cancel.on('click', handle_pe_cancel);
  ce.pe_entry.on('input', handle_pe_input);

  ce.pe_submit.prop('disabled',true);
  ce.pe_submit.on('click'.update_profile_entry);

  // info

  jQuery('#tlc-ttsurvey .info-box').hide();
  jQuery('#tlc-ttsurvey .info-trigger').each(setup_info_trigger);
}

jQuery(document).ready(
  function($) { setup_elements(); }
);
