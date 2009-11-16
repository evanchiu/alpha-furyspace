<?php
  //model.php
  //the model gets data from whatever data source I'm using, and may prepare it basically for the controller
  //Author: Evan G Chiu
  //Date: January 10, 2009

class Model{

  //get all the games that the user with email $email is playing in
  static function get_games($email){
		
    // Connecting, selecting database
    $link = mysql_connect(Configuration::db_server(), Configuration::db_username(), Configuration::db_password())
      or die('Could not connect: ' . mysql_error());
    mysql_select_db(Configuration::db_name()) or die('Could not select database');
		
    // Performing SQL query
    $query = "SELECT game FROM users NATURAL JOIN plays NATURAL JOIN games WHERE email = '$email' AND version = 'alpha';";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());	
		
    $games = array();
    while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $games[] = unserialize($line['game']);
    }	
		
    // Free resultset and close connection
    mysql_free_result($result);
    mysql_close($link);
		
    return $games;	
  }
	
  //get the game with given gameID
  static function get_game($gameID, $turn = 0){
		
    $game = null;
		
    // Connecting, selecting database
    $link = mysql_connect(Configuration::db_server(), Configuration::db_username(), Configuration::db_password())
      or die('Could not connect: ' . mysql_error());
    mysql_select_db(Configuration::db_name()) or die('Could not select database');
		
		
    // Performing SQL query
    $query = "SELECT game FROM games WHERE gid = $gameID;";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());	
		
    $rows = mysql_num_rows($result);
    if($rows === 1){			
      //get the data
      $line = mysql_fetch_array($result, MYSQL_ASSOC);
			
      //get the game out of it
      $cereal = $line['game'];
      $game = unserialize($cereal);
			
      if($game->status == 'finished'){
	$turn = Request::sanitize_int($turn);
	if($turn > 0){
	  $query = "SELECT game FROM history WHERE gid = $gameID AND turn = $turn;";
	  $result = mysql_query($query) or die('Query failed: ' . mysql_error());	
				
	  $rows = mysql_num_rows($result);
	  if($rows === 1){			
	    //get the data
	    $line = mysql_fetch_array($result, MYSQL_ASSOC);
						
	    //get the game out of it
	    $cereal = $line['game'];
	    $game = unserialize($cereal);
	  }
	}			
      }
    }
		
    // Free resultset and close connection
    mysql_free_result($result);
    mysql_close($link);
		
    return $game;	
  }
	
	
  //delete the game with given gameID
  static function delete_game($gameID){
		
    // Connecting, selecting database
    $link = mysql_connect(Configuration::db_server(), Configuration::db_username(), Configuration::db_password())
      or die('Could not connect: ' . mysql_error());
    mysql_select_db(Configuration::db_name()) or die('Could not select database');
		
    // Delete entries in the plays table
    $query = "DELETE FROM plays WHERE gid = $gameID;";
    $rows = mysql_query($query) or die('Query failed: ' . mysql_error());	
		
    // Delete the game itself
    $query = "DELETE FROM games WHERE gid = $gameID;";
    $rows = mysql_query($query) or die('Query failed: ' . mysql_error());
		
    // close connection
    mysql_close($link);
		
    return ($rows == 1);	
  }

	
  static function get_next_id(){
    return mt_rand(0, 1000000);
  }
	
  //saves a game that has been created for the first time
  //returns whether successful
  static function first_save($game){
		
    //insert game into database
    $id      = $game->id;
    $cereal  = serialize($game);
    $version = 'alpha';
		
    // Connecting, selecting database
    $link = mysql_connect(Configuration::db_server(), Configuration::db_username(), Configuration::db_password())
      or die('Could not connect: ' . mysql_error());
    mysql_select_db(Configuration::db_name()) or die('Could not select database');
		
    // Performing SQL Insert
    $query = "INSERT INTO games (gid, game, version) VALUES ($id, '$cereal', '$version');";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());		
		
    //insert entries into the plays table
    if($result){
      $emails = array_keys($game->commanders);
      foreach ($emails as $email){
	$query = "INSERT INTO plays (email, gid) VALUES ('$email', $id);";
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());	
      }
    }
		
    //close connection
    mysql_close($link);
		
    return $result;
  }
	
  //loads the game
  // this is different from get_game in that is uses a transaction 
  // returns the game and the database link for committal
  static function load($gameID){
		
    // Connecting, selecting database
    $link = mysql_connect(Configuration::db_server(), Configuration::db_username(), Configuration::db_password())
      or die('Could not connect: ' . mysql_error());
    mysql_select_db(Configuration::db_name()) or die('Could not select database');
		
    //start the transaction.  This transaction will be completed by the save method
    $query = "START TRANSACTION";
    $result = mysql_query($query, $link) or die('Query failed: ' . mysql_error());	
				
    // Performing SQL query
    $query = "SELECT game FROM games WHERE gid = $gameID;";
    $result = mysql_query($query, $link) or die('Query failed: ' . mysql_error());	
		
    $rows = mysql_num_rows($result);
    if($rows === 1){			
      //get the data
      $line = mysql_fetch_array($result, MYSQL_ASSOC);
			
      //set session variables
      $cereal = $line['game'];
      $game = unserialize($cereal);
    } else {
      $game = null;
    }
		
    // Free resultset but don't close connection, because save() will need it
    mysql_free_result($result);
		
    return array($game, $link);	
  }
	
  //saves the game
  //returns whether it was successful
  static function save($game, $link){
    $id = $game->id;
    $cereal = serialize($game);
		
    // Performing SQL Update
    $query = "UPDATE games SET game = '$cereal' WHERE gid = $id;";
    $result = mysql_query($query, $link) or die('Query failed: ' . mysql_error());		
		
    //Commit the transaction
    $query = "COMMIT";
    $result = mysql_query($query, $link) or die('Query failed: ' . mysql_error());			
		
    //close connection
    mysql_close($link);
		
    return $result;
  }
	
  //if we load a game, but don't want to save it
  //we'll have to rollback
  static function rollback($link){
    $query = 'ROLLBACK';
    $result = mysql_query($query, $link) or die('Query failed: ' . mysql_error());			
		
    //close connection
    mysql_close($link);
		
    return $result;
  }
	
  //checks if email and password match, returns a boolean indicating success
  static function matches($email, $password){
    $accepted = false;
		
    // Connecting, selecting database
    $link = mysql_connect(Configuration::db_server(), Configuration::db_username(), Configuration::db_password())
      or die('Could not connect: ' . mysql_error());
    mysql_select_db(Configuration::db_name()) or die('Could not select database');
		
		
    // Performing SQL query
    $query = "SELECT * FROM users WHERE email = '$email';";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $rows = mysql_num_rows($result);
    if($rows === 1){
      //good if there's only one row
      $row = mysql_fetch_assoc($result);
      $db_password = $row['password'];
			
      if($password == $db_password 
	 or md5($password) == $db_password){
	$accepted = true;
				
	//update the password	
	$salted = md5(Configuration::presalt() . $password . Configuration::postsalt());
	$query = "UPDATE users SET password = '$salted' WHERE email = '$email' ;";
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
								
      } else if(md5(Configuration::presalt() . $password . Configuration::postsalt()) == $db_password){
	$accepted = true;
      } else {
	$accepted = false;
      }		
    } 
		
    // close connection
    mysql_close($link);
		
    return $accepted;
  }
	
  //kicks player from game by removing relationship in the plays table
  static function kick_player($gid, $email){
	
    // Connecting, selecting database
    $link = mysql_connect(Configuration::db_server(), Configuration::db_username(), Configuration::db_password())
      or die('Could not connect: ' . mysql_error());
    mysql_select_db(Configuration::db_name()) or die('Could not select database');
		
    // Performing SQL query
    $query = "DELETE FROM plays WHERE email = '$email' and gid = '$gid';";
    $rows = mysql_query($query) or die('Query failed: ' . mysql_error());
		
    // Free resultset and close connection
    mysql_close($link);
		
    return ($rows == 1);
  }
	
  //adds the user to the database
  static function add_user($email, $password){
		
    // Connecting, selecting database
    $link = mysql_connect(Configuration::db_server(), Configuration::db_username(), Configuration::db_password())
      or die('Could not connect: ' . mysql_error());
    mysql_select_db(Configuration::db_name()) or die('Could not select database');
		
    // Performing SQL Insert	
    $salted = md5(Configuration::presalt() . $password . Configuration::postsalt());
    $query = "INSERT INTO users (email, password) VALUES ('$email', '$salted');";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());		
				
    //close connection
    mysql_close($link);
		
    return $result;		
  }
	
  //checks if the alpha code is in the database, if so, removes it
  //returns true on success, false otherwise
  static function use_code($code){
	
    // Connecting, selecting database
    $link = mysql_connect(Configuration::db_server(), Configuration::db_username(), Configuration::db_password())
      or die('Could not connect: ' . mysql_error());
    mysql_select_db(Configuration::db_name()) or die('Could not select database');
		
    //sanitizing input running query
    $code = htmlspecialchars($code, ENT_QUOTES);
    $query = "SELECT * FROM codes WHERE code = '$code';";
    $result = mysql_query($query, $link) or die('Query failed: ' . mysql_error());	
		
    //analyzing results
    $rows = mysql_num_rows($result);
    if($rows === 1){			
      //free the result because we don't care about it
      mysql_free_result($result);
			
      //delete the entry
      $query = "DELETE FROM codes WHERE code = '$code';";
      $affected_rows = mysql_query($query, $link) or die('Query failed: ' . mysql_error());
      $success = true;
    } else {
      $success = false;
    }
		
    //close connection
    mysql_close($link);
		
    return ($success);
  }
	
  //adds a code to the database
  static function add_code($code){
		
    // Connecting, selecting database
    $link = mysql_connect(Configuration::db_server(), Configuration::db_username(), Configuration::db_password())
      or die('Could not connect: ' . mysql_error());
    mysql_select_db(Configuration::db_name()) or die('Could not select database');
		
    // Performing SQL Insert
    $query = "INSERT INTO codes (code) VALUES ('$code');";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());		
				
    //close connection
    mysql_close($link);
		
    return $result;		
  }
	
  //returns an array of all codes in the database
  static function get_codes(){
		
    // Connecting, selecting database
    $link = mysql_connect(Configuration::db_server(), Configuration::db_username(), Configuration::db_password())
      or die('Could not connect: ' . mysql_error());
    mysql_select_db(Configuration::db_name()) or die('Could not select database');
		
    // Performing SQL Query
    $query = "SELECT * FROM codes;";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());		
		
    $codes = array();
    while($row = mysql_fetch_assoc($result)){
      $codes[] = $row['code'];
    }
		
    // Free resultset and close connection
    mysql_free_result($result);
    mysql_close($link);
		
    return $codes;				
  }
	
  //saves a copy of the game in the history table
  //returns whether successful
  static function archive($game){
    //insert game into database
    $id      = $game->id;
    $turn    = $game->day;
    $version = 'alpha';
    $cereal  = serialize($game);
		
    // Connecting, selecting database
    $link = mysql_connect(Configuration::db_server(), Configuration::db_username(), Configuration::db_password())
      or die('Could not connect: ' . mysql_error());
    mysql_select_db(Configuration::db_name()) or die('Could not select database');
		
    // Performing SQL Insert
    $query = "INSERT INTO history (gid, turn, version, game) VALUES ($id, $turn, '$version', '$cereal');";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
				
    //close connection
    mysql_close($link);
		
    return $result;
  }
	
  //saves the log message
  static function save_log($log){		
    $date = date('Y-m-d', time());
    $logfp = fopen("x/log/battle_$date.log", 'a');
    fwrite($logfp, $log);
    fclose($logfp);
  }
	
  //gets the twitter updates
  static function get_updates(){
    $screenname = 'furyspace';
    $filename = "x/twitter/$screenname.xml";
    $url = "http://twitter.com/statuses/user_timeline.xml?screen_name=$screenname";
    if(!file_exists($filename) or Model::file_age($filename) > 60){   
    
      //pull in fresh data using cURL
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($curl, CURLOPT_TIMEOUT, 2);
      $xml = curl_exec($curl);
      curl_close($curl);
	    
      // store the data
      if ($xml && preg_match('/^\\<\\?xml.*\\<.statuses\\>$/s', $xml)) {
        $file = fopen($filename, 'w');
        if($file){
          fwrite($file, $xml, strlen($xml));
          fclose($file);
        }
      } else {
        $xml = '';
      }

    }
    if (!$xml) {
      //read the data
      $file = fopen($filename, 'r');
      $xml = fread($file, filesize($filename));
      fclose($file);
    }

    $updates = array();
    $sxml = simplexml_load_string($xml);
    foreach($sxml->status as $status){
      $update = array();
      $patterns[0] = '/(http:\\/\\/[a-zA-Z0-9\\/\\.]+)/';
      $replacements[0] = '<a href = "$1">$1</a>';
      $patterns[1] = '/(#\w+)/';
      $replacements[1] = '<a href = "http://twitter.com/#search?q=$1">$1</a>';
      $patterns[2] = '/@(\w+)/';
      $replacements[2] = '<a href = "http://twitter.com/$1">@$1</a>';
      $update['html'] = preg_replace($patterns, $replacements, $status->text);
      $update['timestamp'] = strtotime($status->created_at);
      
      $updates[] = $update;
    }
    return $updates;
  }
	
  //gets the age of a file in seconds
  static function file_age($filename){
    $age = 0;
    if(file_exists($filename)){
      $stat = stat($filename);
      $modified = $stat['mtime'];
      $current = time();
      $age = $current - $modified;
    }
    return $age;
  }
}

?>
