<?php

require('db.php');

# Check if logged in
if (!is_logged_in()) {
	header("Location: index.php");
	exit();
}

$file_uploaded = NULL;
$valid_images = ['jpg','jpeg'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	$file_uploaded = FALSE;
	$target_dir = "images/";
	$imageFileType = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
	# Check if valid file extension
	if(in_array($imageFileType, $valid_images)) {
		$new_name = add_photo($_FILES["photo"]["name"]);
		$target_file = $target_dir . $new_name;
		move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);
		$file_uploaded = TRUE;
	}
}

$photos = get_photos();

?>

<!doctype html>
<html>

<head>
	<link rel="stylesheet" href="photos.css">
</head>

<body>

	<div style="float:right;"><a href="index.php?logout">Logout</a></div>
	
	<h1>Access Has Been Granted, Congratulations You've Broken the Internet!!!</h1>
	
<!--
	<h1>Photos</h1>
	<div>
		<?php foreach($photos as $photo) { ?>
			<img src="images/<?=$photo['photo_id']?>-<?=$photo['name']?>" style="height:100px">
		<?php } ?>
	</div>
	<form action="photos.php" method="post" enctype="multipart/form-data">
		Upload new photo: <input name="photo" id="photo" type="file"><br>
		<input type="submit">
	</form>
	<?php if ($file_uploaded === TRUE) { ?>
		File uploaded!
	<?php } else if ($file_uploaded === FALSE) { ?>
		File uploaded failed!
	<?php } ?>
-->

</body>
</html>