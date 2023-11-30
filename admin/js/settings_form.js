var ce = {};

function handle_form_submit(event)
{
  event.preventDefault();

  var url = '';
  var data = {
    action:'tlc_ttsurvey',
    nonce:form_vars['nonce'],
    query:'admin/submit_settings_form',
  };

  const inputs = ce.form.serializeArray();
  for(const input of inputs) {
    data[input.name] = input.value;
  }

  jQuery.post(
    form_vars['ajaxurl'],
    data,
    function(response) {
      if(response.success) {
        window.location.href = form_vars.overview;
      } else {
        alert("failed to save settings: " + response.data);
      }
    },
    'json',
  );
}

function update_primary(event)
{
  console.log('update_primary');
  var has_primary = false;
  ce.primary.each( function() {
    const id = this.value
    const manage = ce.manage.filter('.'+id);
    var can_manage = true;
    if(manage[0].type == 'checkbox') {
      can_manage = manage.prop('checked');
    }
    if(can_manage) {
      if(this.checked) { has_primary = true; }
      jQuery(this).show();
    } else {
      this.checked = false;
      jQuery(this).hide();
    }
  });
  
  if(has_primary) {
    ce.admin_error.hide()
    ce.submit.prop('disabled',false);
  } else {
    ce.admin_error.html("must select a primary admin").show();
    ce.submit.prop('disabled',true);
  }
}

function handle_clear_log(event)
{
  event.preventDefault()
  if( confirm('Purge all plugin log data') ) {
    jQuery.post(
      form_vars['ajaxurl'],
      {
        action:'tlc_ttsurvey',
        nonce:form_vars['nonce'],
        query:'admin/clear_log',
      },
    );
  }
}

jQuery(document).ready(
  function($) {
    ce.form = $('#tlc-ttsurvey-admin div.settings form');
    ce.submit = ce.form.find('input.submit');
    ce.primary = ce.form.find('input.primary');
    ce.manage = ce.form.find('input.manage');
    ce.admin_error = ce.form.find('.admin-error');
    ce.clear_log = ce.form.find('button.clear-log');

    update_primary();
    ce.manage.on('change',update_primary);
    ce.primary.on('change',update_primary);

    ce.form.on('submit', handle_form_submit);
    ce.clear_log.on('click', handle_clear_log);
  }
);
