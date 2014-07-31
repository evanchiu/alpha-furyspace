<?php
	//style.php
	//adds the color for links
	//its dynamic
	include('../../controller.php');
	
	$colors = Configuration::colors();
	
	header("content-type: text/css");
?>
/*
 * colors.php - spans of every color
 */

<?php
	foreach($colors as $name => $hex){
		print "span.$name{\n";
		print "\tcolor: #$hex;\n";
		print "}\n\n";
	}
?>

		
	
	
	
