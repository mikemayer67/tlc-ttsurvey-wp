<?php
namespace TLC\TTSurvey;

if( ! defined('WPINC') ) { die; }

require_once plugin_path('shortcode/setup.php');
require_once plugin_path('shortcode/login/_elements.php');

echo "<noscript><div>";
echo "<p class='noscript'>Password reset requires that Javascript be enabled.</p>";
$return_url = survey_url();
echo "<a href='$return_url'>Return to login page</a>";
echo "</div></noscript>";

start_login_form("Password Reset",'pwreset');

add_login_instructions([
  'Enter your userid and the new password you would like to use.'
]);

add_login_input('userid',array(
  "info" => <<<INFO
The userid entered here must match the one from the login recovery email.
If there was more than one userid in that email, it must match the one
corresponsing to the password reset link you used to get here.
INFO
));

add_login_input("password",array(
  "confirm" => true,
  "info" => <<<INFO
Used to log into the survey
<p class=info-list><b>must</b> be between 8 and 128 characters</p>
<p class=info-list><b>must</b> contain at least one letter</p>
<p class=info-list><b>may</b> contain: !@%^*-_=~,.</p>
<p class=info-list><b>may</b> contain spaces</p>
INFO
));

add_login_submit("Change Password",'pwreset',true);

close_login_form();
