<?php
	//mapImage.php
	//returns an image of the map
	session_start();
	include('controller.php');
	Controller::assert_login();
	
	$email = $_SESSION['email'];
	$turn = Request::turn(false);
	$omni = false;					//set this flag true to see everything
	if(!$turn){
		$turn = 0;
	} else {
		$omni = true;
	}
	
	$gameID = (isset($_GET['g'])) ? $_GET['g'] : '';
	if($gameID == ''){
		Controller::error(701, "I'm sorry, I can't tell which game you're looking for.");
	} else {
		$game = Model::get_game($gameID, $turn);
		if($game == null){
			Controller::error(702, "I'm sorry, I can't find game <b>$gameID</b>.");
		} if (!in_array($email, array_keys($game->commanders))){
			Controller::error(708, "I'm sorry, it doesn't look like you are playing in game <b>$gameID</b>. " .
								   "[<a href = \"homePage.php\">home</a>]");
		}
	}
	
	$size = (isset($_GET['s'])) ? $_GET['s'] : 500;
	$transparency = (isset($_GET['t'])) ? $_GET['t'] : 10;
	//check if they're looking for a particular planet
	$bigplanet = Request::valid_planet($gameID, false);
	
	//draw the map
	$height = $size;
	$width  = $size;
	$zaphodToPixel = $height / $game->height;
	
	//setup the canvas and paint a transparent dark all over it
	$canvas = imagecreatetruecolor($width, $height);
   	imagesavealpha($canvas, true);
    $trans_colour = imagecolorallocatealpha($canvas, 0, 0, 0, $transparency);
    imagefill($canvas, 0, 0, $trans_colour);
    	    
   	//configure the brushes
	$white 		= imagecolorallocate($canvas, 255, 255, 255);
	$lightgray  = imagecolorallocate($canvas, 100, 100, 100);
	$darkgray	= imagecolorallocate($canvas, 50, 50, 50);
	//allocate each color
	$colors = Configuration::colors();
	$colorbrushes = array();
	foreach($colors as $name => $hex){
		list($red, $green, $blue) = Controller::split_hex($hex);
		$colorbrushes[$name] = imagecolorallocate($canvas, $red, $green, $blue);
	}
	//my color
	$mycolor = $colorbrushes[$game->commanders[$email]['color']];
	   
	//paint grid
	for($i = 1; $i <= $game->width; $i++){
		$linecolor = ($i % 5 == 0) ? $lightgray : $darkgray;
	   	//vertical line
	   	imageline($canvas, $i * $zaphodToPixel, 0, $i * $zaphodToPixel, $height, $linecolor);
	   	//horizontal line
	   	imageline($canvas, 0, $i * $zaphodToPixel, $width, $i * $zaphodToPixel, $linecolor);
	}
	   
	//paint planets!
	if(isset($game->commanders[$email]['visible'])){
		if($omni){
			$visiblenames = array_keys($game->planets);
		} else {
			$visiblenames = $game->commanders[$email]['visible'];
		}
		foreach ($visiblenames as $name){
			$planet = $game->planets[$name];
			
		 	//draw a circle for the planet
		   	$cx = $planet['x'] * $zaphodToPixel;
		   	$cy = $planet['y'] * $zaphodToPixel;
		   	if ($game->is_color_visible($email, $name)){
		   		//color of planet owner
		   		$color = $colorbrushes[$game->commanders[$planet['owner']]['color']];
		   	} else if($omni){
		   		if($planet['owner']){
			   		$color = $colorbrushes[$game->commanders[$planet['owner']]['color']];
		   		} else {
		   			$color = $white;
		   		}
		   	} else {
		   		//I can't tell the color, so its white
		   		$color = $white;
		   	}
		   	
		   	//if selected, pump up the radius
		   	if($name == $bigplanet){
		   		$radius = 12;
		   		//draw concentric circles for distance
		   		//imageellipse($canvas, $cx, $cy, 200, 200, $white);
		   	} else {
		   		$radius = 8;
		   	}
		   	
			imagefilledellipse($canvas, $cx, $cy, $radius, $radius, $color);
		   		
		   	//label it
			imagestring($canvas, 2, $cx - 15, $cy - 18, $name, $color);
		}
	} else {
		foreach ($game->planets as $name => $planet){
		 	//draw a circle for the planet
		   	$cx = $planet['x'] * $zaphodToPixel;
		   	$cy = $planet['y'] * $zaphodToPixel;
		   	$radius = 8;
		   	if($planet['owner'] == $email){
			   	imagefilledellipse($canvas, $cx, $cy, $radius, $radius, $mycolor);	
		   	} else {
			   	imagefilledellipse($canvas, $cx, $cy, $radius, $radius, $white);
			}
		   		
		   	//label it
		   	if($planet['owner'] == $email){
				imagestring($canvas, 2, $cx - 15, $cy - 18, $name, $mycolor);	
		   	} else {
				imagestring($canvas, 2, $cx - 15, $cy - 18, $name, $white);
			}
		}
	}
	
	header('Content-type: image/png');   
	imagepng($canvas);	
?>