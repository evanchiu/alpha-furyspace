<?php
	//home.php
	//this is a visable page, this is where the user will be once logged in
	//it lists the games they're playing in
	session_start();
	require_once('global.php');
	Controller::assert_login();
	
	$email = $_SESSION['email'];
	
	$data['email'] = $email;
	$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
	$data['title'] = "$furyspace } $email's games";
	$data['description'] = "A small turn-based space-themed dominance game";
	$data['metakeywords'] = array('furyspace', 'game');
	$data['styles'] = array('space.css');
	View::header($data);
	
	$gamelist = Model::get_games($email);
?>
	<h1>Welcome, <?php print $email; ?></h1>
	<div id = "tab" class = "readable">
<?	
	View::messages();
	$count = count($gamelist);
	if($count == 0){
		print "<p>I'm sorry, it doesn't look like you've been assigned any commander positions yet.</p>\n";
	} else {
			
		$your_turn  = array();
		$active 	= array();
		$finished 	= array();
		$conquered  = array();
		
		foreach ($gamelist as $game){
			$commander 	= $game->commanders[$email];
			$title  	= $commander['title'];
			$name 		= $commander['name'];
			$day 		= $game->day;
			$personal_status;
			if(in_array($email, $game->conquered)){
				if($game->status == 'finished'){
					$personal_status = 'conquered';
					$finished[] = $game;
				} else {
					$conquered[] = $game;
				}
			} else if ($email == $game->winner){
				$personal_status = 'the winner';
				$finished[] = $game;
			} else if(in_array($email, $game->committed)){
				$personal_status = 'committed';
				$active[] = $game;
			} else {
				$personal_status = 'playing';
				$your_turn[] = $game;
			}
		}
		
		if(count($your_turn) > 0){
			print "<h2>Your Turn:</h2>\n";
			print "<ul>\n";		
			foreach($your_turn as $game){
				$commander 	= $game->commanders[$email];
				$title  	= $commander['title'];
				$name 		= $commander['name'];
				$day 		= $game->day;
				print "<li><b><span class = \"{$commander['color']}\">{$title} {$name}</span></b> " .
					" of the <b><a href = \"mapPage.php?g={$game->id}\">" .
					"{$game->galaxy}</a></b> Galaxy (Day<b> $day</b>) (status: <b>{$game->status}</b>)" .
					" </li>\n";
			}
			print "</ul>\n";
		}
		
		if(count($active) > 0){
			print "<h2>Waiting for other players:</h2>\n";
			print "<ul>\n";		
			foreach($active as $game){
				$commander 	= $game->commanders[$email];
				$title  	= $commander['title'];
				$name 		= $commander['name'];
				$day 		= $game->day;
				print "<li><b><span class = \"{$commander['color']}\">{$title} {$name}</span></b> " .
					" of the <b><a href = \"mapPage.php?g={$game->id}\">" .
					"{$game->galaxy}</a></b> Galaxy (Day<b> $day</b>) (status: <b>{$game->status}</b>)" .
					" </li>\n";
			}
			print "</ul>\n";
		}
		
		
		if(count($conquered) > 0){
			print "<h2>War still rages:</h2>\n";
			print "<ul>\n";		
			foreach($conquered as $game){
				$commander 	= $game->commanders[$email];
				$title  	= $commander['title'];
				$name 		= $commander['name'];
				$day 		= $game->day;
				print "<li><b><span class = \"{$commander['color']}\">{$title} {$name}</span></b> " .
					" of the <b><a href = \"mapPage.php?g={$game->id}\">" .
					"{$game->galaxy}</a></b> Galaxy (Day<b> $day</b>)" .
					" </li>\n";
			}
			print "</ul>\n";
		}
		
		if(count($finished) > 0){
			print "<h2>Finished:</h2>\n";
			print "<ul>\n";	
			foreach($finished as $game){
				if($game->winner == $email){
					$personal_status = 'the winner';
				} else {
					$personal_status = 'conquered';
				}	
				$commander 	= $game->commanders[$email];
				$title  	= $commander['title'];
				$name 		= $commander['name'];
				$day 		= $game->day;
				print "<li><b><span class = \"{$commander['color']}\">{$title} {$name}</span></b> " .
					" of the <b><a href = \"mapPage.php?g={$game->id}\">" .
					"{$game->galaxy}</a></b> Galaxy (Day<b> $day</b>) " .
					" (you are <b>$personal_status</b>)</li>\n";
			}
			print "</ul>\n";
		}		
	}
	
	//for now only Evan can create new galaxies
	if(in_array($email, array('evan@evanchiu.com', 'evchiu@gmail.com'))){
?>
	<p>Create a <a href = "creategamePage.php">New Galaxy</a> or do <a href = "adminPage.php">administrative</a> tasks?</p>
<?php
	}
	
	print "</div><!--tab-->\n";
	View::footer($renderstart);
?>
