<?php
	//mapPage.php
	//this page shows the user the map of the requested galaxy
	session_start();
	require_once('global.php');
	Controller::assert_login();
	
	$email = $_SESSION['email'];
	$admins = array('evchiu@gmail.com', 'evan@evanchiu.com'); //Config::get_admins();
	if(!in_array($email, $admins)){
		Controller::error(705, "So, uh, now you know that I have some admin tools on here. " .
							   "[<a href = \"homePage.php\">home</a>]");

	}
	
	$data['email'] = "Administrator";
	$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
	$data['title'] = "$furyspace } Administration Page";
	$data['styles'] = array('space.css');
	View::header($data);
?>
	<h1>Admin Page</h1>
	
	<?php View::messages(); ?>
	
	<h2>Add Codes</h2>
	<form action = "action.php" method = "post">
	<table class = "admin">
		<tr>
			<td>Code:</td>
			<td><input type = "text" name = "code" /></td>
		</tr>
	</table>
	<input type="hidden" name="origin" value="html" /> 
	<input type="hidden" name="a" value="add_code" />
	<input type="submit" value="add code" />
	</form>		
	
	<h2>Show Codes</h2>
	<form action = "adminPage.php" method = "post">
	<input type="hidden" name="origin" value="html" /> 
	<input type="hidden" name="a" value="show_codes" />
	<input type="submit" value="show codes" />
	</form>		
<?php
	if(isset($_REQUEST['a']) and $_REQUEST['a'] == 'show_codes'){
		$codes = Model::get_codes();
		if(count($codes) > 0){
			print "<ul>\n";
			foreach ($codes as $code){
				print "<li><span class = \"code\">$code</span></li>\n";
			}
			print "</ul>\n";
		} else {
			print "<p class = \"error\">I'm sorry, it doesn't look like there are any codes in the database.</p>";
		}

	}	
?>
	
	<h2>Delete Galaxy</h2>
	<form action = "action.php" method = "post">
	<table class = "admin">
		<tr>
			<td>GID:</td>
			<td><input type = "text" name = "gid" /></td>
		</tr>
	</table>
	<input type="hidden" name="origin" value="html" /> 
	<input type="hidden" name="a" value="delete_galaxy" />
	<input type="submit" value="delete galaxy" />
	</form>
	
	<h2>Kick Player</h2>
	<form action = "action.php" method = "post">
	<table class = "admin">
		<tr>
			<td>GID:</td>
			<td><input type = "text" name = "gid" /></td>
		</tr>
		<tr>
			<td>Email:</td>
			<td><input type = "text" name = "email" /></td>
		</tr>
	</table>
	<input type="hidden" name="origin" value="html" /> 
	<input type="hidden" name="a" value="kick_player" />
	<input type="submit" value="kick player" />
	</form>
	
	<h2>Become Player</h2>
	<form action = "action.php" method = "post">
	<table class = "admin">
		<tr>
			<td>Email:</td>
			<td><input type = "text" name = "email" /></td>
		</tr>
	</table>
	<input type="hidden" name="origin" value="html" /> 
	<input type="hidden" name="a" value="become_player" />
	<input type="submit" value="become player" />
	</form>
	
	<h2>Kill Phantoms</h2>
	<form action = "action.php" method = "post">
	<table class = "admin">
		<tr>
			<td>GID:</td>
			<td><input type = "text" name = "gid" /></td>
		</tr>
	</table>
	<input type="hidden" name="origin" value="html" /> 
	<input type="hidden" name="a" value="kill_phantoms" />
	<input type="submit" value="kill phantoms" />
	</form>
	
	<h2>Galaxy Viewer</h2>
	<form action = "adminPage.php" method = "post">
	<table class = "admin">
		<tr>
			<td>GID:</td>
			<td><input type = "text" name = "gid" /></td>
		</tr>
	</table>
	<input type="hidden" name="origin" value="html" /> 
	<input type="hidden" name="a" value="view_galaxy" />
	<input type="submit" value="view galaxy" />
	</form>
	
	<h2>SQLite Database Creation</h2>
	<form action = "action.php" method="post">
	<input type="hidden" name="origin" value="html" /> 
	<input type="hidden" name="a" value="create_database" />
	<input type="submit" value="view galaxy" />
	</form>
	
<?php
	if(isset($_REQUEST['a']) and $_REQUEST['a'] == 'view_galaxy'){
		$game = Model::get_game($_REQUEST['gid']);
		print "<pre>\n";
		var_dump($game);
		print "</pre>\n";
	}

	View::footer($renderstart);
?>
