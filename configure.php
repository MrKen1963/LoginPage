<?php

require('db.php');

$bad_config = FALSE;

$db_admin_username = "";
$db_admin_password = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	$db_admin_username = $_POST['username'];
	$db_admin_password = $_POST['password'];

	$result = create_user_db($db_admin_username, $db_admin_password);
	if ($result === TRUE) {
		header("Location: index.php");
		exit();
	} else {
		$bad_config = TRUE;
	}

}

?>
<!doctype html>
<html>
<head>
	<title></title>
</head>
<body>

	<p>Hi! We need to configure your environment.</p>
	<form action="configure.php" method="post">
		Admin Username: <input name="username" id="username" placeholder="Admin username" value="<?=$db_admin_username?>"><br>
		Admin Password: <input name="password" id="password" placeholder="Admin password" value="<?=$db_admin_password?>"><br>
		<input type="submit">
	</form>

	<?php if ($bad_config) { ?>
		That didn't work!
		<?= var_dump($result); ?>
	<?php } ?>

</body>