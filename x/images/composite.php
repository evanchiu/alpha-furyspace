<?php
	//composite.php
	//a tool for laying images on top of one another
	
	$building = "";
	$base = "$building.png";
	$layer = "recycle.png";
	
	$bottom = imagecreatefrompng($base);
    imagealphablending($bottom, true);
	imagesavealpha($bottom, true);
	$top = imagecreatefrompng($layer);
    imagealphablending($top, true);
	imagesavealpha($top, true);
	
	imagecopyresampled($bottom, $top, 0, 0, 0, 0, 50, 50, 50, 50);
    imagedestroy($top);
	
	
    header('Content-type: image/png');  
	imagepng($bottom, "recycling_$building.png");
	imagedestroy($bottom);
?>
