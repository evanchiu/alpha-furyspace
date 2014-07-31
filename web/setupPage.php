<?php
	//setupPage.php
	//if the game is still in setup phase, player should go here
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
	
	if($game->status != 'setup'){
		header("Location: mapPage.php?g=$gameID");
	}
	
	$commanderName = $game->getCommander($email);
	
	$data['email'] = "Commander $commanderName";
	$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
	$data['title'] = "$furyspace } setting up $game->galaxy";
	$data['styles'] = array('space.css');
	View::header($data);
	
	$color = $game->commanders[$email]['color'];
	//don't pick any colors that are taken
	$colarray = Configuration::color_names();
	$taken = $game->get_colors();
	$leftover = array_diff($colarray, $taken);	
?>
	<h1>Welcome to <?php print $game->galaxy; ?>, Commander <?php print $commanderName; ?></h1>
	<?php View::messages(); ?>
	<form action="action.php" method="post">
	<table>
		<tr>
			<td>Commander Name</td>
			<td><input type="text" name="commander" id="commander" value="<?php print $commanderName; ?>" /></td>
		</tr>
		<tr>
			<td>Commander Color</td>
			<td>
				<select name="color">
				<?php
					//print your previous color first, then the other choices
					if($color != 'black'){
						print "<option value=\"$color\">$color</option>";
					}
					foreach($leftover as $color){
						print "<option value=\"$color\">$color</option>";
					}				
				?>
				</select>
			</td>
		</tr>
	</table>
	<input type="hidden" name="gid"    value="<?php print $gameID; ?>" />
	<input type="hidden" name="a"      value="update" />
	<input type="hidden" name="origin" value="html" />
	<input type="submit" value="save" />
	</form>
	
	<h2>Other Commanders:</h2>
	<ul>
<?php 
	foreach($game->commanders as $commander){
		if($commander['color'] != 'black'){
			$ospan = "<span class = \"{$commander['color']}\">";
			$cspan = "</span>";
		} else {
			$ospan = "";
			$cspan = "";
		}
		print "<li class = \"readable\">$ospan Commander {$commander['name']}$cspan (<a href = \"mailto:{$commander['email']}\">{$commander['email']}</a>)</li>\n";
	}
?>

	</ul>
<?php
	View::footer($renderstart);
?>
