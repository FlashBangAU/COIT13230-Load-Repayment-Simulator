<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Logged Out</title>
</head>
<body>
	<h1>Loan Repayment Simulator</h1>
	<?php
		session_start();

		$validSession = require('check-session.php');

		if ($validSession){
			$oldUser = $_SESSION['valid-user'];
			unset($_SESSION['valid-user']);
			session_destroy();
		}

		if (!empty($oldUser)) {
			echo 'Logged Out<br>';
		}else{
			echo 'You were not logged in, and so have no been logged out.<br>';
		}
		include('footer-logged-out.php');
	?>
</body>
</html>