<?php
	//style.php
	//adds the color for links
	//its dynamic
	include('../../controller.php');
	
	$color = Request::color();
	
	$colors = Configuration::colors();
	
	header("content-type: text/css");
?>
/*
 * style.php - adds dynamic style for the player's color 
 * current style is *<?php print $color; ?>* 
 * Also styles for every color, since this is configurable
 */

a{
	color: #<?php print $colors[$color]; ?>;
}

		
	
	
	
