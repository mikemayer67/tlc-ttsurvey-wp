
function register_validation($,form)
{
  submit = $(form).find('button.submit').prop('disabled',true);

  username = $(form).find('.username input');
  username.on(
    'change',
    function() {
      username.css('background-color','yellow');
      setTimeout(function() { username.css('background-color','white') }, 1000);
    }
  );
}




jQuery(document).ready(
  function($) {
    register_form = $('div#tlc-ttsurvey div.register form')
    if( register_form.length ) { register_validation($,register_form); }
  }
);

