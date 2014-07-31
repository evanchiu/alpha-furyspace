<?php
//controller.php
//the main controller for the application

require_once('global.php');

class Controller{

	//makes sure the user is logged in, if not, show login message
	//also if user has somehow hacked their session, call the bluff and require a logout and fresh login
	static function assert_login(){
		if(!isset($_SESSION['email']) 
			|| $_SESSION['email'] != Controller::sanitize_email($_SESSION['email'])){
			Controller::show_login_page();
		}
	}
	
	//returns true if we're running live on the server, false otherwise
	static function is_live(){
		return preg_match('/furyspace\.com/', $_SERVER['SERVER_NAME']) > 0;
	}
	
	//replaces curly braces (elvises ({, })) with angle backets (alligators (<, >))
	static function elvis_to_alligator($elvis){
		$alligator = preg_replace(array("/\{/", "/\}/"), array("<", ">"), $elvis);
		return $alligator;
	}	
	
	//changes spaces and quote marks into underscores for use in URLs
	static function make_link($string){
		$fixed = strtolower(preg_replace("/(\\s+|\\\"|\\'|\/|\?)/", '_', $string));
		return $fixed;
	}
	
	static function pad($n){
		return substr('00'.$n, -2);
	}
	
	//function error
	// prints an error page and quits the script
	// code is the error code, '' for do not print code
	// message is the error message
	function error($code, $message){
		//set up page
        $renderstart = microtime(true);
		$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
		$data['title'] = "$furyspace } Error {$code}";
		$data['styles'] = array('space.css');
		View::header($data);
		if($code != ''){
			print "<h1>Error {$code}</h1>\n";
		}
		if(isset($message)){
			print "<p class = \"error\">{$message}</p>\n";
		} else {
			print "<p class = \"error\">Wow, I'm sorry, something didn't work out quite right there, if you could write me and tell me what went wrong and how you got here, I'd really appreciate it, thanks!<p>\n";		
		}
		View::footer($renderstart);
		exit(1);
	}
	
	function show_login_page(){
		//this is probably the wrong place, wrong way to do this
        $renderstart = microtime(true);
		$data['title'] = "furyspace } login";
		$data['hide_login'] = true;
		$data['styles'] = array('space.css', 'login.css');
		View::header($data);
		print "<h1>Welcome</h1>\n";
		View::login();
		print '<p>If you have an alpha code, you can <a href = "registrationPage.php">register</a> now.</p>'."\n";
		View::footer($renderstart);
		exit(1);
	}
	
	//returns the first word in the name
	function first_word($name){
		if(isset($name) && strlen($name) > 0){
			$array = preg_split('/\s+/', $name);
			$first = $array[0];
		} else {
			$first = "";
		}
		return $first;
	}
	
	//looks through the $_POST data and creates a new game
	static function create_game(){
		$gameID = Model::get_next_id();
		$galaxy = Controller::sanitize($_POST['galaxy']);
		$size   = $_POST['gsize'];
		$commanderEmails = array();
		$i = 1;
		while(isset($_POST["commander$i"])){
			$email = $_POST["commander$i"];
			if($email != ''){
				$commanderEmails[] = strtolower($email);
			}
			$i++;
		}
		
		//make the game, save it, and get them to further setup
		$g = new Game($gameID, $galaxy, $size, $commanderEmails);
		Model::first_save($g);
		header("Location: setupPage.php?g=$gameID");
	}

	//makes up a name for a planet
	static function invent_name(){
		$vowels = 'aeiou';
		$consonants = 'bcdfghjklmnpqrstvxyz';
		$patterns = array('vccv', 'cvccvc', 'cvccv');
		$pattern = $patterns[mt_rand(0, count($patterns)-1)];
		$name = '';
		for($i = 0; $i < strlen($pattern); $i++){
			if($pattern[$i] == 'v'){
				$name .= $vowels[mt_rand(0, strlen($vowels)-1)];
			} else {
				$name .= $consonants[mt_rand(0, strlen($consonants)-1)];
			}
		}
		return ucfirst($name);
	}
	
	//counts the lines of code of php files in this folder
	static function count_source(){
		$files = 0;
		$lines = 0;
		foreach (glob('*.php') as $filename){
			$files++;
			$filesize = filesize($filename);
			if($filesize > 0){
				$handle = fopen($filename, 'r');
				$data = fread($handle, $filesize);
				$lines += count(split("\n", $data));
				fclose($handle);
			}
		}
		return array($lines, $files);
	}
	
	//quickly strips characters that will screw with database or
	//unserialized out
	static function sanitize($string){
		return preg_replace('/[^A-Za-z0-9-_ ]/', '', $string);
	}
	
	//an email can have periods and at signs
	static function sanitize_email($email){
		return preg_replace('/[^A-Za-z0-9-_.@]/', '', $email);	
	}
	
	//strip all characters that wouldn't be in an int out
	static function sanitize_int($int){
		return (int)preg_replace('/[^0-9]/', '', $int);		
	}
	
	//converts cardinal numbers like 1, 2 and 3 to ordinal numbers
	//like '1st', '2nd', and '3rd'
	static function cardinal_to_ordinal($int){
		$tens = $int % 100;
		$ones = $int % 10;
		if($tens == 11 || $tens == 12 || $tens == 13){
			$suffix = "th";
		} else if ($ones == 1){
			$suffix = "st";
		} else if ($ones == 2){
			$suffix = "nd";
		} else if ($ones == 3){
			$suffix = "rd";
		} else {
			$suffix = "th";
		}
		return $int . $suffix;
	}
	
	//splits a hex color value into its three channels
	//returns three ints - red, green, and blue
	static function split_hex($hex){
		$red 	= hexdec(substr($hex, 0, 2));
		$green 	= hexdec(substr($hex, 2, 2));
		$blue 	= hexdec(substr($hex, 4, 2));
		return array($red, $green, $blue);
	}
	
	
	
	///
	/// @brief takes a singular word and returns the plural version of it
	///
	/// @param $singular singular form of the noun
	/// @return plural form of the noun
	static function pluralize($singular){
		$plural = '';
		
		//if it ends in <consonant>y, change the y to i and add es
		if(preg_match('/[bcdfghjklmnpqrstvwxyz]y$/', $singular)){
			$plural = preg_replace('/y$/', 'ies', $singular);
		} else {
			$plural = $singular . 's';
		}
		return $plural;		
	}
	
	
	//given a filename, returns the unix time that it was modified
	static function modified_time($filename){
	   $time = 0;
	   if(file_exists($filename)){
	       $stat = stat($filename);
	       $time = $stat['mtime'];
	   }
	   return($time);
	}
}

?>
