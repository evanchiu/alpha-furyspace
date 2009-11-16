<?php
	//fleetPage.php
	//this page shows the user what fleets they currently have in space now
	session_start();
	require_once('global.php');
	Controller::assert_login();
	
	$email = $_SESSION['email'];
	
	$gameID = (isset($_GET['g'])) ? $_GET['g'] : '';
	if($gameID == ''){
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
	$data['title'] = "$furyspace } $title $name's Fleets";
	$data['styles'] = array('space.css', "style.php?color={$game->commanders[$email]['color']}");
	View::header($data);
	View::messages();
	View::galaxy_header($game->galaxy, "$title $name", $game->day, $game->get_stats($email), $game->get_commit($email), $gameID);
	View::navigation($gameID, "map");
	print "<div id = \"tab\" class = \"readable\">\n";
	
	$fleets = $game->commanders[$email]['fleets'];
	$count = count($fleets);
	if($count > 0){
		$s = ($count == 1) ? '' : 's';
		print "<h3>Your $count fleet$s</h3>";
	
		print "<ul>\n";
		for($i = 0; $i < count($fleets); $i++){
			$fleet = $fleets[$i];
			if($fleet['etd'] == $game->day){
				$cancelbutton = "[<a href = \"action.php?g=$gameID&a=recall&fleet=$i&origin=fleet\">recall</a>]";
			} else {
				$cancelbutton = '';
			}
			$s = ($fleet['size'] == 1) ? '' : 's';
			$days_out = $fleet['eta'] - $game->day;
			$when = ($days_out == 1) ? '<b>tomorrow</b>' : "in <b>$days_out</b> days";
			$sourceplanet = View::planetlink($gameID, $fleet['source']);
			$destplanet = View::planetlink($gameID, $fleet['destination']);
			if($game->is_color_visible($email, $fleet['destination'])){
                $destowner  = View::commanderspan($game, $game->planets[$fleet['destination']]['owner']);
                $destowners = "{$destowner}'s";
			} else {
                $destowners = '';
            }
			print "<li><b>{$fleet['size']}</b> ship$s from $sourceplanet headed to $destowners $destplanet arriving on day <b>{$fleet['eta']}</b> ($when) $cancelbutton</li>\n";
		}
		print "</ul>\n";
	} else {
		print "<p>I'm sorry, it doesn't look like you have any ships in flight right now.</p>";
	}
	
	print "</div><!--tab-->";
	View::footer($renderstart);
?>
