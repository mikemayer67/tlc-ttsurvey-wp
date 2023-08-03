<?php
namespace TLC\Live;

require_once tlc_plugin_path('include/logger.php');
require_once tlc_plugin_path('settings.php');
require_once tlc_plugin_path('youtube.php');

$settings = Settings::instance();
$api_key = $settings->get(API_KEY);
$channel = $settings->get(CHANNEL_ID);
$playlist = $settings->get(PLAYLIST_ID);

$ok = tlc_plugin_url('images/icons8-valid.png');
$bad = tlc_plugin_url('images/icons8-invalid.png');
$unknown = tlc_plugin_url('images/icons8-unknown.png');

$timezone = $settings->get(TIMEZONE);
date_default_timezone_set($timezone);

// YouTube Keys and IDs

$api_key_is_good = false;
if( empty($api_key) )
{
  $api_icon = $bad;
  $api_reason = 'required';
} else {
  $api_status = new ValidateAPIKey($api_key);
  $api_key_is_good = $api_status->is_valid();
  if( $api_key_is_good ) {
    $api_icon = $ok;
    $api_reason = '';
  } elseif( $api_status->is_invalid() ) {
    $api_icon = $bad;
    $api_reason = $api_status->reason();
  } else {
    $api_icon = $unknown;
    $api_reason = $api_status->reason();
  }
}

$channel_is_good = false;
if( empty($channel) )
{
  $channel_icon = $bad;
  $channel_reason = 'required';
} else {
  $channel_status = new ValidateChannelID($channel,$api_key);
  $channel_is_good = $channel_status->is_valid();
  if( $channel_is_good ) {
    $channel_icon = $ok;
    $channel_reason = $channel_status->title();
  } elseif( $channel_status->is_invalid() ) {
    $channel_icon = $bad;
    $channel_reason = $channel_status->reason();
  } else {
    $channel_icon = $unknown;
    $channel_reason = $channel_status->reason();
  }
}

$playlist_is_good = false;
if( empty($playlist) )
{
  $playlist_icon = $unknown;
  $playlist_reason = '';
} else {
  $playlist_status = new ValidatePlaylistID($playlist,$api_key);
  $playlist_is_good = $playlist_status->is_valid();
  if( $playlist_is_good ) {
    $playlist_icon = $ok;
    $playlist_reason = $playlist_status->title();
  } elseif( $playlist_status->is_invalid() ) {
    $playlist_icon = $bad;
    $playlist_reason = $playlist_status->reason();
  } else {
    $playlist_icon = $unknown;
    $playlist_reason = $playlist_status->reason();
  }
}

$query_freq = floor($settings->get(QUERY_FREQ)/60);

// Embed Settings

$autoplay = ( $settings->get(AUTOPLAY)
  ? "The stream will <b>automatcally</b> start when it goes live."
  : "The viewer will need to <b>hit the play button</b> in the player to start the livestream."
);

$controls = ( $settings->get(CONTROLS)
  ? "Playback contols <b>will</b> be displayed in the player."
  : "Playback contols will <b>not</b> be displayed in the player."
);

$enablekb = ($settings->get(ENABLE_KB)
  ? "The viewer <b>will</b> be able to control player with the keyboard."
  : "The viewer will <b>not</b> be able to control player with the keyboard."
);

$fullscreen = ($settings->get(FULL_SCREEN)
  ? "The player <b>can</b> be made to dispaly full screen."
  : "The player <b>cannot</b> be made to dispaly full screen."
);

$modestbranding = ($settings->get(MODEST_BRANDING)
  ? "The YouTube logo will <b>not</b> be shown unless the livestream is paused."
  : "The YouTube logo <b>may</b> be shown while the livestream is playing."
);

// Transition

$transition = $settings->get(TRANSITION);
$transition_m = $transition % 60;
$transition   = ($transition - $transition_m) / 60;
$transition_h = $transition % 24;
$transition_d = ($transition - $transition_h) / 24;

$transition = "";
switch($transition_d) {
case 0:  $transition = "";                   break;
case 1:  $transition = "1 day";              break;
default: $transition = "$transition_d days"; break;
}
switch($transition_h) {
case 0:                                        break;
case 1:  $transition .= " 1 hour";              break;
default: $transition .= " $transition_h hours"; break;
}
switch($transition_m) {
case 0:                                          break;
case 1:  $transition .= " 1 minute";              break;
default: $transition .= " $transition_m minutes"; break;
}

if( empty($transition) ) {
  $transition = "at";
} else {
  $transition = "$transition before";
}

// ABANDON

$abandon = $settings->get(ABANDON);
if( $abandon == 0 ) {
  $abandon = 3600;
}
$abandon_m = $abandon % 60;
$abandon   = ($abandon - $abandon_m) / 60;
$abandon_h = $abandon % 24;
$abandon_d = ($abandon - $abandon_h) / 24;

$abandon = "";
switch($abandon_d) {
case 0:  $abandon = "";                   break;
case 1:  $abandon = "1 day";              break;
default: $abandon = "$abandon_d days"; break;
}
switch($abandon_h) {
case 0:                                        break;
case 1:  $abandon .= " 1 hour";              break;
default: $abandon .= " $abandon_h hours"; break;
}
switch($abandon_m) {
case 0:                                          break;
case 1:  $abandon .= " 1 minute";              break;
default: $abandon .= " $abandon_m minutes"; break;
}

// Playlists

$playlist_ids = null;
if($api_key_is_good and $channel_is_good) {
  $query = new PlaylistIDs($channel,$api_key);
  $playlist_ids = $query->playlists();
}

// Upcoming Livestreams

function by_scheduled_start($a,$b) {
  return $a['scheduledStart'] <=> $b['scheduledStart'];
}

$upcoming_livestreams = null;
if($api_key_is_good and $channel_is_good) {
  $query = new UpcomingLivestreams($channel,$api_key);
  $upcoming_livestreams = $query->livestreams();
  uasort($upcoming_livestreams,ns('by_scheduled_start'));
}

// Latest Recorded Video

$latest_recorded_livestream = null;
if($api_key_is_good and $playlist_is_good) {
  $query = new RecordedLivestreams($playlist,$api_key);
  $latest_recorded_livestream = $query->most_recent();
}

?>


<h2>Connection Settings</h2>
<table class='tlc-overview'>
  <tr>
    <td class=label>YouTube API Key</td>
    <td class=value><?=$api_key?></td>
    <td class=status><img src='<?=$api_icon?>'></img></td>
    <td class=status><span><?=$api_reason?></span></td>
  </tr>
  <tr>
    <td class=label>Channel ID</td>
    <td class=value><?=$channel?></td>
    <td class=status><img src='<?=$channel_icon?>'></img></td>
    <td class=status><span><?=$channel_reason?></span></td>
  </tr>
  <tr>
    <td class=label>Playlist ID</td>
    <td class=value><?=$playlist?></td>
    <td class=status><img src='<?=$playlist_icon?>'></img></td>
    <td class=status><span><?=$playlist_reason?></span></td>
  </tr>

  <tr>
    <td class=info colspan=4>
      YouTube API queries will occur no more frequently than once every 
      <b><?=($query_freq>1 ? "$query_freq minutes" : "minute")?></b>.
    </td>
  </tr>
</table>


<h2>Available Playlists</h2>
<table class='tlc-overview'>
<?php if(is_null($playlist_ids)) { ?>
  <tr>
    <td class=note colspan=2>
      Only available if both the current API Key and Channel ID are valid.
   </td>
  </tr>
<?php } elseif(count($playlist_ids)<1) { ?>
  <tr>
    <td class=note colspan=2>
    No public playlists associated with channel <?=$channel?>
    </td>
  </tr>
<?php } else { ?>
  <tr class=heading>
    <td colspan=2>
    The following playlists are associated with channel <?=$channel?>
    </td>
  </tr>
<?php foreach( $playlist_ids as $id=>$title) {?>
  <tr>
    <td class=id><?=$id?></td>
    <td class=status><?=$title?></td>
  </tr>
<?php }} ?>
</table>

<h2>Livestream Settings</h2>
<table class='tlc-overview'>
  <tr>
    <td class=label>autoplay</td>
    <td class=value><?=$autoplay?></td>
  </tr>
  <tr>
    <td class=label>controls</td>
    <td class=value><?=$controls?></td>
  </tr>
  <tr>
    <td class=label>keyboard</td>
    <td class=value><?=$enablekb?></td>
  </tr>
  <tr>
    <td class=label>full screen</td>
    <td class=value><?=$fullscreen?></td>
  </tr>
  <tr>
    <td class=label>modest branding</td>
    <td class=value><?=$modestbranding?></td>
  </tr>
</table>


<h2>Upcoming Livestreams</h2>
<table class='tlc-overview'>
<?php if(is_null($upcoming_livestreams)) { ?>
  <tr>
    <td class=note colspan=2>
      Only available if both the current API Key and Channel ID are valid.
   </td>
  </tr>
<?php } elseif(count($upcoming_livestreams)<1) { ?>
  <tr>
    <td class=note colspan=2>
    There are currently no scheduled upcoming livestreams.
    </td>
  </tr>
<?php } else { 
  foreach($upcoming_livestreams as $vid=>$details) {
    $title = $details['title'];
    $thumb = $details['thumbnail'];
    $start = date('r',$details['scheduledStart']);
?>
  <tr>
    <td class=id><?=$start?></td>
    <td class=value><?=$title?></td>
    <td class=thumb><a href='https://youtube.com/watch?v=<?=$vid?>' target=_blank>
      <img src='<?=$thumb?>'></img></a></td>
  </tr>
<?php }} ?>
</table>

<h2>Most Recently Recorded Video</h2>
<table class='tlc-overview'>
<?php if($latest_recorded_livestream) {
  $title = $latest_recorded_livestream['title'];
  $thumb = $latest_recorded_livestream['thumbnail'];
  $start = date('r',$latest_recorded_livestream['actualStart']);
?>
  <tr>
    <td class=id><?=$start?></td>
    <td class=value><?=$title?></td>
    <td class=thumb><a href='https://youtube.com/watch?v=<?=$vid?>' target=_blank>
      <img src='<?=$thumb?>'></img></a></td>
  </tr></table>
<?php } elseif($api_key_is_good and $playlist_is_good) { ?>
  <tr>
    <td class=note colspan=2>
    There are currently no recorded livestreams.
    </td>
  </tr>
<?php } else { ?>
  <tr>
    <td class=note colspan=2>
      Only available if both the current API Key and Playlist ID are valid.
   </td>
  </tr>

<?php } ?>
</table>

<h2>Summary</h2>

<table class='tlc-overview'>
<?php
if( $api_key_is_good ) {
  if( $channel_is_good ) {
    if( $playlist_is_good ) { ?>
  <tr>
    <td class=info colspan=2>
      The transition from recorded to upcoming livestream will occur <b><?=$transition?></b>
      the scheduled start time.
    </td>
  </tr>
<?php     } else { // valid api_key, valid_channel, invalid playlist ?>
  <tr><td class=warning>
    Only live and upcoming livestreams will be shown.
  </td></tr>
  <tr><td class=info>
    Add a valid playlist ID to show recorded livestreams between live broadcasts.
  </td></tr>
<?php     } // end-playlist?>
  <tr>
    <td class=info colspan=2>
      Upcoming livestreams will be considered "dead" <b><?=$abandon?></b>
      after the scheduled start time.
    </td>
  </tr>
<?php   } else { // valid api_key, invalid channel?>
  <tr>
    <td class=warning>
      Without a validated Channel ID, the shortcode will not be able to function.
    </td>
  </tr>
<?php   } // end-channel ?>
<?php } else { // invalid api_key?>
  <tr>
    <td class=warning>
      Without a validated API Key, the shortcode will not be able to function.
    </td>
  </tr>
<?php } // end-api_key?>


</table>

  
