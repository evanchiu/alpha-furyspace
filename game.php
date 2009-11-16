<?php
	//game.php
	//the game class
	//represents game, with data, and has methods to manage it
	
	//
	//  About commanders
	//
	//  $this->commanders is an associative array
	//  the keys are the commanders' emails
	//  the values are also associative arrays holding data about the commander
	//  keys in this array are:
	//    name       -- commandername for this galaxy
	//    color      -- #rrggbb in hex notation
	//    email      -- possibly redundant storage here
	//    tech  	 -- number of research units the player has produced
	//	  title		 -- player title, defaults to Commander
	//    homeplanet -- name of the homeplanet for this commander
	//	  money      -- amount a money a player owns
	//	  reports	 -- an array of messages for the report. generated by execute()
	//	  last		 -- integer.  the last turnt this player saw, for checking if they need reports
	//	  fleets	 -- an unkeyed array with references to each fleet the commander has
	//	  visible    -- array of planet names that this commander can see
	//    stats		 -- associative array of statistics for this player for this turn
	
	//
	//	About Stats
	//  
	//	these stats are calculated at execute() time for each turn
	//		ships		-- total number of ships this player has
	//		planets		-- total number of planets owned by this player
	//		gpt			-- gold this player will earn over next turn
	
	//
	//  About planets
	//
	//  $this->plantes is an associative array
	//  the keys are the commanders' names
	//  the values are also associative arrays holding data about the planet
	//  keys in this array are:
	//    name       -- planet name for this galaxy
	//    owner      -- email of the commander who currently owns it
	//    buildings  -- an array of buildings, its length gives planet size
	//    class		 -- the class of its resources [1..5] (1 is best) 
	//					gold per day = 55 - class*5
	//    x			 -- its x coordinate in zaphods
	//    y			 -- its y coordinate in zaphods
	//    ships      -- the number of ships currently in orbit
	
	//
	//  About buildings
	//
	//	$planet->buildings is an associative array with the following keys
	//		type	-- empty, research, mine, or factory
	
	//
	//	About Status
	//	
	//	$this->status is a string describing the status of the game
	//		setup		-- indicates that game is being setup, not all players have names
	//		active		-- game is actively playing
	//		finished	-- someone has conquered the galaxy
	
	//
	//  About Costs
	//
	//  $this->cost is an associative array indicating how much money each item costs
	
	//
	//  About Fleets
	//
	//  Each commander has an array of fleets.  This is empty unless ships are flying
	//  a group of traveling ship(s) constitutes a fleet.  A fleet is represented as
	//  an associative array with the following keys:
	//  	source		-- planet they're flying from
	//      destination	-- planet they're flying to
	//		size		-- number of ships in the fleet
	//		eta			-- day number they will land on
	//		etd			-- day number they will leave on
	
	//
	// 	About Battles
	//
	//	A battle happens when a fleet lands on a planet owned by someone else
	//  The battle is fought with each ship fighting one enemy at a time
	//  A ship's power rating is 5 + tech_level
	//  A ship's chance of winning its power rating / sum of both power ratings
	
	//
	//  About Reports
	//	There are two report arrays, morning and evening,
	//  Each gets associative arrays pushed onto it
	//  These associative arrays have an index 'type' which tells you what else to look for
	//	
	//	'update' 		- no fields, means your title, name or home planet was updated, so print all info
	//  'build'			- has 'planetname', 'ships', 'facilities', 'factories', 'mines'
	//	'recycle'		- has 'planetname', 'refund', 'facilities', 'factories', 'mines'
	//  'levelup'		- technology level increase, no data necessary
	//  'fleet_land'	- ships landed, has 'fromplanet', 'toplanet', 'quantity'
	//	'claim'			- claimed an empty planet, has 'planet' 'remain'
	//	'conquer'		- conquere a planet has 'owneremail', 'ownerships', 'initialships', 'remain', 'planetname'
	//  'fail'			- fail to conquer a planet has 'owneremail', 'ownerinitial', 'remain', 'yourships', and 'planetname'
	//  'lose'			- lose a planet 'invadeemail', 'invadeinitial', 'remain', 'yourinitial', 'planetname'
	//  'rebuff'		- defeat invader 'invadeemail', 'invadeinitial', 'remain', 'yourinitial', 'planetname'
	//	'wipeout'		- you got wiped out, no data necessary
	//	'tko'			- you wiped 'loseremail' out
	//	'dominate'		- you have dominated the galaxy
		
	class Game{
		public $galaxy 		= 'Milky Way';
		public $id     		= 0;
		public $size		= 0;
		public $commanders	= array();
		public $planets		= array();
		public $width		= 0;
		public $height		= 0;
		public $status		= 'setup';
		public $cost		= array();
		public $day			= 0;
		public $committed   = array();
		public $conquered	= array();
		public $techreqs	= array();
		public $winner      = '';		//email of the winner
		public $version		= 0.1;
		public $daystart	= 0;		//unix time this day started
		public $baserange	= 6;		//how far a ship with no tech can fly
		
		function __construct($id, $galaxy, $size, $commanderEmails){
			$this->galaxy 	= $galaxy;
			$this->id     	= $id;
			$this->size		= $size;
			
			$this->day = 0;
						
			//set up the pricing
			$this->cost = array(
				'facility'		=> 200,
				'factory'		=> 200,
				'mine'			=> 200,
				'ship'			=> 50,
				'startup'		=> 2000
				);
			
			//set up commanders
			for($i = 0; $i < count($commanderEmails); $i++){
				$email = $commanderEmails[$i];
				$this->commanders[$email] = array(
					'email' 		=> $email,
					'color' 		=> 'black',
					'name'  		=> 'TBA',
					'tech'		    => 0,
					'title'			=> 'Commander',
					'money'			=> $this->cost['startup'],
					'last'			=> 0,
					'fleets' 		=> array(),
					'visible' 		=> array(),
					'morning'		=> array(array('type' => 'update')),
					'evening'		=> array(array('type' => 'creation'))
					);
			}
			
			$this->create_galaxy();
			
			foreach($commanderEmails as $email){
				if(!Notify::new_game($email, $galaxy, $id)){
					$_SESSION['error'] = "Failed to send mail to $email.";
				}
			}
		}
		
		//given the email, return the commander name
		function getCommander($email){
			return $this->commanders[$email]['name'];
		}
		
		//get the tech level for the commander referenced by this email
		function get_tech_level($email){
			$tech = $this->commanders[$email]['tech'];
			return floor(sqrt($tech));
		}
		
		//how far can $email's ships go in a day?
		//ask this function
		function get_day_range($email){
			return ceil($this->get_total_range($email) / 3.0);
		}
		
		//how far can $email's ships go total distance?
		//ask this function
		function get_total_range($email){
			return $this->baserange + $this->get_tech_level($email);
		}
		
		//how much gold will a mine on this planet produce?
		function get_gold_production($planetname){
			return 55 - $this->planets[$planetname]['class'] * 5;
		}
		
		//returns an array full of all the statistics about this player
		function get_stats($email){
			if(isset($this->commanders[$email]['stats'])){
				$stats = $this->commanders[$email]['stats'];
				$stats['gold'] = $this->commanders[$email]['money'];
				$stats['tech_level'] = $this->get_tech_level($email);
				return $stats;
			} else {
				return null;
			}
		}
		
		//checks out the player and gives an int indicating committment
		// 0 - player cannot commit, either conquered or game is over
		// 1 - player can commit
		// 2 - player is committed
		function get_commit($email){
			$commit = 0;
			if($this->status == 'active' and !in_array($email, $this->conquered)){
				if(in_array($email, $this->committed)){
					$commit = 2;
				} else {
					$commit = 1;
				}
			} 
			return $commit;
		}
		
		//can this player see the colors(owners) of other planets
		//
		//color is visible if planet is owned and 
		//this is my planet, or I'm dead, or I'm over level four
		function is_color_visible($email, $planet){
			return ($this->planets[$planet]['owner'] 
					&& (
						$this->planets[$planet]['owner'] == $email
			   			|| in_array($email, $this->conquered)
			   			|| $this->get_tech_level($email) >= 4
			   		   )
			   	   );
		}
		
		//can player $email see the architecture on $planet?
		//call this function to find out!
		//for now, only the owner can
		function is_architecture_visible($email, $planet){
			return ($this->planets[$planet]['owner'] == $email);
		}
		
		/**
		 * function is_architecture_controllable
		 * 		Checks if $email can control $planet's architecture
		 *
		 * @return boolean - whether $email should have controls for $planet's architecture
		 **/
		function is_architecture_controllable($email, $planet)
		{
			return ($this->planets[$planet]['owner'] == $email);
		}
		
		//creates an initial galaxy
		function create_galaxy(){
			$numplanets = $this->size * count($this->commanders);
			//so we're going with a goal density of 1 planet per 15 square zaphods
			$this->width = $this->height = floor(sqrt($numplanets*15));
			
			$barrier = min($this->size, $this->baserange) + 1;	//minimum distance between homeplanets
			$locations = array();		//locations where planets already exist
			$barredlocations = array();		//locations within the barrier range of home planets
			
			//set up home planets
			for($i = 0; $i < count($this->commanders); $i++){
				$planet = array();
				$planet['name'] 	= Controller::invent_name();
				//homeplanet will be either 5 slots of 2 class
				// or 6 slots of 3 class
				$size			 	= mt_rand(5, 6);
				$planet['class'] 	= $size - 3;
				
				//find a unique location
				$x = mt_rand(1, $this->width - 1);
				$y = mt_rand(1, $this->height - 1);
				while(in_array("$x $y", $barredlocations)){
					$x = mt_rand(1, $this->width - 1);
					$y = mt_rand(1, $this->height - 1);					
				}
				$planet['x'] 		= $x;
				$planet['y'] 		= $y;
				$locations[] = "$x $y";
				for($x1 = 1; $x1 < $this->width; $x1++){
					for($y1 = 1; $y1 < $this->height; $y1++){
						if(Game::mathdistance($x, $y, $x1, $y1) <= $barrier){
							$barredlocations[] = "$x1 $y1";
						}
					}
				}
				
				$emails = array_keys($this->commanders);
				$planet['owner'] 	= $emails[$i];
				$planet['ships']	= 0;
				$planet['buildings']= array();
				
				//install empty buildings
				for($b = 0; $b < $size; $b++){
					$planet['buildings'][] = array('type' => 'empty');
				}
				
				//set up this commander's home planet
				$this->commanders[$emails[$i]]['homeplanet'] = $planet['name'];
				
				//add planet to the galaxy
				$this->planets[$planet['name']] = $planet;				
			}
			
			/*
			//dump the barreddetails to a log
			$log = fopen("barred.log", "w");
			for($x1 = 1; $x1 < $this->width; $x1++){
				$line = '';
				for($y1 = 1; $y1 < $this->height; $y1++){
					if(in_array("$x1 $y1", $locations)){
						$char = "O";
					} else if(in_array("$x1 $y1", $barredlocations)){
						$char = "-";
					} else {
						$char = " ";
					}
					$line .= $char;
				}
				fwrite($log, "$line\n");
			}
			fwrite($log, "barredlocations:\n");
			fwrite($log, implode('), (', $barredlocations) . "\n");
			fwrite($log, "home planets:\n");
			fwrite($log, implode('), (', $locations) . "\n");
			fclose($log);
			*/
			
			//set up the rest of the planets
			for(; $i < $numplanets; $i++){
				$planet = array();
				$planet['name'] 	= Controller::invent_name();
				$size 				= mt_rand(3, 12);
				$planet['class'] 	= mt_rand(1, 5);
				
				//find a unique location
				$x = mt_rand(1, $this->width - 1);
				$y = mt_rand(1, $this->height - 1);
				while(in_array("$x $y", $locations)){
					$x = mt_rand(1, $this->width - 1);
					$y = mt_rand(1, $this->height - 1);			
				}
				$planet['x'] 		= $x;
				$planet['y'] 		= $y;
				$locations[] = "$x $y";
				
				$planet['owner']	= '';
				$planet['ships']	= 0;
				$planet['buildings']= array();
				
				//install empty buildings
				for($b = 0; $b < $size; $b++){
					$planet['buildings'][] = array('type' => 'empty');
				}
				
				//add planet to the galaxy
				$this->planets[$planet['name']] = $planet;		
			}
			
			//sort planets by name
			ksort($this->planets);
		}
		
		//returns an array of all planets owned by $commander
		function planets_owned_by($commander){
			$hisPlanets = array();
			foreach($this->planets as $name => $planet){
				if($planet['owner'] == $commander){
					$hisPlanets[$name] = $planet;
				}
			}
			return $hisPlanets;
		}
		
		//returns the distance, in zaphods between two planets
		function distance($planetname1, $planetname2){
			return Game::mathdistance($this->planets[$planetname1]['x'],
									  $this->planets[$planetname1]['y'],
									  $this->planets[$planetname2]['x'],
									  $this->planets[$planetname2]['y']);
		}
		
		static function mathdistance($x1, $y1, $x2, $y2){
			return sqrt(($x1 - $x2)*($x1 - $x2) + ($y1 - $y2)*($y1 - $y2));
		}
		
		//make everything happen for the turn, this should be run on the server
		//with unlimited time after all players have issued their orders
		//NOTE: Remember that to save anything, you must modify the $this variable
		//NOTE: No Calls to Model may be made here.
		function execute(){
			//no one is committed anymore
			$this->committed = array();
			
			$this->day++;
			$this->daystart = time();
			
			//clear out reports, if we're beyond the inital setup day
			if($this->day > 1){
				foreach($this->commanders as $email => $commander){
					$this->commanders[$email]['reports'] = array();
					$this->commanders[$email]['morning'] = array();
					$this->commanders[$email]['evening'] = array();
				}
			}
		
			//
			// Building, mining, research happens first
			//
			//checkout each planet
			foreach(array_keys($this->planets) as $planetname){				
				$planet	= $this->planets[$planetname];
				$email 	= $this->planets[$planetname]['owner'];
				
				//only check buildings if the planet is occupied - NO MORE PHANTOMS
				if($email != ''){			
					//check out each building
					$build = array(
						'ships' 		=> 0,
						'facilities' 	=> 0,
						'factories' 	=> 0,
						'mines'			=> 0);
					$recycle = array(
						'refund'		=> 0,
						'facilities' 	=> 0,
						'factories' 	=> 0,
						'mines'			=> 0);
					for($i = 0; $i < count($this->planets[$planetname]['buildings']); $i++){
						$buildingtype = $planet['buildings'][$i]['type'];
						
						//recycle buildings
						if(preg_match('/^recycling_/', $buildingtype)){
							$original_type = substr($buildingtype, 10);
							$this->planets[$planetname]['buildings'][$i]['type'] = 'empty';
							$gold = (int)floor($this->cost[$original_type] / 4);
							$this->commanders[$email]['money'] += $gold;
							$recycle[Controller::pluralize($original_type)]++;
							$recycle['refund'] += $gold;
						}
						
						//do research
						if($buildingtype == 'facility'){
							$this->commanders[$email]['tech']++;
							if(in_array($this->commanders[$email]['tech'], $this->techreqs)){
								$techlevel = $this->get_tech_level($email);
								$this->commanders[$email]['evening'][] = array('type' => 'levelup');
							}
						}
						
						//mine gold
						if($buildingtype == 'mine'){
							$gold = $this->get_gold_production($planetname);
							$this->commanders[$email]['money'] += $gold;
						}
						
						//build ships
						if($buildingtype == 'pregnant_factory'){
							//add ship, unpregnate the factory
							$this->planets[$planetname]['ships']++;
							$this->planets[$planetname]['buildings'][$i]['type'] = 'factory';
							$build['ships']++;
						}
						
						//build buildings
						if(preg_match('/^future_/', $buildingtype)){
							$buildingfinalname = substr($buildingtype, 7);
							$this->planets[$planetname]['buildings'][$i]['type'] = $buildingfinalname;
							$build[Controller::pluralize($buildingfinalname)]++;
						}
					}
					if($build['ships']			> 0
						|| $build['facilities']	> 0
						|| $build['factories'] 	> 0
						|| $build['mines']		> 0) {
							$build['planetname'] = $planetname;
							$build['type']		 = 'build';
							$this->commanders[$email]['evening'][] = $build;	
					}
					if($recycle['refund']		> 0) {
							$recycle['planetname'] 	= $planetname;
							$recycle['type']		= 'recycle';
							$this->commanders[$email]['evening'][] = $recycle;	
					}
				}
			}
			
			//
			//  Fleet Movement happens second
			//
			//look through each commander in random order
			$emails = array_keys($this->commanders);
			shuffle($emails);
			foreach($emails as $email){
				//cut vision because we'll restore this later
				$this->commanders[$email]['visible'] = array();
			
				//look through each fleet
				// we make a copy, unshift each off, then shift it onto a temp array if it doesn't land today
				// then assign the commander's fleet list to the temp array
				$fleets = $this->commanders[$email]['fleets'];
				$temp   = array();
				while(count($fleets) > 0){
					$fleet = array_shift($fleets);
					if($fleet['eta'] != $this->day){
						$temp[] = $fleet;
					} else {
						//fleet lands today
						$planetname = $fleet['destination'];
						//check if owned
						if($this->planets[$planetname]['owner'] == ''){
							//not owned!  take it over
							$this->planets[$planetname]['owner'] = $email;
							$this->planets[$planetname]['ships'] = $fleet['size'];
							$this->commanders[$email]['morning'][] = array(
								'type'		 	=> 'claim',
								'planetname' 	=> $planetname,
								'remain'		=> $fleet['size']);
								
						} else if($this->planets[$planetname]['owner'] == $email) {
							//this is just a transfer
							$this->planets[$planetname]['ships'] += $fleet['size'];
							$s = ($fleet['size'] == 1) ? '' : 's';
							$this->commanders[$email]['morning'][] = array(
								'type'			=> 'fleet_land',
								'fromplanet'	=> $fleet['source'],
								'toplanet'		=> $planetname,
								'quantity'		=> $fleet['size'],
								'remain'		=> $this->planets[$planetname]['ships']);
							
						} else {
							//someone else owns it! battle!
							$owner = $this->planets[$planetname]['owner'];
							$ebonus = 0;
							$obonus = 0;
							if($planetname == $this->commanders[$owner]['homeplanet']){
								$obonus = 10;
							}
							$epower = 5 + $this->get_tech_level($email) + $ebonus;
							$opower = 5 + $this->get_tech_level($owner) + $obonus;
							$total = $epower + $opower;
							$eships = $fleet['size'];
							$oships = $this->planets[$planetname]['ships'];
							
							$log = "$planetname\n$email $epower $eships\n$owner $opower $oships\n$total\n";
							while($eships > 0 && $oships > 0){
								$force = mt_rand(1, $total);
								$log .= "$force ";
								if($force > $epower){
									$eships--;
								} else {
									$oships--;	
								}
							}
							$log .= "\n";
							
							//save the log
							Model::save_log($log);
							
							$owner_name_title = $this->commanders[$owner]['title'] . ' ' . $this->commanders[$owner]['name'];
							$invader_name_title = $this->commanders[$email]['title'] . ' ' . $this->commanders[$email]['name'];
							if($eships == 0){
								//fleet destroyed	
								$s1 = ($fleet['size'] == 1) ? '' : 's';
								$s2 = ($oships == 1) ? 's' : '';
								$s3 = ($this->planets[$planetname]['ships'] == 1) ? '' : 's';
								$this->commanders[$email]['morning'][] = array(
									'type' 			=> 'fail',
									'owneremail'	=> $owner,
									'ownerinitial'	=> $this->planets[$planetname]['ships'],
									'remain'		=> $oships,
									'yourships'		=> $fleet['size'],
									'planetname'	=> $planetname);
									
								$this->commanders[$owner]['morning'][] = array(
									'type'			=> 'rebuff',
									'invadeemail'	=> $email,
									'invadeinitial'	=> $fleet['size'],
									'yourinitial'	=> $this->planets[$planetname]['ships'],
									'remain'		=> $oships,
									'planetname'	=> $planetname);
									
								$this->planets[$planetname]['ships'] = $oships;
							}
							if($oships == 0){
								//planet takeover
								
								$s  = ($fleet['size'] == 1) ? '' : 's';
								$s2 = ($this->planets[$planetname]['ships'] == 1) ? '' : 's';
								$s3 = ($eships == 1) ? 's' : '';
								$this->commanders[$email]['morning'][] = array(
									'type' 			=> 'conquer',
									'owneremail'	=> $owner,
									'ownerships'	=> $this->planets[$planetname]['ships'],
									'remain'		=> $eships,
									'initialships'	=> $fleet['size'],
									'planetname'	=> $planetname);
								
								$this->commanders[$owner]['morning'][] = array(
									'type'			=> 'lose',
									'invadeemail'	=> $email,
									'invadeinitial'	=> $fleet['size'],
									'yourinitial'	=> $this->planets[$planetname]['ships'],
									'remain'		=> $eships,
									'planetname'	=> $planetname);	
									
								$this->planets[$planetname]['owner'] = $email;
								$this->planets[$planetname]['ships'] = $eships;
								
								if($planetname == $this->commanders[$owner]['homeplanet']){
									//$owner got taken out
									$this->conquered[] = $owner;
									//ingame notification
									$this->commanders[$owner]['morning'][] = array('type' => 'wipeout');
									//email notification
									Notify::elimination($owner, $this->galaxy, $this->id, $owner_name_title, $invader_name_title);
									
									//notify all other players?
																			
									//clear out other planets
									foreach(array_keys($this->planets) as $planetname){
										if($this->planets[$planetname]['owner'] == $owner){
											$this->planets[$planetname]['owner'] = '';
											$this->planets[$planetname]['ships'] = 0;											
										}
									}
									
									//destroy all remaining fleets, so s/he doesn't recolonize
									$this->commanders[$owner]['fleets'] = array();
										
									$this->commanders[$email]['morning'][] = array(
										'type' 			=> 'tko',
										'loseremail'	=> $owner);
									
									//check if game is done
									if(count($this->conquered) + 1 == count($this->commanders)){
										$this->commanders[$email]['morning'][] = array('type' => 'dominate');
										$this->commanders[$email]['title'] = 'Conqueror';
										$this->commanders[$email]['morning'][] = array('type' => 'update');
										$this->status = 'finished';
										$this->winner = $email;
										//notify victory by email
										Notify::victory($email, $this->galaxy, $this->id, $invader_name_title);
										
										foreach(array_keys($this->commanders) as $loseremail){
											if($loseremail != $email){
												$name = $this->commanders[$loseremail]['title'] . ' ' .
													$this->commanders[$loseremail]['name'];
												if(!Notify::gameover($loseremail, 
																	 $this->galaxy, 
																	 $this->id, 
																	 $name, 
																	 $invader_name_title)){
													$_SESSION['error'] = "Failed to send notifications.";
												}
											}
											
											//everyone can see everything
											$this->commanders[$loseremail]['visible'] = array_keys($this->planets);
										}	
										
										return;
									}
								} //ends if($planetname...
							} //ends ($oships... (planet takeover)
						} //ends else (battle)
					} //ends else (fleet lands today)
				} //ends while 
				$this->commanders[$email]['fleets'] = $temp;
			} //ends foreach
			
			//
			//	Calculate visibilities
			//
			//note which planets are visible to each commander
			//I'll just have to compare each planet to each other planet
			foreach($this->planets as $fromname => $fromplanet){
				if($fromplanet['owner']){
					$owner = $fromplanet['owner'];
					$this->commanders[$owner]['visible'][] = $fromname;				
					$sightrange = $this->get_total_range($owner);
					foreach($this->planets as $toname => $toplanet){
						if($this->distance($fromname, $toname) < $sightrange){
							$this->commanders[$owner]['visible'][] = $toname;
						}
					}
				}
			}
			//clean and sort visibility arrays
			foreach(array_keys($this->commanders) as $email){
				if(in_array($email, $this->conquered)){
					$this->commanders[$email]['visible'] = array_keys($this->planets);
				} else {
					$this->commanders[$email]['visible'] = array_unique($this->commanders[$email]['visible']);
				}
				sort($this->commanders[$email]['visible']);
			}
			
			//
			//	Calculate Statistics
			//
			//clear out old staistics
			foreach(array_keys($this->commanders) as $email){
				$this->commanders[$email]['stats'] = array(
					'ships' 	=> 0,
					'planets' 	=> 0,
					'gpt'		=> 0,
					'tpt'		=> 0
					);
			}
			//look through planets for gathering statistics
			foreach($this->planets as $planetname => $planet){
				$email = $planet['owner'];
				if($email){
					$this->commanders[$email]['stats']['planets']++;
					$this->commanders[$email]['stats']['ships'] += $planet['ships'];
					foreach($planet['buildings'] as $building){
						if($building['type'] == 'mine'){
							$this->commanders[$email]['stats']['gpt'] += $this->get_gold_production($planetname);
						}
						if($building['type'] == 'facility'){
							$this->commanders[$email]['stats']['tpt'] += 1;
						}
					}
				}
			}
			
			//add in ships from fleets and calculate distance to next tech level
			foreach($this->commanders as $email => $commander){
				foreach($commander['fleets'] as $fleet){
					$this->commanders[$email]['stats']['ships'] += $fleet['size'];
				}
				$nextlevel = $this->get_tech_level($email) + 1;
				$this->commanders[$email]['stats']['to_next'] = $nextlevel * $nextlevel - $this->commanders[$email]['tech'];
			}
						
			//
			//	Notify live commanders
			//
			foreach(array_keys($this->commanders) as $email){
				if(!in_array($email, $this->conquered)){
					$name = "{$this->commanders[$email]['title']} {$this->commanders[$email]['name']}";
					if(!Notify::turn_update($email, $this->galaxy, $this->id, $this->day, $name)){
						$_SESSION['error'] = "Failed to send notifications.";
					}
				}
			}			
		} //ends function
		
		//checks if status has changed
		function check_status(){
			if($this->status == 'setup'){
				//check if all players have names
				$setup = true;
				foreach($this->commanders as $commander){
					if($commander['name'] == 'TBA'){
						$setup = false;
					}
				}
				if($setup){
					$this->status = 'active';
				}			
			}
		}
		
		//returns an array of the names of the colors of the players
		function get_colors(){
			$colors = array();
			foreach ($this->commanders as $commander){
				$colors[] = $commander['color'];
			}
			return $colors;
		}
		
		//player $email has played this turn, update their last turn
		function update_last($email){
			list($game, $link) = Model::load($this->id);
			
			$game->commanders[$email]['last'] = $game->day;
			
			$result = Model::save($game, $link);
			return $result;
		}
		
		//returns the number of seconds this day has been running
		function day_age(){
			return (time() - $this->daystart);
		}
		
		//returns the number of seconds before this turn times out
		//once turn times out, a committed player can roll the turn
		//by checking in on the game
		function day_timeout(){
			//return timeout, if configured, otherwise, number of seconds in a week is default
			return (isset($this->day_timeout)) ? $this->day_timeout : 604800;
		}
	}
?>