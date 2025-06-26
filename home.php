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
	<div class="container d-flex flex-column min-vh-100">
		<h1 class="my-4">Home Loan Simulator | Home</h1>
		<?php
			session_start();

			//echo '<pre>';
			//print_r($_SESSION); // Debugging line to show session contents
			//echo '</pre>';

			$validSession = require('check-session.php');

			$validLogin = require("check-login.php");

			if($validLogin && $validSession){
				$name = $_SESSION['username'];

				echo "Welcome, $name.<br><br>";

				echo "<h2>Your Options</h2>";
				echo '<p><a href="view-loans.php" class="btn btn-primary">View all home loans</a>	';
				echo <<<END
					<p>
					This web application is a prototype created by Hughen Flint as a final university project.<br>
					The goal of this is to create an accurate home loan simulation where the user is able to predict, track, discover and experiment with loan repayments.<br><br>
					<b>Features Include:</b> Hold multiple Loans, Ability to make additional repayments, Ability to adjust interest rates, Change required payment amounts, Display graph results, Show new payments when variables change the loan.<br><br>
					Instructions on use and function of this application will be found <a href="help.php">here</a>.
					</p>

					<p style="color: red;">
						This web application is a prototype and does not take any accountability for any financial decisions.<br> 
						This is a tool designed to aid in financial understanding and will have divations from what is seen in the real world.
					</p>
				END;

				include("footer-logged-in.php");
			}else{
				echo "You are not logged in.<br>";

				echo "<h2>Your Options</h2>";
				echo "<p>You must log in to manage home loan.</p>";
				echo '<p><a href="login.php" class="btn btn-primary">Login</a></p>';
				echo <<<END
					<p>
					This web application is a prototype created by Hughen Flint as a final university project.<br>
					The goal of this is to create an accurate home loan simulation where the user is able to predict, track, discover and experiment with loan repayments.<br><br>
					<b>Features Include:</b> Hold multiple Loans, Ability to make additional repayments, Ability to adjust interest rates, Change required payment amounts, Display graph results, Show new payments when variables change the loan.<br><br>
					Instructions on use and function of this application will be found <a href="help.php">here</a>.
					</p>

					<p style="color: red;">
						This web application is a prototype and does not take any accountability for any financial decisions.<br> 
						This is a tool designed to aid in financial understanding and will have divations from what is seen in the real world.
					</p>
				END;

				include("footer-logged-out.php");
			}
		?>
	</div>
</body>
</html>