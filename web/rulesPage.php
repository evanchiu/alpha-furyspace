<?php
	//rulesPage.php
	//this page shows the user the rules of the game filled in with
	//configuration options for the game
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
	$data['title'] = "$furyspace } $game->galaxy Rules";
	$data['styles'] = array('space.css', "style.php?color={$game->commanders[$email]['color']}");
	View::header($data);
	View::messages();
	View::galaxy_header($game->galaxy, "$title $name", $game->day, $game->get_stats($email), $game->get_commit($email), $gameID);
	View::navigation($gameID, "map");
?>
<div id = "tab">
	<h3>Basics</h3>
	<div class = "sub">
	<ul class = "readable">
		<li>Factories make ships</li>
		<li>Ships explore the galaxy, invade enemies, and protect your planets</li>
		<li>Mines harvest gold</li>
		<li>Gold is paid to build more buildings and ships</li>
		<li>Facilities do research</li>
		<li>Research makes your ships go farther, faster, fight better, and lets you see more of the galaxy</li>
		<li>Take over enemies' home planets to beat them</li>
	</ul>
	</div>
	<h3>Concept</h3>
	<div class = "sub">
		<p>Furyspace is a space-themed small-group strategy game.  You become a commander, you get a home planet with decent statistics (2/5 or 3/6) and some resources (<b><?php print $game->cost['startup']; ?></b> gold) to start with, and you begin building your empire.  If you don't read the rest of this, just know that <b>capturing a home planet destroys the Commander</b>, so guard it responsibly.</p>
		<p><b>Update!</b> Ships defending the home planet get a power bonus of <b>+10</b>. (See battles for a description of power)</p>
	</div>
	
	<h3>Galaxies</h3>
	<div class = "sub">
		<p>A Galaxy is an instance of this game.  It has a set amount of planets, and a set of commanders that are playing in it.  You are currently playing in the <b><?php print $game->galaxy; ?></b> Galaxy.</p>
		<div class = "sub">
			<h4>Distances</h4>
			<p>Distances are measured in Zaphods.  Your map draws a thin line every zaphod and a thick line every five to help you estimate how far planets are apart.</p>
		</div>
	</div>
	
	<h3>Planets</h3>
	<div class = "sub">
		<p>Planets are stationary in this game.  Each is assigned a location at Galaxy Creation.  A planet has two unrelated ratings, class and size.</p>
		<div class = "sub">
			<h4>Class</h4>
			<p>Class is rated on a scale of 1 to 5, with 1st being the best.  Class determines how well mines work on that planet.  A mine will produce <span class = "code">55 - (5 * class)</span> gold per day.  So on a 1st class, you'll get 50 gold/day, on a 2nd class, 45 gold/day, on a 5th class, 30 gold/day.</p>
			<h4>Size</h4>
			<p>Size indicates how many buildings may be built on the planet</p>
			<h4>Home Planet</h4>
			<p>Each Commander is assigned one home planet.  Yours is <a href = "planetsPage.php?g=<?php print $gameID; ?>#<?php print $game->commanders[$email]['homeplanet']; ?>"><?php print $game->commanders[$email]['homeplanet']; ?></a>.  <b>If a home planet is conquered, the commander is taken out of the game.</b>  All his (or her) ships are destroyed and his (or her) planets become unoccupied.</p>
		</div>
	</div>
	
	<h3>Architecture</h3>
	<div class = "sub">
		<p>There are three types of buildings that can be built.  After building, these can be recycled for one fourth of their build cost.</p>
		<div class = "sub">
			<h4>Research Facilities</h4>
			<p>Research Facilities (or simply facilities) do research.  Each produces one tech unit per day.  A facility costs <b><?php print $game->cost['facility']; ?></b> gold to build.  Once built, a research facility will function automatically.</p>
			<h4>Mines</h4>
			<p>Mines produce gold.  The better the planet's class, the more gold produced per mine per turn.  The formula is listed under Planet Class.  A mine costs <b><?php print $game->cost['mine']; ?></b> gold to build. Once built, a mine will function automatically.</p>
			<h4>Factories</h4>
			<p>Factories produce ships.  Each factory can build one ship per turn.  A facility costs <b><?php print $game->cost['factory']; ?></b> gold to build.  Once built, ships can be manually queued up, turning the factory into a pregnant_factory.</p>
		</div>
	</div>
		
	<h3>Ships</h3>
	<div class = "sub">
		<p>Ships are your primary (i.e. only way in this version) of defending your planets, attacking enemy planets, and exploring the galaxy.  A ship costs <b><?php print $game->cost['ship']; ?></b> gold and can be built at a factory.  A ship can travel 6 + tech_level zaphods (currently <b><?php print ($game->get_total_range($email));?></b> for you) in three days.  In a single day, your ship will fly <b><?php print($game->get_day_range($email)); ?></b> zaphods.</p>
	</div>
	
	<h3>Sight</h3>
	<div class = "sub">
		<p>You can only see planets that you can fly to.  That is, only planets within <b><?php print ($game->get_total_range($email));?></b> zaphods of any of your planets.  At level 4, you will be able to see who owns the planets you can see.  When you are conquered, you can see all the planets and who owns them.  And you might share this information with other commanders...</p>
	</div>

	<h3>Fleets</h3>
	<div class = "sub">
		<p>A fleet is a group of ships (or just one) moving through space from one planet to another.  Fleets are dispatched from planets that have ships on them. Fleets will not combine (so you might have two fleets going to the same place on the same day, they will attack separately).  When a fleet lands on a planet you own, its ships go into orbit around it.  When a fleet lands on an unoccupied planet, you take it over.  When a fleet attempts to land on an enemy planet, it will go into battle with the enemy ships there.</p>
		<h4>Order</h4>
		<p>Fleets are processed by commander, but the commander order is random, so potentially different every day.  If two fleets are scheduled to arrive at the same planet, on the same day, whichever commander is processed first will act first.  So it may be that ships you send to protect a planet will arrive first and protect, or it may be conquered, yet you reconquer it the same turn (because your opponent was processed before you).  Also if two opposing commanders arrive at a third contenders planet on the same day, the first commander processed will fight first, then the second commander will fight whoever owns the planet after that.  The order is randomized because this second commander has an obvious advantage.</p>
	</div>
		
	<h3>Technology</h3>
	<div class = "sub">
		<p>As your facilities do research, you increase in tech level.  The formula for tech level is <span class = "code">floor(sqrt(tech_units))</span>.  You currently have <b><?php print $game->commanders[$email]['tech']; ?></b> units, putting you at level <b><?php print $game->get_tech_level($email); ?></b>.</p>
	</div>
	
	<h3>Battles</h3>
	<div class = "sub">
		<p>Yeah, battles!  This is where the action happens.  Battles are fought one ship at a time.  As a front ship gets destroyed another one from the fleet or orbital moves in to replace it.  Ships have a power rating of <span class = "code">5 + tech_level</span> (currently <b><?php print (5 + $game->get_tech_level($email));?></b> for you).  When two ships fight a random number is drawn between the sum of their power levels and whoever's side it lands on is the winner.  So a ship with power level 15 has 75% chance of beating a ship with power level 5.</p>
	</div>

<div class = "puller"></div>
</div><!--tab-->
<?php View::footer($renderstart); ?>
