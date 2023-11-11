<?php
namespace TLC\TTSurvey;
if( ! defined('WPINC') ) { die; }

require_once plugin_path('include/const.php');

$keys = $message_data['keys'];
$nkeys = count($keys);

$scheme = parse_url($_SERVER['HTTP_REFERER'],PHP_URL_SCHEME);
$host = parse_url($_SERVER['HTTP_REFERER'],PHP_URL_HOST);
$path = parse_url($_SERVER['HTTP_REFERER'],PHP_URL_PATH);
$survey_url = "$scheme://$host/$path?tlc=1&tlcpage=pwreset";

$timeout = intval(round(LOGIN_RECOVERY_TIMEOUT / 60));

echo $custom_content;

echo "<div style='margin-left:1em;'>";
if($nkeys == 1) {
  echo "<p>Here is the login information you requested:</p>";
} else {
  echo "<p>There are $nkeys participants using this email address:</p>";
}
echo "</div>";

echo "<div style='margin-top:1em; margin-left:2em;'>";
foreach($keys as $key=>$info)
{
  echo "<div style='margin:15px 0;'>";
  echo "<div><b>" . $info['username'] . "</b></div>";

  echo "<div style='margin-left:8px;'>Userid: <b>";
  echo $info['userid'];
  echo "</b></div>";

  echo "<div style='margin-left:8px;'>";
  echo "Password Reset: $survey_url&token=$key";
  echo "</div>";

  echo "</div>";
}
echo "</div>";

echo "<div style='margin-top:20px;'>";
echo "<p style='font-style:italic;'>The password reset links must be used";
echo " within $timeout minutes and must be used on the same device and";
echo " browser used to request this email.</div>";

echo "<br>";
require plugin_path("include/sendmail/contacts.php");
