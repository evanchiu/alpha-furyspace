<?php
	//testPage.php
	//this page let me test whatever I'm curious about
	
	session_start();
	require_once('global.php');
		
	$data['email'] = "testing...";
	$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
	$data['title'] = "$furyspace } test page";
	$data['styles'] = array('space.css', "style.php?color=cyan");
	View::header($data);
	
?>
	<h1>Test Page</h1>
	<p>This is where I test stuff.</p>	
<?php
	
	//run test functions here
	print time();
	
	print '<p>'.date('j i s', 1234622991).'</p>';
	
	colorTest();
	
	list($r, $g, $b) = Controller::split_hex('a0b0c0');
	print "r = $r, g = $g, b = $b";
	
	$a = 0;
	$b = 0;
	$c = 5;
	$d = 12;
	$e = Game::mathdistance($a, $b, $c, $d);
	print "a = $a, b = $b, c = $c, d = $d, e = $e";
	
	//colorTest();

/*
	//not sure what this test is
	$string = 'abcd';
	print "string@2 == {$string[2]}";

	//configuration test
	print "<h2>Configuration</h2>\n";
	$server = Configuration::db_server();
	print "<p>server = ($server)</p>";
	
	$emails = array('evan@evan.com', 'josh@josh.com', 'peter@peter.com');
	$g = new Game(42, 'milky way', 10, $emails);
	$x = strlen(serialize($g));
	print "<h2>Size</h2><p>Game is $x bytes.</p>\n";
	print "<h2>Map</h2><img src = \"zmaps/42.png\">\n";
	print "<h2>Galaxy:</h2>\n<pre>";
	var_dump($g);
	print "\n</pre>\n";

	print "<h2>Serialization</h2>\n";

	$g = new Game(100, 'test', 20, array('evan', 'rukia'));
	$cereal_in 	= serialize($g);
	$slash		= addslashes($cereal_in);
	$cereal_out = stripslashes($slash);
	
	if($cereal_in == $cereal_out){
		print "<p>cereal in and out are the same.</p>\n";
	}
	
	$g = unserialize($cereal_out);
	print "<pre>\n";
	var_dump($slash);
	print "</pre>\n";
*/
	
	View::footer($renderstart); ?>

<?php
	function test_colors(){
		$g = new Game(100, 'test', 20, array('evan', 'rukia'));
	}
	
	function test_sanitize(){
		print '<h2>Sanitize Test</h2>';
		$name = "';alert(String.fromCharCode(88,83,83))//';alert(String.fromCharCode(88,83,83))//\";alert(String.fromCharCode(88,83,83))//\";alert(String.fromCharCode(88,83,83))//--></SCRIPT>\">'><SCRIPT>alert(String.fromCharCode(88,83,83))</SCRIPT>";
		$fixed = Controller::sanitize($name);
		print "<p><b>xss</b> sanitized is <b>$fixed</b>.</p>\n";	
	}
	
	function test_email_sanitize(){
		print '<h2>Sanitize Test</h2>';
		$email = "evan.chiu@gm-_||ail.com";
		$fixed = Controller::sanitize_email($email);
		print "<p><b>$email</b> sanitized is <b>$fixed</b>.</p>\n";	
	}
	
	function colorTest(){
		$colors = Configuration::colors();
		foreach($colors as $name => $hex){
			print "<p><span class = \"$name\">$name</span></p>\n";
		}
	
	}
?>
