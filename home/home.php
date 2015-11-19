<head>
<meta charset="utf-8">
<title>Instagram</title>
<link rel="stylesheet" type="text/css" media="screen"
	  href="stylesheetHome.css" />

<!-- pulls the jquery file from the directory above this one -->
<script type='text/javascript' src="../jquery.min.js"></script>
</head>
<body>
<?php
	$cookie = $_COOKIE['instaDBMS'];
	if (!isset($_COOKIE['instaDBMS']))
		header('Location: ../index.php');
 ?>

 <!-- Lets Make the header of the page -->
 <div class=header>
	 <!--TODO: Replace text with an image -->
	 <p id="projectName">instaDBMS</p>
	 <input id="searchSite" name='searchSite' type='text'
	        placeholder=" Search?">
	 <?php
	 require_once("../conn.php");
	 $stmtUN = $mysqli->prepare("SELECT user_name FROM user where user_id= ?");
	 // We cant be sure the user hasn't modified the cookie.
	 $stmtUN->bind_param("s", $_COOKIE['instaDBMS']);
	 $stmtUN->execute();
	 $stmtUN->bind_result($un);
	 while ($stmtUN->fetch())
		echo "<p id=user_name>" . $un . "</p>";
 ?>
</div>

	<!--TODO: Determine how we choose what photos to display -->
	<!-- Static image for now -->
	<?php
	// ? is the user_id who owns the photo.
	// TODO: this gets a list of photos by that user. Do we want a
	// specific one?
	$stmtImage = $mysqli->prepare("SELECT image, photo_id FROM photo WHERE
		photo.user_id = ?");

	// ? is the photo_id to get the likes of (from $stmtImage)
	$stmtCountLike = $mysqli->prepare("SELECT COUNT(photolikes.photo_id)
		FROM photolikes WHERE photolikes.photo_id = ?");

	// First ? is the photo_id of the photo we got
	// Second ? is the user_id of the user to get the comment they made.
	$stmtComment = $mysqli->prepare("SELECT user.user_name, comment.text
		FROM comment INNER JOIN user ON comment.user_id=user.user_id WHERE
		comment.photo_id = ? ORDER BY comment.comment_id ASC");


	// TODO: replace with the user_id from the selction algorithm we chose
	$i = 2;
	$stmtImage->bind_param('i', $i);
	$stmtImage->execute();
	$stmtImage->store_result();
	$stmtImage->bind_result($image, $photo_id);

	while ($stmtImage->fetch())
	{
		echo '<div class="photo_view">';
		// display the photo we got
		echo '<img class="picture" src="data:image/jpg;base64,' . $image .
		'"/>';

		// Now that I have the photo_id, I can get the comments and likes
		// that are tied to that photo.
		$stmtCountLike->bind_param('i', $photo_id);
		$stmtComment->bind_param('i', $photo_id);

		$stmtCountLike->execute();
		$stmtCountLike->store_result();
		$stmtComment->execute();
		$stmtComment->store_result();

		$stmtCountLike->bind_result($numLikes);
		$stmtComment->bind_result($user_name, $text);

		// using COUNT, we are guanenteed only one row, no loop needed.
		$stmtCountLike->fetch();
		echo '<p class="likes">' . $numLikes;
		echo (($numLikes == 1) ? ' like' : ' likes');
		echo '<br>';

		while ($stmtComment->fetch())
		{
			echo '<span class="comment">' . $user_name .  " " . $text .
				 '</span>';
			echo '<br>';
		}
		// and finally, close that div
		echo '</div>';
	}
	?>

</body>
