<?php
//mapPage.php
//this page shows the user the map of the requested galaxy
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

//send them to setup page if the game isn't set up yet
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

//check if they're looking for a particular planet
$planetname = Request::valid_planet($gameID, false);

$title = $game->commanders[$email]['title'];
$name  = $game->commanders[$email]['name'];

$HEIGHT = 600;	//height to display the map

$data['email'] = "$title $name";
$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
if($planetname){
	$data['title'] = "$furyspace } $game->galaxy Map } $planetname";
} else {
	$data['title'] = "$furyspace } $game->galaxy Map with list";
}
//	$data['metarefresh'] = array('time' => 30, 'location' => "mapPage.php?g=$gameID");
$data['styles'] = array('space.css', "style.php?color={$game->commanders[$email]['color']}");
View::header($data);
View::messages();
View::galaxy_header($game->galaxy, "$title $name", $game->day, $game->get_stats($email), $game->get_commit($email), $gameID);
View::navigation($gameID, "map");
?>
<div id = "tab">
	<div id = "map_and_caption">
		<img 
		id = "map"
		src = "mapImage.php?g=<?php print $gameID; ?>&t=127&s=<?php print $HEIGHT; ?>&cachekill=<?php print time(); ?><?php if($planetname){print "&planet=$planetname";} ?>" 
		alt = "<?php print $game->galaxy; ?> galaxy" 
		title = "<?php print $game->galaxy; ?> galaxy"
		usemap = "#planets" 
		/>

		<p>View a <a href = "mapImage.php?g=<?php print $gameID; ?>&s=1000&t=0&cachekill=<?php print time(); ?>">larger</a> map.  Your homeplanet has an asterisk (*) by it.</p>
	</div><!--map_and_caption-->
	<div id = "planetcol">
		<?php if($planetname){
			
			$planet = $game->planets[$planetname];
			
			//
			// 	Planet Header
			//
			print "<a name = \"{$planet['name']}\"></a>";
			print "<div class = \"planet_header\">\n";
			$star = '';
			$hp = '';
			if($game->commanders[$email]['homeplanet'] == $planet['name']){
				$star 	= '*';
			}
			print "<div class = \"name\">$star{$planet['name']}$hp</div>\n";
			print "<div class = \"stats\">\n";

			$ordinal = Controller::cardinal_to_ordinal($planet['class']);
			$size = count($planet['buildings']);
			print "Class: {$ordinal}";
			if($planet['owner'] == $email){
				print "<br />Size: {$size} <br />";
				print "<br />Ships: {$planet['ships']}";
			}
			//show owner if you can see the color, and you're not the owner
		   	if ($game->is_color_visible($email, $planet['name']) and $planet['owner'] != $email){
		   		$owner = View::commanderspan($game, $planet['owner']);
		   		print "<br />Owner: $owner";
		   	}
			print "<br />[<a href = \"mapPage.php?g=$gameID\">planet list</a>]\n";
			print "</div><!--stats-->\n";
			print "</div><!--planet_header-->\n";

			//
			//	Fleet Control
			//
			if($planet['owner'] == $email) {
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
					print "<h3>Dispatch</h3>";
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
					print "<input type = \"hidden\" name = \"origin\" value = \"html\" />\n";
					print "<input type =\"submit\" value = \"dispatch\" />\n";
					print "</form>";
					print "</ul>\n";
					print "</div><!--dispatch-->\n";
				}
			}
			
			//
			//	Planet Architecture
			//
			if($game->is_architecture_visible($email, $planetname)){
				print "<div class = \"architecture\">\n";
				print "<h3>Architecture</h3>";
				print "<ul>\n";
				$i = 0;
				foreach($planet['buildings'] as $building){
					$type = $building['type'];
					if (preg_match('/^recycling_/', $type)){
						$recycle_button = "<a href = \"action.php?g=$gameID&a=cancel_recycle&planet={$planet['name']}&slot=$i&origin=map\"><img src = \"x/images/cancel.png\" alt=\"cancel recycle\" title=\"cancel recycle\" class=\"control\"></a>";			
					} else {
						$recycle_button = "<a href = \"action.php?g=$gameID&a=recycle_structure&planet={$planet['name']}&slot=$i&origin=map\"><img src = \"x/images/recycle.png\" alt=\"recycle\" title=\"recycle\" class=\"control\"></a>";
					}

					if ($type == 'empty') {
						print "<li><img src=\"x/images/empty.png\" alt=\"empty\" title=\"empty\" class=\"structure\">";
				        $buildings = array("facility", "factory", "mine");
				        foreach ($buildings as $building){
						  print "<a href = \"action.php?g=$gameID&a=build_structure&origin=map&planet={$planet['name']}&building=$building&slot=$i\"><img src = \"x/images/$building.png\" alt=\"build $building\" title=\"build $building\" class=\"control\"></a>";
						}
						print "</li>\n";	
					} else if (preg_match('/^future/', $type)) {
						print "<li><img src=\"x/images/{$type}.png\" alt=\"{$type}\" title=\"{$type}\" class=\"structure\" /> <a href   = \"action.php?g=$gameID&a=cancel_structure&origin=map&planet={$planet['name']}&building={$type}&slot=$i\"><img src = \"x/images/cancel.png\" alt=\"cancel build\" title=\"cancel build\" class=\"control\"></a>"; 
					} else if ($type == 'factory') {
						print "<li><img src=\"x/images/{$type}.png\" alt=\"{$type}\" title=\"{$type}\" class=\"structure\"/> <a href  = \"action.php?g=$gameID&a=build_ship&origin=map&planet={$planet['name']}&slot=$i\"><img src = \"x/images/build_ship.png\" alt=\"build ship\" title=\"build_ship\" class=\"control\"></a> $recycle_button"; 
					} else if (preg_match('/^pregnant/', $type)) {
						print "<li><img src=\"x/images/{$type}.png\" alt=\"{$type}\" title=\"{$type}\" class=\"structure\" /> <a href = \"action.php?g=$gameID&a=cancel_ship&origin=map&planet={$planet['name']}&ship={$type}&slot=$i\"><img src = \"x/images/cancel.png\" alt=\"cancel ship\" title=\"cancel ship\" class=\"control\"></a>"; 
					} else {
						print "<li><img src=\"x/images/{$type}.png\" alt=\"{$type}\" title=\"{$type}\"  class=\"structure\"> $recycle_button</li>\n";
					}
					$i++;
				}
				print "</ul>\n";
				print "</div><!--architecture-->\n";
			}
			
			//
			// Architecture Control
			//
			if($game->is_architecture_controllable($email, $planetname)){
				print "<div class = \"arch_control\">\n";
				print "<h3>Architecture Control</h3>\n";
				print "<ul>\n";
				print "<li><a href = \"action.php?a=queue_planet&origin=map&gid=$gameID&planet=$planetname\">Queue all ships on $planetname</a></li>\n";
				print "</ul>\n";
			}
		} else { //show list
?>
		<table>
			<tr><th>Name</th><th>Class</th><th>Ships</th></tr>
			<?	
		foreach($game->commanders[$email]['visible'] as $planetname){
			$planet = $game->planets[$planetname];
			print "\t<tr>\n";
			$star = ($planetname == $game->commanders[$email]['homeplanet']) ? '*' : '';
			if($planet['owner'] == $email){
				print "\t\t<td>$star<a href = \"mapPage.php?g=$gameID&planet={$planet['name']}\">{$planet['name']}</a></td>\n";
				} else if($game->is_color_visible($email, $planet['name'])){
					print "\t\t<td><span class = \"{$game->commanders[$planet['owner']]['color']}\">{$planet['name']}</span></td>\n";
				} else {
					print "\t\t<td>{$planet['name']}</td>\n";	
				} 
				$size = count($planet['buildings']);
				$ordinal = Controller::cardinal_to_ordinal($planet['class']);
				print "\t\t<td>{$ordinal}</td>\n";
				if($planet['owner'] == $email){
					print "\t\t<td>{$planet['ships']}</td>\n";
				} else {
					print "\t\t<td></td>\n";		
				}

				print "\t</tr>\n";
			}
			print "</table>\n";
			?>
		</div><!--planetcol-->
		<?php } ?>
		<!--<p><b>Note:</b> This page refreshes every 30 seconds.</p>-->
		<div class = "puller"></div>
	</div><!--tab-->

<map name = "planets">
<?php
	$zaphodToPixel = $HEIGHT / $game->height;
	$radius = 8;
	foreach($game->commanders[$email]['visible'] as $planetname){
		$planet = $game->planets[$planetname];
		$cx = $planet['x'] * $zaphodToPixel;
		$cy = $planet['y'] * $zaphodToPixel;
		$link = "mapPage.php?g=$gameID&planet=$planetname";
		$ordinal = Controller::cardinal_to_ordinal($planet['class']);
		$alt = "$planetname - $ordinal class";
		if($planet['owner'] == $email){
			$size = count($planet['buildings']);
			$alt .= " - size {$size}";
			$s = ($planet['ships'] == 1) ? '' : 's';
			$alt .= " - {$planet['ships']} ship$s";
		}
		print "<area shape=\"circle\" coords=\"$cx,$cy,$radius\" href=\"$link\" alt=\"$alt\" title=\"$alt\"/>\n";		
	}
?>
</map>
	
	
	<?php View::footer($renderstart); ?>
