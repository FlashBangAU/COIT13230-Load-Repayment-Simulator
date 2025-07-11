<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<script src="js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>
	<title>Logged Out</title>
</head>
<body>
	<div class="container d-flex flex-column min-vh-100">
		<h1 class="my-4">Loan Repayment Simulator</h1>
		<?php
			session_start();

			$validSession = require('check-session.php');

			if (isset($_SESSION['valid-user'])) {
			    session_unset();
			    session_destroy();
			    echo 'Logged Out.<br>';
			    echo '<p><a href="login.php" class="btn btn-primary">Login</a></p>';
			}else{
				echo 'You were not logged in, and so have not been logged out.<br>';
				echo '<p><a href="login.php" class="btn btn-primary">Login</a></p>';
			}
			
			include('footer-logged-out.php');
		?>
	</div>
</body>
</html>