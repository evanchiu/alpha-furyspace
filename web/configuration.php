<?php
	//configuration.php
	//holds configuration data
	//this is the mamp configuraiton settings

	class Configuration{
		static private $local = array(
									'server'	=> 'localhost',
									'username'	=> 'root',
									'password'	=> 'root',
									'database'	=> 'furyspace'
									);
		static private $live = array(
									'server'	=> '',
									'username'	=> '',
									'password'	=> '',
									'database'	=> ''
									);

		static function db_server(){
			if(Controller::is_live()){
				$server = self::$live['server'];
			} else {
				$server = self::$local['server'];
			}
			return $server;
		}

		static function db_username(){
			if(Controller::is_live()){
				$username = self::$live['username'];
			} else {
				$username = self::$local['username'];
			}
			return $username;
		}

		static function db_password(){
			if(Controller::is_live()){
				$password = self::$live['password'];
			} else {
				$password = self::$local['password'];
			}
			return $password;
		}

		static function db_name(){
			if(Controller::is_live()){
				$database = self::$live['database'];
			} else {
				$database = self::$local['database'];
			}
			return $database;
		}

		//some salt to protect the password before it gets MD5'd
		static function presalt(){
			return 'c29tZSBqddW';
		}

		//some salt to protect the password before it gets MD5'd
		static function postsalt(){
			return 'WtuIGZhb3Jh';
		}

		//returns true if this email is an administrator
		static function is_administrator($email){
			$admins = array('commander@furyspace.com'
				// Add your email here to be an administrator
			);
			return in_array($email, $admins);
		}

		//return the reply to address for notification emails
		static function get_reply(){
			return 'commander@furyspace.com';
		}

		//deal with colors
		static private $colors = array(
									'red'		=> 'dd1111',
									'orange'	=> 'ff9911',
									'mocha'		=> 'cc9900',
									'yellow'	=> 'dddd11',
									'lime'		=> '99ff33',
									'green'		=> '11cc11',
									'forest'    => '119911',
									'cyan'		=> '33cccc',
									'blue'		=> '6699ff',
									'indigo'	=> '6666ff',
									'violet'	=> '9933ff',
									'magenta'	=> 'cc33cc',
									'pink'		=> 'ff99cc'
									);
		static function colors(){
			return self::$colors;
		}
		static function color_names(){
			return array_keys(self::$colors);
		}
	}
?>
