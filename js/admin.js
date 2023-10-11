
jQuery(document).ready(
  function($) {
    $('.requires-javascript').show();

    // new survey form
    var ns_form = $('form.tlc.new-survey');
    var ns_new_name = ns_form.find('input.new-name'); 
    var ns_error = ns_form.find('span.error');
    var ns_submit = ns_form.find('input.submit');
    ns_new_name.on('keyup',function() {
      ns_existing_names = ns_form.find('input.existing')[0].value;
      ns_existing_names = JSON.parse(ns_existing_names);
      new_name = ns_new_name.val()
      err = "";
      if(new_name.length<4) {
        err = "too short";
      }
      else if($.inArray(new_name,ns_existing_names)>=0) {
        err = "existing survey";
      }
      else if(!/^[a-zA-Z0-9., -]+$/.test(new_name)) {
        err = "invalid name";
      }
      ns_submit.prop('disabled',err.length>0);
      ns_error.html(err);
    });
  }
);

