<?php
	//planetsPage.php
	//this page shows the user their list of planets and lets them control them
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
	
	//send to reports page if they haven't seen this day yet
	if($game->commanders[$email]['last'] < $game->day){
		header("Location: reportPage.php?g=$gameID");
	}
	
	$title = $game->commanders[$email]['title'];
	$name  = $game->commanders[$email]['name'];
	
	$data['email'] = "$title $name";
	$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
	$data['title'] = "$furyspace } $title $name's planets in $game->galaxy";
	$data['styles'] = array('space.css', "style.php?color={$game->commanders[$email]['color']}");
	View::header($data);
	View::messages();
	View::galaxy_header($game->galaxy, "$title $name", $game->day, $game->get_stats($email), $game->get_commit($email), $gameID);
	View::navigation($gameID, "map");
	print "<div id = \"tab\">\n";
	
	if(!isset($game->commanders[$email]['stats'])){
		$commit = $game->get_commit($email);
		if($commit > 0){
			if($commit == 2){
				print "<p>You are commited for this turn.  You can continue to look around, plan and tweak your orders, but the turn can now roll over at any time.</p>";
			} else {
				print "<p>When you're done with your turn, click [<a href = \"action.php?g=$gameID&a=commit&origin=planets\">commit</a>].";
			}
		}
	}
	
	if(!isset($game->commanders[$email]['stats'])){
		$money = $game->commanders[$email]['money'];
		print "<p>You have $money gold.</p>";
	}
	
	print "<p>Macro: <a href = \"action.php?gid=$gameID&a=queue_all&origin=planets\">queue up all ships</a> - fills every factory until you run out of money.</p>";
	
	$planets = $game->planets_owned_by($email);
	$number = count($planets);
	$s = ($number == 1) ? '' : 's';
	if(!isset($game->commanders[$email]['stats'])){
		print "<p>You own $number planet$s:</p>";
	}
	print "<p>Scroll help: | ";
	foreach ($planets as $planet){
		print "<a href = \"#{$planet['name']}\">{$planet['name']}</a> | ";
	}
	print "</p>";
	
	foreach ($planets as $planet){
	
		//
		// 	Planet Header
		//
		print "<a name = \"{$planet['name']}\"></a>";
		print "<div class = \"planet_header\">\n";
		$star = '';
		$hp = '';
		if($game->commanders[$email]['homeplanet'] == $planet['name']){
			$star 	= '*';
			$hp 	= ' (home planet)';
		}
		print "<div class = \"name\">$star{$planet['name']}$hp</div>\n";
		print "<div class = \"stats\">\n";
		
		$ordinal = Controller::cardinal_to_ordinal($planet['class']);
		$size = count($planet['buildings']);
		print "Class: {$ordinal} | Size: {$size} | Ships: {$planet['ships']} \n";
		print "[<a href = \"#top\">top</a>]\n";
		print "</div><!--stats-->\n";
		print "<div class = \"puller\"></div>\n";
		print "</div><!--planet_header-->\n";
		
		//
		//	Fleet Control
		//
		$nearby = array();
		$distperday = $game->get_day_range($email);
		$range = $game->get_total_range($email);
		foreach($game->planets as $otherplanet){
			if($planet['name'] == $otherplanet['name']){
				continue;
			}
			$dist = $game->distance($planet['name'], $otherplanet['name']);
			if($dist < $range){
				$nearby[] = array(
					'name' => $otherplanet['name'],
					'time' => ceil($dist / $distperday)
				);
			}
		}
		if($planet['ships'] > 0){
			print "<div class = \"dispatch\">\n";
			print "<ul>\n";
			print "<form action = \"action.php?g=$gameID\" method = \"post\">\n";
			print "<input type = \"hidden\" name=\"action\" value =\"dispatch_ships\" />";
			print "<input type = \"hidden\" name=\"source\" value =\"{$planet['name']}\" />";
			print "<li>Number: ";
			print "<select name = \"number\">\n";
			for($i = 1; $i <= $planet['ships']; $i++){
				print "<option value=\"$i\">$i</option>\n";
			}
			print "</select></li>\n";
			print "<li>Destination: ";
			print "<select name = \"destination\">\n";
			foreach($nearby as $other){
				$s = ($other['time'] == 1) ? '' : 's';
				print "<option value=\"{$other['name']}\">{$other['name']} - {$other['time']} day$s</option>\n";
			}
			print "</select></li>\n";
			print "<input type = \"hidden\" name = \"a\" value = \"dispatch\" />\n";
			print "<input type = \"hidden\" name = \"origin\" value = \"planets\" />\n";
			print "<input type =\"submit\" value = \"dispatch\" />\n";
			print "</form>";
			print "</ul>\n";
			print "</div><!--dispatch-->\n";
		}
		
		//
		//	Planet Architecture
		//
		print "<div class = \"architecture\">\n";
		print "<ul>\n";
		$i = 0;
		foreach($planet['buildings'] as $building){
			$type = $building['type'];
			if(preg_match('/^recycling_/', $type)){
				$recycle_button = "[<a href = \"action.php?g=$gameID&a=cancel_recycle&planet={$planet['name']}&slot=$i&origin=planets\">cancel recycle</a>]";			
			} else {
				$recycle_button = "[<a href = \"action.php?g=$gameID&a=recycle_structure&planet={$planet['name']}&slot=$i&origin=planets\">recycle</a>]";
			}
			
			if($type == 'empty'){
				print "<li>empty } Build: ";
				print "[<a href = \"action.php?g=$gameID&a=build_structure&origin=planets&planet={$planet['name']}&building=facility&slot=$i\">facility</a>]";
				print "[<a href = \"action.php?g=$gameID&a=build_structure&origin=planets&planet={$planet['name']}&building=mine&slot=$i\">mine</a>]";
				print "[<a href = \"action.php?g=$gameID&a=build_structure&origin=planets&planet={$planet['name']}&building=factory&slot=$i\">factory</a>]";
				print "</li>\n";
			} else if (preg_match('/^future/', $type)){
				print "<li>{$type} [<a href = \"action.php?g=$gameID&a=cancel_structure&origin=planets&planet={$planet['name']}&building={$type}&slot=$i\">cancel</a>]"; 
			} else if ($type == 'factory'){
				print "<li>{$type} [<a href = \"action.php?g=$gameID&a=build_ship&origin=planets&planet={$planet['name']}&slot=$i\">build ship</a>] $recycle_button"; 
			} else if (preg_match('/^pregnant/', $type)){
				print "<li>{$type} [<a href = \"action.php?g=$gameID&a=cancel_ship&origin=planets&planet={$planet['name']}&ship={$type}&slot=$i\">cancel</a>]"; 
			} else {
				print "<li>{$building['type']} $recycle_button</li>\n";
			}
			$i++;
		}
		print "</ul>\n";		
		print "</div><!--architecture-->\n";
	}
	
	print "</div><!--tab-->\n";
	View::footer($renderstart);
?>
