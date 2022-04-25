<?php

$webapp_servername = "127.0.0.1";
$webapp_schema     = "login";
$webapp_username   = "login";
$webapp_password   = "CowSaysMoo";
$webapp_tables     = ["CREATE TABLE IF NOT EXISTS $webapp_schema.users (user_id int not null auto_increment, username varchar(50) not null, password varchar(50) not null, primary key (user_id))",
					  "INSERT INTO $webapp_schema.users (username, password) VALUES ('user', 'admin')",
					  "INSERT INTO $webapp_schema.users (username, password) VALUES ('admin', 'password')",
					  "CREATE TABLE IF NOT EXISTS $webapp_schema.photos (photo_id int not null auto_increment, user_id int not null, name varchar(50), primary key (photo_id))",
					  "CREATE TABLE IF NOT EXISTS $webapp_schema.sessions (session_id int not null auto_increment, user_id int not null, primary key (session_id))"
					 ];

function get_connection() {

	$db_conn = NULL;
	global $webapp_servername;
	global $webapp_schema;
	global $webapp_username;
	global $webapp_password;

	try {
		$db_conn = new PDO("mysql:host=$webapp_servername;dbname=$webapp_schema", $webapp_username, $webapp_password);
	} catch (PDOException $e) {}

	return $db_conn;

}

function create_user_db($db_admin_username, $db_admin_password) {

	global $webapp_servername;
	global $webapp_username;
	global $webapp_password;
	global $webapp_schema;
	global $webapp_tables;

	try {
		# Connect to the database
		$db_conn = new PDO("mysql:host=$webapp_servername", $db_admin_username, $db_admin_password);

		# Create the webapp user account in the database
		$sql = "CREATE USER IF NOT EXISTS '$webapp_username'@'localhost' IDENTIFIED BY '$webapp_password'";
		$sth = $db_conn->prepare($sql);
		$result = $sth->execute();

		# Create the webapp database schema
		$sql = "CREATE DATABASE IF NOT EXISTS `$webapp_schema`";
		$sth = $db_conn->prepare($sql);
		$result = $sth->execute();

		# Grant the user account access to the schema
		$sql = "GRANT ALL PRIVILEGES ON $webapp_schema.* TO '$webapp_username'@'localhost'";
		$sth = $db_conn->prepare($sql);
		$result = $sth->execute();

		# Create the required table(s)
		foreach($webapp_tables as $sql) {
			$sth = $db_conn->prepare($sql);
			$result = $sth->execute();
		}

		return TRUE;
		
	} catch (PDOException $e) {
		return $e;
	}

	return FALSE;
}

function login($username, $password) {
	global $webapp_servername;
	global $webapp_username;
	global $webapp_password;
	global $webapp_schema;

	try {
		# Connect to the database
		$db_conn = new PDO("mysql:host=$webapp_servername;dbname=$webapp_schema", $webapp_username, $webapp_password);

		# Search for a user with the specified credentials
		$sql = "SELECT user_id FROM users WHERE username = :username AND password = :password";
		$sth = $db_conn->prepare($sql);
		$result = $sth->execute(array(':username' => $username, ':password' => $password));
		$count = $sth->rowCount();
		if ($count == 1) {

			# Create a session for this user
			$user_id = $sth->fetch(PDO::FETCH_ASSOC)['user_id'];
			$sql = "INSERT INTO sessions (user_id) VALUES (:user_id)";
			$sth = $db_conn->prepare($sql);
			$result = $sth->execute(array(':user_id' => $user_id));
			$session_id = $db_conn->lastInsertId();

			# Store session_id and user_id in cookies (expires in 1 day)
			setcookie('session_id', $session_id, time() + (86400 * 30));
			setcookie('user_id', $user_id, time() + (86400 * 30));

			# Success or failure
			return $result;
		}

	} catch (PDOException $e) {
		return $e;
	}

	return FALSE;
	
}

function is_logged_in() {
	global $webapp_servername;
	global $webapp_username;
	global $webapp_password;
	global $webapp_schema;

	# Get credentials from cookies
	$session_id = $_COOKIE['session_id'];
	$user_id = $_COOKIE['user_id'];
	if (empty($session_id) || empty($user_id)) {
		return FALSE;
	}

	try {
		# Connect to the database
		$db_conn = new PDO("mysql:host=$webapp_servername;dbname=$webapp_schema", $webapp_username, $webapp_password);

		# Search for a user with the specified credentials
		$sql = "SELECT * FROM sessions WHERE user_id = :user_id AND session_id = :session_id";
		$sth = $db_conn->prepare($sql);
		$result = $sth->execute(array(':user_id' => $user_id, ':session_id' => $session_id));
		$count = $sth->rowCount();
		if ($count == 1) {
			return TRUE;
		} else {
			return FALSE;
		}

	} catch (PDOException $e) {
		return $e;
	}

	return FALSE;
	
}

function get_photos() {
	global $webapp_servername;
	global $webapp_username;
	global $webapp_password;
	global $webapp_schema;

	$photos = array();

	# Get the current user from the cookies
	$user_id = $_COOKIE['user_id'];

	try {
		# Connect to the database
		$db_conn = new PDO("mysql:host=$webapp_servername;dbname=$webapp_schema", $webapp_username, $webapp_password);

		# Search for a user with the specified credentials
		$sql = "SELECT * FROM photos WHERE user_id = :user_id";
		$sth = $db_conn->prepare($sql);
		$result = $sth->execute(array(':user_id' => $user_id));
		$photos = $sth->fetchAll(PDO::FETCH_ASSOC);

	} catch (PDOException $e) {
		return $e;
	}

	return $photos;
	
}

function add_photo($name) {

	global $webapp_servername;
	global $webapp_username;
	global $webapp_password;
	global $webapp_schema;

	# Get the current user from the cookies
	$user_id = $_COOKIE['user_id'];

	try {
		# Connect to the database
		$db_conn = new PDO("mysql:host=$webapp_servername;dbname=$webapp_schema", $webapp_username, $webapp_password);

		# Search for a user with the specified credentials
		$sql = "INSERT INTO photos (user_id, name) VALUES (:user_id, :name)";
		$sth = $db_conn->prepare($sql);
		$result = $sth->execute(array(':user_id' => $user_id, ':name' => $name));
		$photo_id = $db_conn->lastInsertId();

	} catch (PDOException $e) {
		return $e;
	}

	return $photo_id . '-' . $name;

}