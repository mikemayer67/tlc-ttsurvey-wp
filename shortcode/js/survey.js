var ce = {};
var menubar_fixed = false;

function setup_elements()
{
  ce.container = jQuery('#survey');
  ce.menubar = ce.container.find('.menubar');
}

jQuery(document).ready(
  function($) {
    setup_elements();
  }
);
