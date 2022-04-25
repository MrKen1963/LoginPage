<?php

error_reporting(-1);
$db_conn = NULL;

require('db.php');

# Check for config
$db_conn = get_connection();
if ($db_conn === NULL) {
	header("Location: configure.php");
	exit();
}

$username = "";
$password = "";
$failed_login = NULL;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$failed_login = TRUE;
	
	$username = $_POST['username'];
	$password = $_POST['password'];

	$result = login($username, $password);
	if ($result === TRUE) {
		header("Location: photos.php");
		exit();
	} else {
		$failed_login = TRUE;
		var_dump($result);
	}
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (array_key_exists('logout', $_GET)) {
		setcookie('session_id', '', time() - (86400 * 30));
		setcookie('user_id', '', time() - (86400 * 30));
		
	}
}
?>

<!DOCTYPE html>
<html>

<head>
<link rel="stylesheet" href="Login.css">
</head>

<body>

	<h1>Attempt To Login At Your Own Risk!</h1>
	
	<form action="index.php" method="post">
	
		Username: <input name="username" id="username" placeholder="Username" value=""><br>
		Password: <input name="password" id="password" placeholder="Password" value=""><br>
		<input type="submit">
		
	</form>

	<?php if ($failed_login) { ?>
		Access Denied, Better Luck Next Time!
	<?php } ?>
	
</body>
</html>