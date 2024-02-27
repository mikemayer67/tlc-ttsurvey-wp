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
      console.log(`hide: editor_bottom:${editor_bottom} > survey_bottom:${survey_bottom}`)
      ce.profile_editor.hide();
    } else {
      console.log(`grow_container: editor_bottom:${editor_bottom} > survey_bottom:${survey_bottom}`)
      ce.profile_editor.show();
      ce.container.css({'height': editor_height + menubar_height + 5});
    }
  } else {
    ce.profile_editor.show();
    if( menubar_fixed ) {
      console.log(`fixed PE: page_top=${page_top} menubar_top=${menubar_top} top=${page_top + menubar_height + 5}`);
      ce.profile_editor.css( {
        'position':'fixed',
        'top':page_top + menubar_height + 5,
        'width':editor_width,
      });
    } else {
      console.log(`relative PE: page_top=${page_top} menubar_top=${menubar_top} top=${menubar_height + 5}`);
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

  var query_data = null;
  var editor = null;
  if( key == "name" ) {
    editor = ce.profile_editor.find('.editor-body.name');
    query_data = { key:'fullname', value:entry.val() };
  } else if( key == "email" ) {
    editor = ce.profile_editor.find('.editor-body.email');
    query_data = { key:'email', value:entry.val() };
  } else if ( (key == 'password') || (key == 'password-confirm') ) {
    editor = ce.profile_editor.find('.editor-body.password');
    query_data = {
      key:'password',
      value: ce.profile_editor.find('.editor-body.password input.primary').val(),
      confirm: ce.profile_editor.find('.editor-body.password input.confirm').val(),
    };
  }

  const default_val = entry[0].dataset.default;
  var error = editor.find('.error');
  var entries = editor.find('.text-entry');

  ajax_query(
    'validate_profile_update',
    query_data,
    function(response) {
      entries.removeClass(['invalid','empty']);
      if(response.success) {
        error.html('');
        if(entry.val() != default_val) {
          ce.pe_submit.prop('disabled',false);
        }
      }
      else if(response.data == "#empty") 
      {
        error.html('');
        entries.addClass('empty');
      }
      else 
      {
        error.html(response.data);
        entries.addClass('invalid');
      }
    }
  );
}

function update_profile_entry(e)
{
  e.preventDefault();
  const key = ce.profile_editor[0].dataset.target;
  const action = ce.profile_editor[0].dataset.action;

  var entry = ce.profile_editor.find(`.editor-body.${key} .text-entry`);
  const value = entry.val();

  ajax_query(
    'update_profile',
    {
      key:(key=="name"?"fullname":key),
      value:value,
    },
    function(response) {
      if(response.success) {
        if(key != "password") {
          entry[0].dataset.default = value;
        }
        if(key == "name") {
          ce.user_menu_name.html(value);
        }
        else if(key == "email") {
          if(value) {
            ce.edit_user_email.show();
            ce.drop_user_email.show();
            ce.add_user_email.hide();
          } else {
            ce.edit_user_email.hide();
            ce.drop_user_email.hide();
            ce.add_user_email.show();
          }
        }
        ce.profile_modal.hide();
        update_layout(e);
      } else {
        alert(response.data);
      }
    }
  );
}

function handle_pe_cancel(e)
{
  e.preventDefault();
  ce.profile_modal.hide();
  update_layout(e);
}

function handle_pe_input(e)
{
  ce.pe_submit.prop('disabled',true);

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

function drop_user_email(e)
{
  e.preventDefault();
  const ans = confirm("You will no longer receive updates on the status of your survey");
  if(ans) {
    ajax_query(
      'drop_user_email',
      {},
      function(response) {
        if(response.success) {
          var entry = ce.profile_editor.find('.editor-body.email .text-entry')
          entry[0].dataset.default = "";
          ce.edit_user_email.hide();
          ce.drop_user_email.hide();
          ce.add_user_email.show();
          alert("Email address removed");
        } else {
          alert("Failed to remove email address");
        }
      }
    );
  }
}

/******************************************************************************
* Info handlers
******************************************************************************/

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

  tgt.on( 'click', function() { 
    tgt.removeClass('visible').hide();
    jQuery('#tlc-ttsurvey .modal .dialog').css('z-index',15);
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
  ce.user_menu_name = ce.menubar.find('.menu-btn.user span.name');

  ce.profile_modal  = ce.container.find('.modal.user-profile');
  ce.profile_editor = ce.profile_modal.find('.dialog');

  ce.pe_cancel = ce.profile_editor.find('.cancel');
  ce.pe_submit = ce.profile_editor.find('.submit');
  ce.pe_editor = ce.profile_editor.find('.editor-body');
  ce.pe_entry = ce.profile_editor.find('.text-entry');

  ce.matchMedia = window.matchMedia("(max-width:480px)");
  ce.matchMedia.addEventListener('change',watch_media);
  watch_media(null);

  jQuery(window).on('scroll',update_layout);
  jQuery(window).on('resize',update_layout);

  // menubar

  ce.edit_user_name  = ce.user_menu.find('.edit-user-name');
  ce.edit_user_email = ce.user_menu.find('.edit-user-email');
  ce.add_user_email  = ce.user_menu.find('.add-user-email');
  ce.drop_user_email = ce.user_menu.find('.drop-user-email');
  ce.change_password = ce.user_menu.find('.change-password');

  ce.edit_user_name.on( 'click', function(e) { start_editor(e,'name','edit'); } );
  ce.edit_user_email.on('click', function(e) { start_editor(e,'email','edit'); } );
  ce.add_user_email.on( 'click', function(e) { start_editor(e,'email','add'); } );
  ce.drop_user_email.on('click', function(e) { drop_user_email(e); } );
  ce.change_password.on('click', function(e) { start_editor(e,'password','change'); } );

  if( ce.user_menu.data('email') ) {
    ce.add_user_email.hide();
  } else {
    ce.edit_user_email.hide();
    ce.drop_user_email.hide();
  }

  // profile editor

  ce.pe_cancel.on('click', handle_pe_cancel);
  ce.pe_entry.on('input', handle_pe_input);

  ce.pe_submit.prop('disabled',true);
  ce.pe_submit.on('click',update_profile_entry);

  // info

  jQuery('#tlc-ttsurvey .info-box').hide();
  jQuery('#tlc-ttsurvey .info-trigger').each(setup_info_trigger);
}

jQuery(document).ready(
  function($) { setup_elements(); }
);
