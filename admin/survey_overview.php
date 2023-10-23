<?php
namespace TLC\TTSurvey;

if(!defined('WPINC')) { die; }

if( !plugin_admin_can('view') ) { wp_die('Unauthorized user'); }

require_once plugin_path('include/settings.php');
require_once plugin_path('include/surveys.php');
require_once plugin_path('include/logger.php');


echo "<div class=overview>";
echo "<h2>Survey Settings</h2>";
add_tlc_settings_overview();
add_tlc_survey_usage();
echo "</div>";

function add_tlc_settings_overview()
{
  $current = current_survey();
  echo "<table>";
  add_current_survey_overview($current);
  add_past_survey_overview($current);
  add_admins_overview();
  add_survey_url();
  add_advanced();
  echo "</table>";
}

function add_current_survey_overview($current)
{
  if($current) {
    $name = $current['name'];
    $status = $current['status'];
  } else {
    $name = "None";
    $status = "(create/reopen one on the Content tab)";
  }

  echo "<tr>";
  echo "  <td class='label'>Current Survey</td>";
  echo "  <td class='value'>";
  echo "    <table>";
  echo "      <tr>";
  echo "        <td class='name'>$name</td>";
  echo "        <td class='value'>$status</td>";
  echo "      </tr>";
  echo "    </table>";
  echo "  </td>";
  echo "</tr>";
}

function add_past_survey_overview($current)
{
  echo "<tr>";
  echo "  <td class='label'>Past Surveys</td>";
  echo "  <td class='value'>";
  echo "    <table>";

  $catalog = survey_catalog();
  if($catalog) {
    $current_name = $current['name'];
    $others = array();
    foreach($catalog as $post_id=>$survey) {
      $name = $survey['name'];
      $status = $survey['status'];
      if(strcmp($current_name,$name)!=0) { $others[$name] = $status; }
    }
    krsort($others);

    foreach($others as $name=>$status) {
      echo "<tr><td class='name'>$name</td><td class='value'>$status</td></tr>";
    }

  } else {
    echo "<tr><td class='name'>n/a</td></tr>";
  }

  echo "    </table>";
  echo "  </td>";
  echo "</tr>";
}

function add_admins_overview()
{
  echo "<tr>";
  echo "  <td class='label'>Admins</td>";
  echo "  <td class='value'>";
  echo "    <table>";

  $caps = survey_capabilities();

  foreach(get_users() as $user) {
    $id = $user->id;
    $name = $user->display_name;
    $responses = $caps['responses'][$id];
    $content = $caps['content'][$id];
    $user_caps = array();
    if( $caps['manage'][$id] ) { $user_caps[] = "Manage"; }
    if( $caps['content'][$id] ) { $user_caps[] = "Content"; }
    if( $caps['responses'][$id] ) { $user_caps[] = "Responses"; }
    if( !empty($user_caps) ) {
      $user_caps = implode(", ",$user_caps);
      echo "<tr><td class='name'>$name</td><td class='value'>$user_caps</td></tr>";
    }
  }
  echo "    </table>";
  echo "  </td>";
  echo "</tr>";
}

function add_survey_url()
{
  $pdf_uri = survey_pdf_uri();
  echo "<tr>";
  echo "  <td class='label'>Survey URL</td>";
  echo "  <td class='value'>$pdf_uri</td>";
  echo "</tr>";
}


function add_advanced()
{
  $log_level = LOGGER_[survey_log_level()];
  $post_ui = POST_UI_[survey_post_ui()];
  echo "<tr>";
  echo "  <td class='label'>Log Level</td>";
  echo "  <td class='value'>$log_level</td>";
  echo "</tr><tr>";
  echo "  <td class='label'>Post UI</td>";
  echo "  <td class='value'>$post_ui</td>";
  echo "</tr>";
}


function add_tlc_survey_usage() { ?>

<h2>Usage</h2>
<div class='usage'>

<div class='note'>
Simply add the shortcode <code>[tlc-ttsurvey]</code> to your pages or posts to embed
the Time &amp; Talent survey
</div>

<div class='qual'>
Only the first occurance of this shortcode on any given page will be rendered.  All others will be quietly ignored.
</div>

<div class='note'>
The following <b>optional</b> arguments are currently recognized (<i>yes, there's only one right now</i>):
</div>
<div class='qual'>
Any unspecified argument defaults to the value defined in the plugin settings
</div>

<div class='args'>
<div class='note'>name</div>
<div class='qual'>Must match one of the survey names.</div>
</div>

<div class='note'>Example</div>
<div class='example'><span>[tlc-ttsurvey name=2023]</span></div>

</div>

<h2>Theme Compatibility</h2>
<div class='info'>
  The survey does not render well when its width is too narrow.  If your theme 
  provides wide page templates, you may want to make sure the page that contains 
  the survey uses that template.  Similarly, you probably do not want to use 
  multi-column templates or templates with side bars for the survey page.
</div>


<?php }
