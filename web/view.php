<?php
//view.php
//contains functions to print XHTML for the application

class View{

	//takes the associate
	static function header($data){
		extract($data);
		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="x/images/fury_favicon.png" />
	<?php
		
		//print meta description
		if(isset($description)){
			print "\t<meta name = \"description\" content = \"$description\" />\n";
		}
		
		//print meta keywords
		if(isset($metakeywords)){
			$keys = implode(', ', $metakeywords);
			print "\t<meta name = \"keywords\" content = \"$keys\" />\n";
		}
		
		//if desired, print meta refresh
		if(isset($metarefresh)){
			print "\t<meta http-equiv=Refresh content='{$metarefresh['time']}; URL={$metarefresh['location']}'> ";
		}
		
		//load CSS
		foreach ($styles as $style) {
            $filename = preg_replace('/\\?.*$/', '', "x/styles/$style");
            $time = Controller::modified_time($filename);
            if (strpos($style, '?')) {
                $href = "x/styles/$style&amp;t=$time";
            } else {
                $href = "x/styles/$style?t=$time";
            }
			print "\t<link rel = \"stylesheet\" type = \"text/css\" href = \"$href\" />\n";
		}
		
		//load JavaScript
		if(isset($scripts)){
			foreach($scripts as $script){
				print "\t<script type = \"text/javascript\" src = \"x/scripts/$script\"></script>\n";
			}
		}
	?>
    <title><?php if (!Controller::is_live()){print '[local] ';} ?><?php print $title ?></title>
<?php
	if(Controller::is_live()){
?>
<!--Google Analytics Tracking Code-->
<script src='http://www.google-analytics.com/ga.js' type='text/javascript'></script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-3338354-4");
pageTracker._trackPageview();
} catch(err) {}</script>
<?php
	} //ends if is live
?>  
  </head>
  	<body>
  		<a name = "top"></a>
		<div id = "container">
			<div id = "banner">
				<div id = "logo">
					<a href = "index.php"><span>alpha.<?php 
	$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
	print $furyspace;?></span></a>
				</div>
<?php if(!isset($hide_login) || !$hide_login){ View::login(); } ?>
			<div class = "puller"></div>
			</div><!--banner-->
			<div id = "epicenter">
<?php	
	}
	
	static function login(){
		//if we have an email, then the user is logged in
		if(isset($_SESSION['email'])){
?>
				<div id = "login">
					<div id = "name">
						<?php print $_SESSION['email']; ?> 
						<form action = "action.php" method="post">
							<span>
								[<a href = "homePage.php">home</a>]
								<input type = "hidden" name = "a" value="logout" />
								<input type = "submit" value = "logout" />
							</span>
						</form>
					</div><!--name-->
				</div><!--login-->
<?php
		//otherwise user is not logged in yet
		} else {
?>
				<div id = "login">
					<form action = "action.php" method = "post">
						<div id = "email"><span>email: <input type = "text" name = "u"/></span></div>
						<div id = "password">
							<span>password: <input type = "password" name = "p"/></span>
							<!--putting these hidden fields in this div is stupid but the XHTML demands it -->
							<input type = "hidden" name="uri" value="<?php print $_SERVER['REQUEST_URI']; ?>" />
							<input type = "hidden" name = "a" value = "login" />
						</div>
						<div id = "button"><span><input type = "submit" value = "login" /></span></div>
					</form>
				</div><!--login-->
<?php
		}
	}
	
	//shows the user's messages, which are stored in the session, then deletes them from the session
	static function messages(){
		if(isset($_SESSION['notification'])){
			print "<p class = \"notification\">{$_SESSION['notification']}</p>\n";
			unset($_SESSION['notification']);
		}
		if(isset($_SESSION['error'])){
			print "<p class = \"error\">{$_SESSION['error']}</p>\n";
			unset($_SESSION['error']);
		}
	}
	
	//
	//	function galaxy_header
	//
	//	prints the galaxy header
	//	@param $name 		- name of the galaxy
	//	@param $commander 	- title and name of the commander
	//	@param $day			- day of the galaxy
	//	@param $stats		- associtive array of statisics about the commander
	//	@param $commit		- 0 means don't show any commit options, 1 is uncommited, 2 is committed
	static function galaxy_header($name, $commander, $day, $stats = null, $commit = 0, $gameID = 0){
?>
			<div id = "galaxy_header">
				<div id = "galaxy_label"><span><?php print "$commander of $name Galaxy"; ?></span></div>
				<div id = "galaxy_day"><span><?php print "day $day"; ?></span></div>
<?php if(isset($stats) and is_array($stats)){ 
		extract($stats);
?>
				<div id = "statistics">
					Planets: 		<?php print $planets; 	?> |
					Ships: 			<?php print $ships; 	?> |
					Gold: 			<?php print $gold;		?> |
					Gold/Turn:	<?php print $gpt;		?> |
					Tech Level:		<?php print $tech_level;?> 
<?php if (isset($tpt)): ?>
					| To Next:		<?php print $to_next;   ?>
					| Tech/Turn:<?php print $tpt;       ?>
<?php endif; ?>
				</div>
<?php 
	} //ends if($statistics) 
	if($commit > 0){
		if($commit == 2){
			print "<div id = \"commitment\"><span>committed</span></div>\n";
		} else {
			print "<div id = \"commitment\"><span>[<a href = \"action.php?g=$gameID&a=commit&origin=html\">commit</a>]</span></div>\n";
		}
	}
?>
				<div class = "puller"></div>
			</div>
<?php
	}
	
	static function navigation($gameID, $page = ''){
		print "\t\t\t<div id = \"tabrow\">\n";
		print "\t\t\t\t<ul>\n";
		print "\t\t\t\t\t<li><a href = \"mapPage.php?g=$gameID\">Map</a></li>\n";
		print "\t\t\t\t\t<li><a href = \"planetsPage.php?g=$gameID\">Planets</a></li>\n";
		print "\t\t\t\t\t<li><a href = \"commandersPage.php?g=$gameID\">Commanders</a></li>\n";
		print "\t\t\t\t\t<li><a href = \"fleetPage.php?g=$gameID\">Fleets</a></li>\n";
		print "\t\t\t\t\t<li><a href = \"reportPage.php?g=$gameID\">Report</a></li>\n";
		print "\t\t\t\t\t<li><a href = \"rulesPage.php?g=$gameID\">Rules</a></li>\n";
		print "\t\t\t\t</ul>\n";
		print "\t\t\t\t<div class = \"puller\"></div>\n";
		print "\t\t\t</div><!--tabrow-->\n";
	}
	
	static function footer($renderstart = 0){
		?>
			<div class = "puller"></div>
			</div><!--epicenter-->
			<div id = "footer">
				<div id = "copy">
					<?php View::copy_year(2009) ?> <a href = "http://evanchiu.com">Evan</a> |
					<a href = "prioritiesPage.php">Priorities</a> |
					<a href = "updatesPage.php">Updates</a><br />
					<a href = "http://jigsaw.w3.org/css-validator/check/referer">
						<img class = "borderless" src = "x/images/w3c_css.png" 
							alt = "This website meets W3C CSS standards." />
					</a>
					<a href = "http://validator.w3.org/check?uri=referer">
						<img class = "borderless" src = "x/images/w3c_xhtml10.png" 
							alt = "This website meets W3C XHTML 1.0 strict standards." />
					</a>
				</div><!--copy-->
				<div id="stats">
				<?php
					list($lines, $files) = Controller::count_source();
				    if($renderstart){
				        $rendertime = round(microtime(true) - $renderstart, 2);
				        $render = "| rendered in <b>$rendertime</b> seconds";
				    } else {
				        $render = '';
				    }
					print "Server: <b>{$_SERVER['SERVER_NAME']}</b> | Files: <b>$files</b> | Lines: <b>$lines</b> $render\n";
				?>
				</div><!--stats-->
			<div class = "puller"></div>
			</div><!--close footer-->
		<div class = "puller"></div>
		</div><!--close container-->	
  </body>
</html><?php
	}
	
	///
	/// @brief returns a string that is a properly colored link to the planet
	///
	/// @param $gid the galaxy id
	/// @param $planetname the planet's name to link to
	static function planetlink($gid, $planetname){
		return "<a href = \"mapPage.php?g=$gid&planet=$planetname\"><span color = \"cyan\">$planetname</span></a>";
	}
	
	
	///
	/// @brief returns a string that is a properly colored commander with name and title
	///
	/// @param $game the game that the commander plays in
	/// @param $email the commander's email
	static function commanderspan($game, $email){
		$name  = $game->commanders[$email]['name'];
		$title = $game->commanders[$email]['title'];
		$color = $game->commanders[$email]['color'];
		return "<span class = \"$color\">$title $name</span>";
	}	
	
	static function copy_year($start = false)
	{
	   $current = date("Y");
	   if(!$start || $start == $current){
	     $year = $current;
	   } else {
	     $year = "$start-$current";
	   }
	   print "&copy;$year";
	}
}
