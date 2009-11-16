<?php
	//commandersPage.php
	//this page shows the user the other commanders in their galaxy
	session_start();
	require_once('global.php');
	Controller::assert_login();
	$email = $_SESSION['email'];
	
	$gameID = (isset($_GET['g'])) ? Controller::sanitize_int($_GET['g']) : '';
	if(!$gameID){
		Controller::error(701, "I'm sorry, I can't tell which game you're looking for.");
	} else {
		$game = Model::get_game($gameID);
		if($game == null){
			Controller::error(702, "I'm sorry, I can't find game <b>$gameID</b>.");
		} if (!in_array($email, array_keys($game->commanders))){
			Controller::error(708, "I'm sorry, it doesn't look like you are playing in game <b>$gameID</b>. " .
								   "[<a href = \"homePage.php\">home</a>]");
		}
	}
	
	if($game->status == 'setup'){
		header("Location: setupPage.php?g=$gameID");
	}

	// Check if they're committed and the turn has timed out
	if(in_array($email, $game->committed) && $game->day_age() > $game->day_timeout()){
		header("Location: action.php?a=timeout&gid=$gameID&origin=html");
	}
	
	//send to reports page if they haven't seen this day yet
	if($game->commanders[$email]['last'] < $game->day){
		header("Location: reportPage.php?g=$gameID");
	}
	
	$title = $game->commanders[$email]['title'];
	$name  = $game->commanders[$email]['name'];
	
	$refresh_rate = 20;
	
	$data['email'] = "$title $name";
	$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
	$data['title'] = "$furyspace } Commanders of $game->galaxy";
	$data['metarefresh'] = array('time' => $refresh_rate, 'location' => "commandersPage.php?g=$gameID");
	$data['styles'] = array('space.css', "style.php?color={$game->commanders[$email]['color']}");
	View::header($data);
	View::messages();
	View::galaxy_header($game->galaxy, "$title $name", $game->day, $game->get_stats($email), $game->get_commit($email), $gameID);
	View::navigation($gameID);
	print "<div id = \"tab\" class = \"readable\">\n";
	
	//print information about how long the turn has been running
	$seconds = $game->day_age();
	$timearray = array(
		'day' => 86400,
		'hour' => 3600,
		'minute' => 60,
		'second' => 1);
		
	foreach($timearray as $unitname => $secondsperunit){
		if($seconds > $secondsperunit){
			$units = floor($seconds / $secondsperunit);
			$time = "<b>$units</b> " . (($units > 1) ? Controller::pluralize($unitname) : $unitname);
			break;
		}
	}
	print "<p>This turn has been active for $time.</p>";
	
	$commanders = $game->commanders;
	$i = 0;
	foreach ($commanders as $commander){
		$i = (($i + 1) % 3);
		print "<div class = \"commander_box\">\n";
		print "<h2><span class = \"{$commander['color']}\">Commander {$commander['name']}</span></h2>\n";
		print "<ul>\n<li>Color: {$commander['color']}</li>\n";
		if(in_array($commander['email'], $game->conquered)){
			print "<li>Status: Conquered</li>\n";
		} else if($commander['email'] == $game->winner){
			print "<li>Status: Victorious</li>\n";
		} else if(in_array($commander['email'], $game->committed)){
			print "<li>Status: Committed</li>\n";
		} else {
			print "<li>Status: Still playing this turn</li>\n";
		}
		print "<li>Email: <a href = \"mailto:{$commander['email']}\">{$commander['email']}</a></li>\n";
		//if this is me, output additional information
		if($commander['email'] == $email){
			$tech = $commander['tech'];
			$techlevel = $game->get_tech_level($email);
			print "<li>Tech Units: $tech</li>";
			print "<li>Tech Level: $techlevel</li>";
		}
		print "</ul>\n";
		print "</div><!--commander_box-->\n";
		if($i == 0){
			print "<div class = \"puller\"></div>\n";
		}
		
		
	}
	
	print "<p><b>Note</b>: This page will refresh automatically every $refresh_rate seconds.</p>";
	print "</div><!--tab-->\n";
	View::footer($renderstart);
?>
