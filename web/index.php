<?php
//index.php
//the home page
	session_start();

	require_once('global.php');
	
	$data['email'] = (isset($_SESSION['email'])) ? $_SESSION['email'] : '';
	$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
	$data['title'] = "alpha.$furyspace";
	$data['description'] = "A small turn-based space-themed dominance game";
	$data['metakeywords'] = array('furyspace', 
								'game');
	$data['styles'] = array('space.css');
	View::header($data);
	View::messages();
?>

<h1>Hey There!</h1>
<p>Furyspace is currently a closed alpha, but I'm always looking for more testers.  At this point the game isn't so much about being fun to play as it is figuring out where the remaining problems are, and providing me with good feedback about what happened and how I might be able to fix it.  I also appreciate any ideas about new development, functionality or anything else that will make the game better.</p>

<p>If you have an alpha registration code, you can use it on the <a href = "registrationPage.php">Registration Page</a>.</p>

<?php View::footer($renderstart); ?>
