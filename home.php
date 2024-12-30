<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<script src="js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>
	<title>Home</title>
</head>
<body>
	<h1>Loan Repayment Simulator | Home</h1>
	<?php
		session_start();
		$validSession = require('check-session.php');

		$validLogin = require("check-login.php");

		if($validLogin || $validSession){
			$name = $_SESSION['username'];

			echo "Welcome, $name.<br><br>";

			echo "<h2>Your Options</h2>";
			echo '<p><a href="loans.php" class="btn btn-primary">View all home loans</a><br>';
			echo '<a href="add-loan.php" class="btn btn-primary">Add a loan</a></p>';

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