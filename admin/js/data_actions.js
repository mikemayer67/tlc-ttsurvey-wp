var ce = {};

function handle_dump(e)
{
  var data_link = jQuery(this);
  var data = {
    action:'tlc_ttsurvey',
    nonce:data_vars['nonce'],
    query:'admin/data_actions',
  };
  jQuery.post(
    data_vars['ajaxurl'],
    data,
    function(response) {
      var data_link_r = jQuery(this);
      console.log(response);
    }
  );
}


jQuery(document).ready(
  function($) {
    ce.data_actions = $('#tlc-ttsurvey-admin div.data');
    ce.dump_actions = ce.data_actions.find('.dumps a');

    ce.dump_actions.on('click',handle_dump);
  }
);
