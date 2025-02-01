<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<script src="js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>
	<title>Help</title>
</head>
<body>
	<div class="container d-flex flex-column min-vh-100">
		<h1 class="my-4">Home Loan Simulator | Help</h1>
		<?php
			session_start();
			$validSession = require('check-session.php');

			$validLogin = require("check-login.php");

			echo <<<END
				<h3>Loans/Interest/Additional Payments</h3>
				<p>
					<b>Adding:</b>
					If adding a loan, click 'Add New Loan' on the footer or home page.<br>
					Interest or Additional Repayments can be accessed by clicking 'View Changing Elements' on the loan list.<br>
					Enter all fields in the form and click 'Add'.
					<br><br>

					<b>Editing:</b>
					Edit any fields in the form and click 'Edit'.
					<br><br>

					<b>Deleting:</b>
					View details and confirm you want to delete it and click 'Delete'.
					<br>

					When deleting a loan, all associated interest and payments are also deleted.
				</p>

				<h3>View Changing Elements</h3>
				<p>
					When adding or editing interest or additional repayment, a checkbox exists to refinance the loan. This will change the payment so that the loan ends at the initial term duration. Any changes after that date that are not checked will shorten the duration unless forced(etc. interest increases to a higher rate).<br><br>

					If taking an amount out of the loan without checking the box to refinance may cause the loan to not finish in the required period or simply increase the amount on the loan over time (Your bank should tell you how much is possible to take out if allowed).
				</p>

				<h3>Simulate Loan</h3>
				<p>
					The loan will run through a loop for each day, calculating the daily interest. It will charge the accumulated interest to remove the payment amount and the remainder from the principal.<br>
					This is repeated until the loan has ended or exceeded the loan duration (which should never happen unless a payment of a negative amount was taken in the loan).<br>
					Graph information is taken from this simulation and used to display loan data through the term.<br>
					Below the graph a table will be shown of changes that happened to the loan and the payments required for each interval.
				</p>

			END;


			if($validLogin && $validSession){
				require 'footer-logged-in.php';
			}else{
				require 'footer-logged-out.php';
			}

		?>
	</div>
</body>