<?php
	//reportPage.php
	//this page shows the user the events that happened last turn.
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
	
	//Show a notification if they haven't seen this turn yet
	if($game->commanders[$email]['last'] < $game->day){
		$_SESSION['notification'] = "Welcome to day <b>{$game->day}</b>.";
	}
	$game->update_last($email);
	
	$title = $game->commanders[$email]['title'];
	$name  = $game->commanders[$email]['name'];
	
	$data['email'] = "$title $name";
	$data['title'] = "$game->galaxy } $title $name's Report";
//	$data['metarefresh'] = array('time' => 30, 'location' => "reportPage.php?g=$gameID");
	$data['styles'] = array('space.css', "style.php?color={$game->commanders[$email]['color']}");
	View::header($data);
	View::messages();
	View::galaxy_header($game->galaxy, "$title $name", $game->day, $game->get_stats($email), $game->get_commit($email), $gameID);
	View::navigation($gameID, "map");
	print "<div id = \"tab\" class = \"readable\">\n";
	
	$previous = $game->day - 1;
	if(isset($game->commanders[$email]['morning'])){
	
		//
		// print evening reports
		//
		print "<h2>Events last night (day $previous):</h2>\n";
		$number = count($game->commanders[$email]['evening']);
		if($number == 0) {
			print "<p>Actually, nothing happened last night.</p>";	
		} else {
			print "<ul>\n";
			foreach($game->commanders[$email]['evening'] as $report){
				extract($report);
				switch($type) {
					case 'build':
						$items = array();	//an array of strings inicating how many of each thing was built
						if($ships > 0) {
							$items[] = ($ships == 1) ? 'a ship' : "$ships ships";
						}
						if($facilities > 0) {
							$items[] = ($facilities == 1) ? 'a facility' : "$facilities facilities";
						}
						if($factories > 0) {
							$items[] = ($factories == 1) ? 'a factory' : "$factories factories";
						}
						if($mines > 0) {
							$items[] = ($mines == 1) ? 'a mine' : "$mines mines";
						}
						if(count($items) >= 2){
							$lastitem = array_pop($items);
						}
						if(count($items) >= 2){
							$itemstring = implode(', ', $items);
						} else {
							$itemstring = $items[0];
						}
						if(isset($lastitem)){
							$itemstring .= " and " . $lastitem;
						}
						$planet = View::planetlink($gameID, $planetname);
						print "<li>You built $itemstring on $planet.</li>\n";
						break;
					case 'recycle':		
						$items = array();	//an array of strings inicating how many of each thing was built
						if($facilities > 0) {
							$items[] = ($facilities == 1) ? 'a facility' : "$facilities facilities";
						}
						if($factories > 0) {
							$items[] = ($factories == 1) ? 'a factory' : "$factories factories";
						}
						if($mines > 0) {
							$items[] = ($mines == 1) ? 'a mine' : "$mines mines";
						}
						if(count($items) >= 2){
							$lastitem = array_pop($items);
						}
						if(count($items) >= 2){
							$itemstring = implode(', ', $items);
						} else {
							$itemstring = $items[0];
						}
						if(isset($lastitem)){
							$itemstring .= " and " . $lastitem;
						}
						print "<li>You reycled $itemstring on <a href = \"mapPage.php?g=$gameID&planet=$planetname\">$planetname</a> for $refund gold.</li>\n";
						break;	
					
					case 'levelup':
						$level = $game->get_tech_level($email);
						print "<li>Your technology level is now $level.</li>\n";
						break;
						
					case 'creation':
						print "<li>The {$game->galaxy} galaxy was created.</li>\n";
						break;	
					
				}
			}
			print "</ul>\n";
		}
	
		//
		// print morning reports
		//
		print "<h2>Events this morning (day {$game->day}):</h2>\n";
		$number = count($game->commanders[$email]['morning']);
		if($number == 0) {
			print "<p>Actually, nothing happened this morning.</p>";	
		} else {
			print "<ul>\n";
			foreach($game->commanders[$email]['morning'] as $report){
				extract($report);
				switch($type) {
					case 'update':
						$color = $game->commanders[$email]['color'];
						$homeplanetname = $game->commanders[$email]['homeplanet'];
						$homeplanet = View::planetlink($gameID, $homeplanetname);
						$capcolor = ucfirst($color);
						print "<li>You became <span class = \"$color\">$capcolor $title $name</span> of $homeplanet.</li>\n";
						break;
						
					case 'fleet_land':
						$fromplanetlink = View::planetlink($gameID, $fromplanet);
						$toplanetlink   = View::planetlink($gameID, $toplanet);
						$s = ($quantity == 1) ? '' : 's';
						print "<li>$quantity ship$s from $fromplanetlink landed on $toplanetlink.</li>\n";
						break;
					
					case 'claim':
						$s = ($remain == 1) ? '' : 's';
						$planet = View::planetlink($gameID, $planetname);
						print "<li>$remain ship$s claimed $planet.</li>\n";
						break;
						
					case 'conquer':
						$s1 = ($initialships == 1) ? '' : 's';
						$s2 = ($ownerships == 1) ? '' : 's';
						$remains = ($remain == 1) ? 'remains' : 'remain';
						$planet = View::planetlink($gameID, $planetname);
						$owner = View::commanderspan($game, $owneremail);
						print "<li>Your $initialships ship$s1 conquered $planet from $owner, who defended with $ownerships ship$s2 ($remain $remains)</li>\n";
						break;
						
					case 'fail':
						$s1 = ($yourships == 1) ? '' : 's';
						$s2 = ($ownerinitial == 1) ? '' : 's';
						$remains = ($remain == 1) ? 'remains' : 'remain';
						$planet = View::planetlink($gameID, $planetname);
						$owner = View::commanderspan($game, $owneremail);
						print "<li>Your $yourships ship$s1 failed to invade $owner's $planet, which was defended by $ownerinitial ship$s2 ($remain $remains)</li>\n";
						break;
						
					case 'lose':
						$s1 = ($yourinitial == 1) ? '' : 's';
						$s2 = ($invadeinitial == 1) ? '' : 's';
						$remains = ($remain == 1) ? 'remains' : 'remain';
						$planet = View::planetlink($gameID, $planetname);
						$invader = View::commanderspan($game, $invadeemail);
						print "<li>$invader invaded $planet with $invadeinitial ship$s2, defeating your $yourinitial ship$s1 ($remain $remains)</li>\n";
						break;
						
					case 'rebuff':
						$s1 = ($yourinitial == 1) ? '' : 's';
						$s2 = ($invadeinitial == 1) ? '' : 's';
						$remains = ($remain == 1) ? 'remains' : 'remain';
						$planet = View::planetlink($gameID, $planetname);
						$invader = View::commanderspan($game, $invadeemail);
						print "<li>Your $yourinitial ship$s1 rebuffed $invader's $invadeinitial ship$s2 on $planet. ($remain $remains)</li>\n";
						break;
						
					case 'wipeout':
						print "<li>With no home planet, you have been wiped out.</li>\n";
						break;
						
					case 'tko':
						$loser = View::commanderspan($game, $loseremail);
						print "<li>You have wiped out $loser.</li>\n";
						break;
						
					case 'dominate':
						$galaxy = $game->galaxy;
						print "<li>You have conquered $galaxy!</li>\n";
						break;						
				}
			}
			print "</ul>\n";
		}
	} else {
		print "<h2>(Old Style Reports) Events of Day $previous:</h2>\n";
		
		$number = count($game->commanders[$email]['reports']);
		
		if($number == 0){
			print "<p>Nothing worth noting happened this turn.</p>";
		} else {
			print "<ul>\n";
			foreach($game->commanders[$email]['reports'] as $report){
				print "<li>$report</li>\n";
			}
			print "</ul>\n";
		}
	}
	
//	print "<p><b>Note</b>: This page will refresh automatically every 30 seconds.</p>";
	
	print "</div><!--tab-->";
	View::footer($renderstart);
?>
