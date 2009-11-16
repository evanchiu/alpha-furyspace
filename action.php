<?php
	//action.php
	//this is the action handler, all forms and user input, 
	//other than inital login, should route through here
	session_start();
	require_once('global.php');	
	
	//front part parses the input, then calls a static method of the action class
	//typically the 'a' request variable will tell what kind of action this is
	
	Action::demand(array('a'));
	
	$action = $_REQUEST['a'];
	switch($action){
		case 'login':
			Action::demand(array('u', 'p'));
			Action::login(
				$_REQUEST['u'],
				$_REQUEST['p']
			);
			$location = (isset($_REQUEST['uri'])) ? $_REQUEST['uri'] : 'homePage.php';
			//certain logins should redirect to home page.
			if(!preg_match('/Page\.php/', $location)){
				$location = 'homePage.php';
			}
			header("Location: $location");
			break;
			
		case 'logout':
			unset($_SESSION['email']);
			$_SESSION['notification'] = "You have logged out successfully!";
			header('Location: index.php');
			break;
			
		case 'commit':
			Controller::assert_login();
			Action::demand(array('g', 'origin'));
			Action::commit($_SESSION['email'],
						  $_REQUEST['g']
						  );	
			//if this came from HTML, reroute them back to the planet page
			if($_REQUEST['origin'] == 'html'){
				$_SESSION['notification'] = "Your orders are committed.";
				header("Location: commandersPage.php?g={$_REQUEST['g']}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
		case 'build_structure':
			Controller::assert_login();
			Action::demand(array('building', 'planet', 'g', 'origin'));
			Action::build_structure($_SESSION['email'],
						  $_REQUEST['building'],
						  $_REQUEST['planet'],
						  $_REQUEST['g'],
						  Request::slot()
						  );	
			//if this came from HTML, reroute them back to the appropriate page
			if($_REQUEST['origin'] == 'html' || $_REQUEST['origin'] == 'map'){
				header("Location: mapPage.php?g={$_REQUEST['g']}&planet={$_REQUEST['planet']}");
			} else if ($_REQUEST['origin'] == 'planets'){
				header("Location: planetsPage.php?g={$_REQUEST['g']}#{$_REQUEST['planet']}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
		case 'cancel_structure':
			Controller::assert_login();
			Action::demand(array('building', 'planet', 'g', 'origin'));
			Action::cancel_structure($_SESSION['email'],
						  $_REQUEST['building'],
						  $_REQUEST['planet'],
						  $_REQUEST['g'],
						  Request::slot()
						  );
			//if this came from HTML, reroute them back to the appropriate page
			if($_REQUEST['origin'] == 'html' || $_REQUEST['origin'] == 'map'){
				header("Location: mapPage.php?g={$_REQUEST['g']}&planet={$_REQUEST['planet']}");
			} else if ($_REQUEST['origin'] == 'planets'){
				header("Location: planetsPage.php?g={$_REQUEST['g']}#{$_REQUEST['planet']}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;  
			
		case 'build_ship':
			Controller::assert_login();
			Action::demand(array('planet', 'g', 'origin'));
			Action::build_ship($_SESSION['email'],
						  $_REQUEST['planet'],
						  $_REQUEST['g'],
						  Request::slot()
						  );	
			//if this came from HTML, reroute them back to the appropriate page
			if($_REQUEST['origin'] == 'html' || $_REQUEST['origin'] == 'map'){
				header("Location: mapPage.php?g={$_REQUEST['g']}&planet={$_REQUEST['planet']}");
			} else if ($_REQUEST['origin'] == 'planets'){
				header("Location: planetsPage.php?g={$_REQUEST['g']}#{$_REQUEST['planet']}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
		case 'cancel_ship':
			Controller::assert_login();
			Action::demand(array('ship', 'planet', 'g', 'origin'));
			Action::cancel_ship($_SESSION['email'],
						  $_REQUEST['ship'],
						  $_REQUEST['planet'],
						  $_REQUEST['g'],
						  Request::slot()
						  );
			//if this came from HTML, reroute them back to the appropriate page
			if($_REQUEST['origin'] == 'html' || $_REQUEST['origin'] == 'map'){
				header("Location: mapPage.php?g={$_REQUEST['g']}&planet={$_REQUEST['planet']}");
			} else if ($_REQUEST['origin'] == 'planets'){
				header("Location: planetsPage.php?g={$_REQUEST['g']}#{$_REQUEST['planet']}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break; 
			
		case 'dispatch':
			Controller::assert_login();
			Action::demand(array('number', 'source', 'destination', 'g', 'origin'));
			Action::dispatch($_SESSION['email'],
						  $_REQUEST['number'],
						  $_REQUEST['source'],
						  $_REQUEST['destination'],
						  $_REQUEST['g']
						  );
			//if this came from HTML, reroute them back to the appropriate page
			if($_REQUEST['origin'] == 'html' || $_REQUEST['origin'] == 'map'){
				$destplanet = View::planetlink($_REQUEST['g'], $_REQUEST['destination']);
				$s = ($_REQUEST['number'] == 1) ? '' : 's';
				$_SESSION['notification'] = "You sent {$_REQUEST['number']} ship$s to $destplanet";
				header("Location: mapPage.php?g={$_REQUEST['g']}&planet={$_REQUEST['source']}");
			} else if ($_REQUEST['origin'] == 'planets'){
				header("Location: planetsPage.php?g={$_REQUEST['g']}#{$_REQUEST['source']}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;		
			
		case 'recall':
			Controller::assert_login();
			Action::demand(array('g', 'origin', 'fleet'));
			$fleet = Action::recall($_SESSION['email'],
							$_REQUEST['g'],
							Request::fleet()
						  );
			//if this came from HTML, reroute them back to the appropriate page
			if($_REQUEST['origin'] == 'html' || $_REQUEST['origin'] == 'fleet'){
				if($fleet){
					$s = ($fleet['size'] == 1) ? '' : 's';
					$planetlink = View::planetlink($_REQUEST['g'], $fleet['destination']);
					$_SESSION['notification'] = "You canceled a fleet of <b>{$fleet['size']}</b> ship$s headed to $planetlink.";
				}
				header("Location: fleetPage.php?g={$_REQUEST['g']}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
		
		case 'recycle_structure':
			Controller::assert_login();
			Action::demand(array('g', 'planet', 'slot', 'origin'));
			Action::recycle_structure($_SESSION['email'],
						  $_REQUEST['g'],
						  $_REQUEST['planet'],
						  Request::slot()
						  );
			//if this came from HTML, reroute them back to the appropriate page
			if($_REQUEST['origin'] == 'html' || $_REQUEST['origin'] == 'map'){
				header("Location: mapPage.php?g={$_REQUEST['g']}&planet={$_REQUEST['planet']}");
			} else if ($_REQUEST['origin'] == 'planets'){
				header("Location: planetsPage.php?g={$_REQUEST['g']}#{$_REQUEST['planet']}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;	
		
		case 'cancel_recycle':
			Controller::assert_login();
			Action::demand(array('g', 'planet', 'slot', 'origin'));
			Action::cancel_recycle($_SESSION['email'],
						  $_REQUEST['g'],
						  $_REQUEST['planet'],
						  Request::slot()
						  );
			//if this came from HTML, reroute them back to the appropriate page
			if($_REQUEST['origin'] == 'html' || $_REQUEST['origin'] == 'map'){
				header("Location: mapPage.php?g={$_REQUEST['g']}&planet={$_REQUEST['planet']}");
			} else if ($_REQUEST['origin'] == 'planets'){
				header("Location: planetsPage.php?g={$_REQUEST['g']}#{$_REQUEST['planet']}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
		case 'update':
			Controller::assert_login();
			Action::demand(array('commander', 'color', 'gid', 'origin'));
			Action::update_game($_SESSION['email'],
						   $_REQUEST['commander'],
						   Request::color(),
						   $_REQUEST['gid']
						   );
			//if this came from HTML, reroute them back to the planet page
			if($_REQUEST['origin'] == 'html'){
				header("Location: mapPage.php?g={$_REQUEST['gid']}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
		case 'register':
			Action::demand(array('code', 'email', 'password', 'password2', 'origin'));
			Action::register($_REQUEST['code'], 
							 $_REQUEST['email'],
							 $_REQUEST['password'],
							 $_REQUEST['password2']
							 );
			//if this came from HTML, log them in
			if($_REQUEST['origin'] == 'html'){
				$_SESSION['notification'] = "Congratulations, your user account has been created successfully!";
				Action::login($_REQUEST['email'], $_REQUEST['password']);
				header("Location: homePage.php");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
		case 'queue_all':
			//user wants to queue up all ships
			Action::demand(array('gid', 'origin'));
			$gid = Request::gid();
			$queued = Action::queue_all($_SESSION['email'], $gid);
							  
			//if this came from HTML, route them back
			if($_REQUEST['origin'] == 'html' || $_REQUEST['origin'] == 'planets'){
				$s = ($queued == 1) ? '' : 's';
				$_SESSION['notification'] = "You have queued <b>$queued</b> ship$s.";
				header("Location: planetsPage.php?g={$gid}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
		case 'queue_planet':
			//user wants to queue up all ships on a particular planet
			Action::demand(array('gid', 'origin', 'planet'));
			$gid = Request::gid();
			$planetname = Request::planet();
			$queued = Action::queue_planet($_SESSION['email'], 
										   $gid, 
										   $planetname);
							  
			//if this came from HTML, route them back
			$planetlink = View::planetlink($gid, $planetname);
			if($_REQUEST['origin'] == 'html' || $_REQUEST['origin'] == 'map'){
				$s = ($queued == 1) ? '' : 's';
				$_SESSION['notification'] = "You have queued <b>$queued</b> ship$s on $planetlink.";
				header("Location: mapPage.php?g={$gid}&planet={$planetname}");
			} else if ($_REQUEST['origin'] == 'planets'){
				$s = ($queued == 1) ? '' : 's';
				$_SESSION['notification'] = "You have queued <b>$queued</b> ship$s on $planetlink.";
				header("Location: planetsPage.php?g={$gid}#{$planetname}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
		
			
		case 'timeout':
			//user thinks turn has timed out
			Action::demand(array('gid', 'origin'));
			$gid = Request::gid();
			$queued = Action::timeout($_REQUEST['gid']);
							  
			//if this came from HTML, route them back
			if($_REQUEST['origin'] == 'html'){
				header("Location: reportPage.php?g={$gid}");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
			
		case 'add_code':
			Action::demand(array('code', 'origin'));
			Action::add_code($_REQUEST['code']);
			//if this came from HTML, redirect back
			if($_REQUEST['origin'] == 'html'){
				$_SESSION['notification'] = "<b>{$_REQUEST['code']}</b> has been added to codes.";
				header("Location: adminPage.php");
			}			 else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}	
			break;
			
		case 'delete_galaxy':
			Controller::assert_login();
			Configuration::is_administrator($_SESSION['email']) || Controller::error(705, "Deleting galaxies is an administrator tool.");
			Action::delete_galaxy($_REQUEST['gid']);
			//if this came from HTML, redirect back
			if($_REQUEST['origin'] == 'html'){
				$_SESSION['notification'] = "<b>{$_REQUEST['gid']}</b> has been deleted.";
				header("Location: adminPage.php");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
		case 'become_player':
			Controller::assert_login();
			Configuration::is_administrator($_SESSION['email']) || Controller::error(705, "Becoming Players is an administrator tool.");
			$_SESSION['email'] = $_REQUEST['email'];
			//if this came from HTML, redirect back
			if($_REQUEST['origin'] == 'html'){
				$_SESSION['notification'] = "You have become <b>{$_REQUEST['email']}</b>.";
				header("Location: homePage.php");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
		case 'kick_player':
			Controller::assert_login();
			Configuration::is_administrator($_SESSION['email']) || Controller::error(705, "Kicking Players is an administrator tool.");
			Action::kick_player($_REQUEST['gid'], $_REQUEST['email']);
			//if this came from HTML, redirect back
			if($_REQUEST['origin'] == 'html'){
				$_SESSION['notification'] = "<b>{$_REQUEST['email']}</b> from <b>{$_REQUEST['gid']}</b> has been kicked.";
				header("Location: adminPage.php");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
		case 'kill_phantoms':
			Controller::assert_login();
			Configuration::is_administrator($_SESSION['email']) || Controller::error(705, "Killing Phantoms is an administrator tool.");
			if(Action::kill_phantoms($_REQUEST['gid'])){
				$_SESSION['notification'] = "Some phantoms in <b>{$_REQUEST['gid']}</b> have been killed.";				
			}
			//if this came from HTML, redirect back
			if($_REQUEST['origin'] == 'html'){
				header("Location: adminPage.php");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
		case 'create_database':
			Controller::assert_login();
			Configuration::is_administrator($_SESSION['email']) || Controller::error(705, "Creating Databases is an administrator tool.");
			// Create database
			
            if ($db = sqlite_open('mysqlitedb', 0666, $sqliteerror)) { 
                //sqlite_query($db, 'CREATE TABLE foo (bar varchar(10))');
                sqlite_query($db, "INSERT INTO foo VALUES ('wom')");
                $result = sqlite_query($db, 'select bar from foo');
            } else {
                die($sqliteerror);
            }            			
			
			//if this came from HTML, redirect back
			if($_REQUEST['origin'] == 'html'){
				$_SESSION['notification'] = "Database created successfully. (result)";
				header("Location: homePage.php");
			} else {
				Controller::error(711, "Hello, where did <i>you</i> come from?");
			}
			break;
			
		default:
			Controller::error(706, "I'm not sure what you're doing here...");
	}		

	
	///
	/// @class Action
	///
	/// @brief Makes user actions happen
	///
	class Action{
	
		///
		/// @brief demands that certain parameters are in the $_REQUEST array.
		///
		/// Produces a 711 if a parameter is missing.
		/// 
		/// @param $data an array of the required indicies of the $_REQUEST array
		static function demand($data){
			foreach($data as $dot){
				if(!isset($_REQUEST[$dot])){
					Controller::error(711, "I'm missing the <b>$dot</b> parameter in your request data.");
				}
			}
		}

		///
		/// @brief logs the user into the system by setting session variables
		///
		/// @param $email the email user wants to log in with
		/// @param $password the password the user provided
		static function login($email, $password){
			$email = strtolower(Controller::sanitize_email($email));
			if(Model::matches($email, $password)){
				$_SESSION['email'] = $email;
				$_SESSION['notification'] = "You have logged in successfully!";
			} else {
				Controller::error(703, "I'm sorry, your email and password do not match.");
			}
		}
		
		///
		/// @brief commits a player's actions for their turn
		///
		/// @param $email the user's primary email
		/// @param $gid the game id they've committed in
		static function commit($email, $gid){	
			list($game, $link) = Model::load(Controller::sanitize_int($gid));
			$archive = false;
						
			//validate input
			if($game == null){	
				Model::rollback($link);
				Controller::error(711, "It turns out <b>$gid</b> isn't a valid game identifier, sorry.");
			}
			if(!array_key_exists($email, $game->commanders)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$email</b> isn't commanding in <b>{$game->galaxy}</b>, sorry.");
			}
			
			$game->commanders[$email]['last'] = $game->day;
			$game->committed[] = $email;
			$game->committed = array_unique($game->committed);
			if(count($game->committed) == count($game->commanders) - count($game->conquered)
				&& $game->status != 'finished'){
				$game->execute();
				$archive = true;
			}
			
			$result = Model::save($game, $link);
			if($result && $archive){
				$result = Model::archive($game);
			}
			return $result;
		}
		
		///
		/// @brief force-rolls the turn since it has timed out
		///
		/// @param gid the game identifier
		static function timeout($gid){
			list($game, $link) = Model::load(Controller::sanitize_int($gid));
			$archive = false;
						
			//validate input
			if($game == null){	
				Model::rollback($link);
				Controller::error(711, "It turns out <b>$gid</b> isn't a valid game identifier, sorry.");
			}
			if($game->day_age() <= $game->day_timeout()){
				Model::rollback($link);
				$age = $game->day_age();
				$to = $game->day_timeout();
				Controller::error(712, "Game <b>$gid</b> hasn't timed out yet.  The turn has been up for <b>$age</b> seconds, and times out after <b>$to</b> seconds.");
			}
			
			if($game->status == 'active'){
				$game->execute();
				$archive = true;
			}
			
			$result = Model::save($game, $link);
			if($result && $archive){
				$result = Model::archive($game);
			}
			return $result;
		}
	
		///
		/// @brief builds a structure on a planet
		///
		/// @param $email 			the player paying for the building
		/// @param $buildingname 	the type of building to be built
		/// @param $planetname 		the name of the planet to build on
		/// @param $gid 			the game identifier
		/// @param $slot 			index of where to build the building
		static function build_structure($email, $buildingname, $planetname, $gid, $slot){		
			list($game, $link) = Model::load(Controller::sanitize_int($gid));
			
			//validate input
			if($game == null){	
				Model::rollback($link);
				Controller::error(711, "It turns out <b>$gid</b> isn't a valid game identifier, sorry.");
			}
			if(!array_key_exists($email, $game->commanders)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$email</b> isn't commanding in <b>{$game->galaxy}</b>, sorry.");
			}
			if(!array_key_exists($buildingname, $game->cost)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$buildingame</b> isn't a valid name for a building, sorry.");			
			}
			if(!array_key_exists($planetname, $game->planets)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$planetname</b> isn't a valid planet in <b>{$game->galaxy}</b>, sorry.");			
			}
			
			$money = $game->commanders[$email]['money'];
			$cost = $game->cost[$buildingname];
			
			if($money >= $cost){
				if($game->planets[$planetname]['buildings'][$slot]['type'] == 'empty'){
					$game->planets[$planetname]['buildings'][$slot]['type'] = "future_$buildingname";
					$game->commanders[$email]['money'] = $money - $cost;
				}
				$result = Model::save($game, $link);
			} else {
				$_SESSION['error'] = "Sorry, you don't have enough money to build a $buildingname.  It costs {$game->cost[$buildingname]}, but you only have {$game->commanders[$email]['money']}.";	
				Model::rollback($link);
			}
		}
	
		///
		/// @brief cancels a building
		///
		/// @param $email 			the player destroying the building
		/// @param $buildingname 	the type of building to be built
		/// @param $planetname 		the name of the planet to build on
		/// @param $gid 			the game identifier
		/// @param $slot 			index of where to build the building
		static function cancel_structure($email, $buildingname, $planetname, $gid, $slot){
			list($game, $link) = Model::load(Controller::sanitize_int($gid));
			
			//validate input
			if($game == null){	
				Model::rollback($link);
				Controller::error(711, "It turns out <b>$gid</b> isn't a valid game identifier, sorry.");
			}
			if(!array_key_exists($email, $game->commanders)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$email</b> isn't commanding in <b>{$game->galaxy}</b>, sorry.");
			}
			if(!array_key_exists($planetname, $game->planets)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$planetname</b> isn't a valid planet in <b>{$game->galaxy}</b>, sorry.");			
			}
			
			$money = $game->commanders[$email]['money'];
			if(preg_match('/^future_/', $buildingname)){
				$originalbuildingname = preg_replace('/^future_/', '', $buildingname);
				$cost = $game->cost[$originalbuildingname];
			} else {
				Model::rollback($link);
				Controller::error(711, "You can't cancel a <b>$buildingame</b>, sorry.");		
			}

			if($game->planets[$planetname]['buildings'][$slot]['type'] == $buildingname){
				$game->planets[$planetname]['buildings'][$slot]['type'] = "empty";
				$game->commanders[$email]['money'] = $money + $cost;
			}
			
			$result = Model::save($game, $link);
			return $result;
		}		
				
	
		///
		/// @brief queues up a ship
		///
		/// @param $email 			the player paying for the building
		/// @param $buildingname 	the type of building to be built
		/// @param $planetname 		the name of the planet to build on
		/// @param $gid 			the game identifier
		/// @param $slot 			index of where to build the building
		static function build_ship($email, $planetname, $gid, $slot){
			list($game, $link) = Model::load(Controller::sanitize_int($gid));
			
			//validate input
			if($game == null){	
				Model::rollback($link);
				Controller::error(711, "It turns out <b>$gid</b> isn't a valid game identifier, sorry.");
			}
			if(!array_key_exists($email, $game->commanders)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$email</b> isn't commanding in <b>{$game->galaxy}</b>, sorry.");
			}
			if(!array_key_exists($planetname, $game->planets)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$planetname</b> isn't a valid planet in <b>{$game->galaxy}</b>, sorry.");			
			}
			
			$money = $game->commanders[$email]['money'];
			$cost = $game->cost['ship'];
			$built = false;
			
			if($money >= $cost){
				if(isset($slot) and $slot > 0){
					if($game->planets[$planetname]['buildings'][$slot]['type'] == 'factory'){
						$game->planets[$planetname]['buildings'][$slot]['type'] = "pregnant_factory";
						$game->commanders[$email]['money'] = $money - $cost;
						$built = true;
					}
				} else {
					//non-slotted version for the build all ships macro
					for($i = 0; $i < count($game->planets[$planetname]['buildings']); $i++){
						if($game->planets[$planetname]['buildings'][$i]['type'] == 'factory'){
							$game->planets[$planetname]['buildings'][$i]['type'] = "pregnant_factory";
							$game->commanders[$email]['money'] = $money - $cost;
							$built = true;
							break;
						}
					}
				}
				if($built){
					$result = Model::save($game, $link);
				} else {
					$_SESSION['error'] = "Sorry, there doesn't seem to be an open factory on <b>$planetname</b>.";
					Model::rollback($link);
					$result = false;					
				}
			} else {
				$_SESSION['error'] = "Sorry, you don't have enough money to build a ship.  It costs {$game->cost['ship']}, but you only have {$game->commanders[$email]['money']}.";
				Model::rollback($link);
				$result = false;
			}		
			return $result;
		}
		
		//$email wants to cancel building a $buildingname on $planetname in the galaxy identified by $gid
		//$buildingname is expected to be 'pregnant_factory' for now
		static function cancel_ship($email, $buildingname, $planetname, $gid, $slot){
			list($game, $link) = Model::load(Controller::sanitize_int($gid));
			
			//validate input
			if($game == null){	
				Model::rollback($link);
				Controller::error(711, "It turns out <b>$gid</b> isn't a valid game identifier, sorry.");
			}
			if(!array_key_exists($email, $game->commanders)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$email</b> isn't commanding in <b>{$game->galaxy}</b>, sorry.");
			}
			if(!array_key_exists($planetname, $game->planets)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$planetname</b> isn't a valid planet in <b>{$game->galaxy}</b>, sorry.");			
			}
			
			$money = $game->commanders[$email]['money'];
			if(preg_match('/^pregnant_/', $buildingname)){
				$originalbuildingname = preg_replace('/^pregnant_/', '', $buildingname);
				$cost = $game->cost['ship'];
			} else {
				Model::rollback($link);
				Controller::error(711, "You can't cancel a <b>$buildingame</b>, sorry.");		
			}

			if($game->planets[$planetname]['buildings'][$slot]['type'] == $buildingname){
				$game->planets[$planetname]['buildings'][$slot]['type'] = "factory";
				$game->commanders[$email]['money'] = $money + $cost;
			} else {
				Model::rollback($link);
				Controller::error(711, "There isn't ship to cancel on <b>$planetname</b> at slot <b>$slot</b>.");			
			}
			
			$result = Model::save($game, $link);
			return $result;
		}
	
		//dispatches some ships, creating a fleet
		function dispatch($email, $number, $sourcename, $destname, $gid){	
			list($game, $link) = Model::load(Controller::sanitize_int($gid));
			
			//validate input
			$number = (int)Controller::sanitize_int($number);
			if($game == null){	
				Model::rollback($link);
				Controller::error(711, "It turns out <b>$gid</b> isn't a valid game identifier, sorry.");
			}
			if(!array_key_exists($email, $game->commanders)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$email</b> isn't commanding in <b>{$game->galaxy}</b>, sorry.");
			}
			if(!array_key_exists($sourcename, $game->planets)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$sourcename</b> isn't a valid planet in <b>{$game->galaxy}</b>, sorry.");			
			}
			if(!array_key_exists($destname, $game->planets)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$destname</b> isn't a valid planet in <b>{$game->galaxy}</b>, sorry.");			
			}
			if($number > $game->planets[$sourcename]['ships']){
				Model::rollback($link);
				Controller::error(711, "You can't send <b>{$number}</b> ships from a planet that only has <b>{$game->planets[$sourcename]['ships']}</b>, sorry.");				
			}
			
			$dist = $game->distance($sourcename, $destname);
			if($dist > $game->get_total_range($email)){
				Model::rollback($link);
				Controller::error(711, "Your ships can't travel <b>$dist</b> zaphods yet, sorry.");				
			}			
			
			$distperday = $game->get_day_range($email);
			$time = ceil($dist/$distperday);
			
			$game->commanders[$email]['fleets'][] = array(
					'source' 		=> $sourcename,
					'destination' 	=> $destname,
					'size'			=> $number,
					'eta'			=> $game->day + $time,
					'etd'			=> $game->day
				);
				
			$game->planets[$sourcename]['ships'] -= $number;
			
			$result = Model::save($game, $link);
			return $result;			
		}
		
		//recalls a fleet, grounding it immediately
		function recall($email, $gid, $fleetid){	
			list($game, $link) = Model::load(Controller::sanitize_int($gid));
			
			//validate input
			if($game == null){	
				Model::rollback($link);
				Controller::error(711, "It turns out <b>$gid</b> isn't a valid game identifier, sorry.");
			}
			if(!array_key_exists($email, $game->commanders)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$email</b> isn't commanding in <b>{$game->galaxy}</b>, sorry.");
			}
			if(!isset($game->commanders[$email]['fleets'][$fleetid])){
				Model::rollback($link);
				Controller::error(711, "You don't have a fleet number <b>$fleetid</b>, sorry.");
			}
			$fleet = $game->commanders[$email]['fleets'][$fleetid];
			if($fleet['etd'] != $game->day){
				Model::rollback($link);
				Controller::error(711, "Fleet <b>$fleetid</b> has already left, you cannot recall it, sorry.");
			}
			
			//remove fleet from fleet list
			array_splice($game->commanders[$email]['fleets'], $fleetid, 1);
			
			//add ships back to the planet
			$game->planets[$fleet['source']]['ships'] += $fleet['size'];			
			
			$result = Model::save($game, $link);
			return ($result) ? $fleet : null;			
		}
		
		//recycle the structure denoted by $slot on $planet in galaxy $gid
		function recycle_structure($email, $gid, $planet, $slot){	
			list($game, $link) = Model::load(Controller::sanitize_int($gid));
			
			//validate input
			if($game == null){	
				Model::rollback($link);
				Controller::error(711, "It turns out <b>$gid</b> isn't a valid game identifier, sorry.");
			}
			if(!array_key_exists($email, $game->commanders)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$email</b> isn't commanding in <b>{$game->galaxy}</b>, sorry.");
			}
			if(!array_key_exists($planet, $game->planets)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$sourcename</b> isn't a valid planet in <b>{$game->galaxy}</b>, sorry.");			
			}
			
			$type = $game->planets[$planet]['buildings'][$slot]['type'];
			if($type != 'empty'
				and $email = $game->planets[$planet]['owner']){
				$game->planets[$planet]['buildings'][$slot]['type'] = "recycling_$type";
			} else {
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$planet</b> doesn't have a structure at slot <b>$slot</b>.");
			}
			
			$result = Model::save($game, $link);
			return $result;			
		}
		
		//cancel the recycling job
		function cancel_recycle($email, $gid, $planet, $slot){	
			list($game, $link) = Model::load(Controller::sanitize_int($gid));
			
			//validate input
			if($game == null){	
				Model::rollback($link);
				Controller::error(711, "It turns out <b>$gid</b> isn't a valid game identifier, sorry.");
			}
			if(!array_key_exists($email, $game->commanders)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$email</b> isn't commanding in <b>{$game->galaxy}</b>, sorry.");
			}
			if(!array_key_exists($planet, $game->planets)){
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$sourcename</b> isn't a valid planet in <b>{$game->galaxy}</b>, sorry.");			
			}
			
			$type = $game->planets[$planet]['buildings'][$slot]['type'];
			if(preg_match('/^recycling_/', $type)
				and $email = $game->planets[$planet]['owner']){
				$original_type = substr($type, 10);
				$game->planets[$planet]['buildings'][$slot]['type'] = $original_type;
			} else {
				Model::rollback($link);
				Controller::error(711, "It looks like <b>$planet</b> doesn't have a recycling job at slot <b>$slot</b>.");			
			}
			
			$result = Model::save($game, $link);
			return $result;			
		}

	
		//updates a game with a specific character's data
		static function update_game($email, $commander, $color, $gid){
			$gid 		= (int)Controller::sanitize_int($gid);
			$name   	= substr(Controller::sanitize($commander), 0, 64);
			
			list($game, $link) = Model::load($gid);
			if($game->status != 'setup'){
				Model::rollback($link);
				Controller::error(711, "<b>{$game->galaxy}</b> is already setup, sorry.");
			}
			//check if color is taken, and if $email is already $color
			if(in_array($color, $game->get_colors()) && $game->commanders[$email]['color'] != $color){
				Model::rollback($link);
				Controller::error(711, "Sorry, someone else is playing as <b>$color</b>.");
			}
			if(strlen($name) < 2 or $name == 'TBA'){
				Model::rollback($link);
				Controller::error(711, "Sorry, \"<b>$name</b>\" is not a valid name.");
			}
			
			$game->commanders[$email]['name']  = $name;
			$game->commanders[$email]['color'] = $color;
		
			//update the game status
			$game->check_status();		
			
			//save it back
			$result = Model::save($game, $link);
			
			//this player is now committed
			Action::commit($email, $gid);
		}
	
		//registers the user
		//returns on success or uses controller's error on failure
		static function register($code, $email, $pass1, $pass2){
			if($pass1 != $pass2){
				Controller::error(703, "I'm sorry, your passwords don't match. [back to <a href = \"registrationPage.php\">registration</a>]");
			}
			if(Model::use_code($code)){
				Model::add_user($email, $pass1);
			} else {
				Controller::error(704, "I'm sorry, '$code' is not a valid code. [back to <a href = \"registrationPage.php\">registration</a>]");
			}
		}
		
		//email wants to queue up all the ships he can in game $gid
		static function queue_all($email, $gid){
			$game = Model::get_game($gid);
			
			$queued = 0;
			
			foreach($game->planets_owned_by($email) as $name => $planet){
				while(Action::build_ship($email, $name, $gid, -1)){
					$queued++;
				}
			}
			
			unset($_SESSION['error']);
			return $queued;
		}
		
		//email wants to queue up all the ships he can on $planetname
		static function queue_planet($email, $gid, $planetname){
			$game = Model::get_game($gid);
			
			$queued = 0;
			
			while(Action::build_ship($email, $planetname, $gid, -1)){
				$queued++;
			}
			
			unset($_SESSION['error']);
			return $queued;
		}
		
		//adds a code to the database
		static function add_code($code){
			if(!Model::add_code($code)){
				Controller::error(707, "Code Adding Failed!");
			}
		}	
		
		//deletes a game
		static function delete_galaxy($id){
			if(!Model::delete_game($id)){
				Controller::error(710, "Deleting Galaxy <b>$id</b> Failed!");
			}
		}	
		
		//kicks a player out of a game
		static function kick_player($id, $kick_email){	
		
			//
			// remove player from game
			//
			list($game, $link) = Model::load($id);
			
			//make a temp array with all commanders except the kicked one
			$temp = array();
			foreach($game->commanders as $email => $commander){
				if($email != $kick_email){
					$temp[$email] = $commander;
				}
			}
			$game->commanders = $temp;
			
			//remove kicked player from all planets
			foreach(array_keys($game->planets) as $planetname){
				if($game->planets[$planetname]['owner'] == $kick_email){
					$game->planets[$planetname]['owner'] = '';
					$game->planets[$planetname]['ships'] = 0;					
				}
			}
			
			$result = Model::save($game, $link);
			
			// remove plays relationship
			Model::kick_player($id, $kick_email);
			
			
			return $result;			
		}	
		
		static function kill_phantoms($gid){
			$success = false;
			$gid 		= (int)Controller::sanitize_int($gid);
			list($game, $link) = Model::load($gid);
			
			//validate input
			if($game == null){	
				Model::rollback($link);
				Controller::error(711, "It turns out <b>$gid</b> isn't a valid game identifier, sorry.");
			}
			
			$healthy = array();
			foreach($game->commanders as $email => $commander){
				if($email != ''){
					$healthy[$email] = $commander;
				}
			}
			if(count($healthy) < count($game->commanders)){
				$success = true;
			}
			$game->commanders = $healthy;
			
			//the phantom may have been holding up a turn, check to run it now
			$game->committed = array_unique($game->committed);
			if(count($game->committed) == count($game->commanders) - count($game->conquered)
				&& $game->status != 'finished'){
				$game->execute();
			}
			
			Model::save($game, $link);
			
			return $success;		
		}
	}
	
?>
