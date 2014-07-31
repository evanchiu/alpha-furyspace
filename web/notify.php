<?php
	//notify.php
	//sends out notifications
	
	class Notify{		
	
		//sends out an email about a turn update
		static function turn_update($email, $galaxyname, $galaxyid, $day, $name = ''){
			$to = $email;
			$subject = "New day in $galaxyname";
			$header = "From: Furyspace Notification <notifications@alpha.furyspace.com>\r\n"
				. 'Reply-To: ' . Configuration::get_reply() . "\r\n";
			$message = "Good Morning, $name!\n"
				. "It is now day $day in $galaxyname.\n"
				. "http://alpha.furyspace.com/mapPage.php?g=$galaxyid\n\n"
				. "Thanks for playing!\n";
			$success = mail($to, $subject, $message, $header);
			return $success;
		}
		
		//sends out an email about a new game
		static function new_game($email, $galaxyname, $galaxyid){
			$to = $email;
			$subject = "$galaxyname, the newest furyspace";
			$header = "From: Furyspace Notification <notifications@alpha.furyspace.com>\r\n"
				. 'Reply-To: ' . Configuration::get_reply() . "\r\n";
			$message = "Hello!\n"
				. "You've been invited to command fleets in a brand new furyspace, $galaxyname!\n"
				. "http://alpha.furyspace.com/setupPage.php?g=$galaxyid\n\n"
				. "Thanks for playing!\n";
			$success = mail($to, $subject, $message, $header);		
			return $success;
		}
		
		//sends out notification that player got eliminated
		static function elimination($email, $galaxyname, $galaxyid, $name = '', $killername = ''){
			$to = $email;
			$subject = "Destruction in $galaxyname";
			$header = "From: Furyspace Notification <notifications@alpha.furyspace.com>\r\n"
				. 'Reply-To: ' . Configuration::get_reply() . "\r\n";
			$message = "My Regrets, $name,\n"
				. "$killername invaded your home planet in $galaxyname.\n"
				. "http://alpha.furyspace.com/mapPage.php?g=$galaxyid\n\n"
				. "Thanks for playing!\n";
			$success = mail($to, $subject, $message, $header);
			return $success;
		}
		
		//sends out notification that player is victorious
		static function victory($email, $galaxyname, $galaxyid, $name = ''){
			$to = $email;
			$subject = "Victory in $galaxyname";
			$header = "From: Furyspace Notification <notifications@alpha.furyspace.com>\r\n"
				. 'Reply-To: ' . Configuration::get_reply() . "\r\n";
			$message = "Congratulations, $name!\n"
				. "You have conquered $galaxyname.\n"
				. "http://alpha.furyspace.com/mapPage.php?g=$galaxyid\n\n"
				. "Thanks for playing!\n";
			$success = mail($to, $subject, $message, $header);
			return $success;
		}
		
		//notifies $email that $victor has conquered $galaxyname
		static function gameover($email, $galaxyname, $galaxyid, $name, $victor){
			$to = $email;
			$subject = "$victor conquered $galaxyname";
			$header = "From: Furyspace Notification <notifications@alpha.furyspace.com>\r\n"
				. 'Reply-To: ' . Configuration::get_reply() . "\r\n";
			$message = "Hello, $name!\n"
				. "$victor conquered $galaxyname.\n"
				. "http://alpha.furyspace.com/mapPage.php?g=$galaxyid\n\n"
				. "Thanks for playing!\n";
			$success = mail($to, $subject, $message, $header);
			return $success;		
		}
	}
?>
