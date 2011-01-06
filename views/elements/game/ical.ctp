<?php
$created = strtotime ($game['Game']['created']);
$created = date('Ymd', $created) . 'T' . date('His', $created) . 'Z';
$modified = strtotime ($game['Game']['updated']);
$modified = date('Ymd', $modified) . 'T' . date('His', $modified) . 'Z';

// Get domain URL for signing games
$domain = Configure::read('urls.domain');

if (!is_array($team_id)) {
	$team_id = array($team_id);
}

if (in_array ($game['HomeTeam']['id'], $team_id)) {
	$my_team = $game['HomeTeam'];
	$my_home_away = 'home';
	$opponent = $game['AwayTeam'];
	$opp_home_away = 'away';
} else {
	$my_team = $game['AwayTeam'];
	$my_home_away = 'away';
	$opponent = $game['HomeTeam'];
	$opp_home_away = 'home';
}

$field = "{$game['GameSlot']['Field']['long_name']} ({$game['GameSlot']['Field']['code']})";
$field_address = "{$game['GameSlot']['Field']['name']}, {$game['GameSlot']['Field']['location_street']}, {$game['GameSlot']['Field']['location_city']}, {$game['GameSlot']['Field']['location_province']}";

// encode game start and end times
$game_date = "TZID=$timezone:" . strftime('%Y%m%d', strtotime($game['GameSlot']['game_date'])); // from date type
$game_start = $game_date . 'T'
		. implode('', explode(':', $game['GameSlot']['game_start'])); // from 'hh:mm:ss' string
$game_end = $game_date . 'T'
		. implode('', explode(':', $game['GameSlot']['display_game_end']));  // from 'hh:mm:ss' string
$game_stamp = strftime('%a %b %d %Y', strtotime ($game['GameSlot']['game_date'])) .
		" {$game['GameSlot']['game_start']} to {$game['GameSlot']['display_game_end']}";

// date stamp this file
$now = gmstrftime('%Y%m%dT%H%M%SZ'); // MUST be in UTC

// generate field url
$field_url = Router::url(array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']), true);

// output game
?>
BEGIN:VEVENT
UID:<?php echo "$uid_prefix$game_id@$domain"; ?>

DTSTAMP:<?php echo $now; ?>

CREATED:<?php echo $modified; ?>

LAST-MODIFIED:<?php echo $modified; ?>

DTSTART;<?php echo $game_start; ?>

DTEND;<?php echo $game_end; ?>

LOCATION;ALTREP=<?php echo "\"$field_url\":$field_address"; ?>

X-LOCATION-URL:<?php echo $field_url; ?>

SUMMARY:<?php echo "{$my_team['name']} ($my_home_away)"; ?> vs. <?php echo "{$opponent['name']} ($opp_home_away)"; ?>

DESCRIPTION:Game <?php echo $game_id; ?>: <?php echo "{$my_team['name']} ($my_home_away)"; ?> vs. <?php echo "{$opponent['name']} ($opp_home_away)"; ?>
 at <?php echo $field; ?> on <?php echo $game_stamp; ?> (they wear <?php echo $opponent['shirt_colour']; ?>)
X-OPPONENT-COLOUR:<?php echo $opponent['shirt_colour']; ?>

STATUS:CONFIRMED
TRANSP:OPAQUE
END:VEVENT
