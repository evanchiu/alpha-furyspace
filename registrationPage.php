<?php
	//registrationPage.php
	//this is a visable page, this is where the user will be once logged in
	//it lists the games they're playing in
	session_start();
	require_once('global.php');
	
	$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
	$data['title'] = "$furyspace } registration";
	$data['description'] = "A small turn-based space-themed dominance game";
	$data['metakeywords'] = array('furyspace', 'game');
	$data['styles'] = array('space.css');
	View::header($data);
?>
	<h1>Registration</h1>
	<p>If you have an alpha code, you can register now.</p>
	<form action = "action.php" method = "post">
	<table id = "registration">
		<tr>
			<td>Alpha Code:</td>
			<td><input type = "text" name = "code" /></td>
		</tr><tr>
			<td>Email:</td>
			<td><input type = "text" name = "email" /></td>
		</tr><tr>
			<td>Password:</td>
			<td><input type = "password" name = "password" /></td>
		</tr><tr>
			<td>Confirm Password:</td>
			<td><input type = "password" name = "password2" /></td>
		</tr>	
	</table>
	<input type="hidden" name="origin" value="html" /> 
	<input type="hidden" name="a" value="register" />
	<input type="submit" value="register" />
	</form>	
<?php
	View::footer($renderstart);
?>
