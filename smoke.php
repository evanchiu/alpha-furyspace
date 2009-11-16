<?php
	//mapImage.php
	//returns an image of the map
	
	$size 			= (isset($_GET['s'])) ? $_GET['s'] 	: 10;
	$transparency 	= (isset($_GET['t'])) ? $_GET['t'] 	: 10;
	$chanel 		= (isset($_GET['c'])) ? $_GET['c'] 	: 0;
	$automate 		= (isset($_GET['a'])) ? true 		: false;
	
	//draw the map
	$height = $size;
	$width  = $size;
	
	if($automate){
		for($i = 0; $i < 131; $i += 10){
			$transparency = ($i > 128) ? 127 : $i;
		
			//setup the canvas and paint a transparent dark all over it
			$canvas = imagecreatetruecolor($width, $height);
		  	imagesavealpha($canvas, true);
		  	$trans_colour = imagecolorallocatealpha($canvas, $chanel, $chanel, $chanel, $transparency);
		    imagefill($canvas, 0, 0, $trans_colour);
   		 	
   		 	//output to file
			header('Content-type: image/png');   
			$filename = "smoke$transparency.png";
			imagepng($canvas, $filename);
			print $filename;
		}
	} else {
		//setup the canvas and paint a transparent dark all over it
		$canvas = imagecreatetruecolor($width, $height);
	  	imagesavealpha($canvas, true);
	  	$trans_colour = imagecolorallocatealpha($canvas, $chanel, $chanel, $chanel, $transparency);
	    imagefill($canvas, 0, 0, $trans_colour);
   	 	
   	 	//print to screen    	
		header('Content-type: image/png');   
		imagepng($canvas);
	}
?>