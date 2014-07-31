<?php
	//creategamepage.php
	//this page creates a game object
	session_start();
	require_once('global.php');
	Controller::assert_login();

	$email = $_SESSION['email'];
	if(!Configuration::is_administrator($email)){
		Controller::error(705, "Admins only, please return to the " .
							   "[<a href = \"homePage.php\">home</a>]");
	}

	if(isset($_POST['action'])){
		if($_POST['action'] == 'create'){
			Controller::create_game();
		}
	}
		
	$email = $_SESSION['email'];
	
	$data['email'] = "$email";
	$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
	$data['title'] = "$furyspace } Galaxy Creator";
	$data['styles'] = array('space.css');
	View::header($data);
	
?>
	<h1>Create a new Galaxy!</h1>
	<p>Easiest way to start this is a terribly basic tabular layout...</p>
	
	<form action = "creategamePage.php" method="post">
	<table>
		<tr><th colspan="2">Galaxy Details</th></tr>
		<tr>
			<td>Galaxy Name:</td>
			<td><input type="text" name="galaxy" id="galaxy" /></td>
		</tr>
		<tr>
			<td>Galaxy Size:</td>
			<td>
				<select name="gsize">
					<option value="3">Tiny (3 per player)</option>
					<option value="5">Small (5 per player)</option>
					<option value="10">Medium (10 per player)</option>
					<option value="20">Large (20 per player)</option>
				</select>
			</td>
		</tr>
		<tr><th colspan="2">Commanders to Invite</th></tr>
		<tr>
			<td>Commander 1 Email:</td>
			<td><input type="text" name="commander1" id="commander1" value="<?php print $email; ?>"/></td>
		</tr>
<?php
	$count = count(Configuration::colors());
	for($i = 2; $i <= $count; $i++){
		print "\t\t<tr>\n";
		print "\t\t\t<td>Commander $i Email:</td>\n";
		print "\t\t\t<td><input type=\"text\" name=\"commander$i\" id=\"commander$i\"/></td>";
	}
?>
	</table>
	<input type="hidden" name="action" value="create" />
	<input type="submit" value="Create" />
	</form>
<?	
	View::footer($renderstart);
?>
