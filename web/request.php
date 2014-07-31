<?php
//request.php
//holds the request class
//processes all input
//when this class is complete, no other class should ever touch the superglobals

class Request{
	static function color(){
		if(!isset($_REQUEST['color'])){
			Controller::error(711, "Missing <b>color</b> in the request data.");
		}

		$color =  Request::alpha_filter($_REQUEST['color']);
		if(!in_array($color, Configuration::color_names())){
			Controller::error(711, "<b>$color</b> is not a valid color.");
		}

		return $color;
	}

	//check whether a user is logged in
	static function is_logged_in(){
		//todo: check hash here too
		return (isset($_SESSION['email']));
	}

	//get the email the user logged in with
	static function login_email(){
		//todo: add a protective hash to make spoofing more difficult
		return Request::sanitize_email($_SESSION['email']);
	}

	static function gid(){
		$gid = (isset($_REQUEST['g'])) ? $_REQUEST['g'] : 0;
		$gid = (isset($_REQUEST['gid'])) ? $_REQUEST['gid'] : $gid;
		if($gid){
			return Request::sanitize_int($_REQUEST['gid']);
		} else {
			Controller::error(701, "Sorry, I can't tell which game you're looking for");
		}
	}

	//gets 'slot' out of request data
	static function slot(){
		if(isset($_REQUEST['slot'])){
			return Request::sanitize_int($_REQUEST['slot']);
		} else {
			Controller::error(711, "Sorry, I'm missing <b>slot</b> from the input data.");
		}
	}

	// gets for the planet parameter in the request data
	// if $demand is true, it shows an error page if planet data is missing
	// if $demand is false, it returns an empty string if planet data is missing
	static function planet($demand = true){
		if(isset($_REQUEST['planet'])){
			return Request::alpha_filter($_REQUEST['planet']);
		} else {
			if($demand){
				Controller::error(711, "Sorry, I'm missing <b>planet</b> from the input data.");
			} else {
				return '';
			}
		}
	}
	
	//gets the planet, then checks to make sure it's in the game
	static function valid_planet($gid, $demand = true){
		$planetname = Request::planet($demand);
		$game = Model::get_game($gid);
		if(in_array($planetname, array_keys($game->planets))){
			return $planetname;
		} else {
			if($demand){
				Controller::error(711, "Sorry, It doesn't appear that there's a planet <b>planet</b> in game <b>$gid</b>.");
			} else {
				return '';
			}
		}
	}	
	
	//gets the fleet number
	static function fleet($demand = true){
		if(isset($_REQUEST['fleet'])){
			return Request::sanitize_int($_REQUEST['fleet']);
		} else {
			if($demand){
				Controller::error(711, "Sorry, I'm missing <b>fleet</b> from the input data.");
			} else {
				return '';
			}
		}
	}
	
	//gets the turn number
	//TODO: fix temp obfuscation hack
	static function turn($demand = true){
		if(isset($_REQUEST['omniturn'])){
			return Request::sanitize_int($_REQUEST['omniturn']);
		} else {
			if($demand){
				Controller::error(711, "Sorry, I'm missing <b>turn</b> from the input data.");
			} else {
				return '';
			}
		}
	}

	//generic filter function which returns a $string containing only characters in the $legal set
	static function filter($string, $legals){
		return preg_replace("/[^$legals]/", '', $string);
	}

	//strip all characters that wouldn't be in an int out
	static function sanitize_int($int){
		return (int)preg_replace('/[^0-9]/', '', $int);		
	}

	//alpha numeric
	static function alpha_filter($string){
		return Request::filter($string, 'A-Za-z');
	}

	//an email can have periods and at signs
	static function sanitize_email($email){
		return preg_replace('/[^A-Za-z0-9-_.@]/', '', $email);	
	}
}

?>
