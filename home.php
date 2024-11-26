<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Home</title>
</head>
<body>
	<h1>Loan Repayment Simulator | Home</h1>
	<?php
		session_start();
		$validSession = require('check-session.php');

		$validLogin = require("check-login.php");

		if($validLogin || $validSession){
			$name = $_SESSION['valid-user'];

			echo "Welcome, $name.<br><br>";

			echo "<h2>Your Options</h2>";
			echo '<p><a href="loans.php">View all home loans</a><br>';
			echo '<a href="add-loan.php">Add a loan</a></p>';

			include("footer-logged-in.php");
		}else{
			echo "You are not logged in.<br>";

			echo "<h2>Your Options</h2>";
			echo "<p>You must log in to manage home loan.</p>";

			include("footer-logged-out.php");
		}
	?>
</body>
</html>