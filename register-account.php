<!-- HTML Registration Form -->
<script src="js/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/custom.css">
<script src="js/bootstrap.min.js"></script>

<h1>Create an Account</h1>
<form method="post" action="">
    <p>Username: <input type="text" name="name" maxlength="16" class="form-control" required></p>
    <p>Email: <input type="email" name="email" maxlength="50" class="form-control" required></p>
    <p>Password: <input type="password" name="password" maxlength="64" class="form-control" required></p>
    <p><input type="submit" name="submit" class="btn btn-primary" value="Register"></p>
</form>

<?php
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	    // Check if all fields are provided
	    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password'])) {
	        echo "<p class='text-danger'>All fields are required.</p>";
	        exit;
	    }

	    require('db-connection.php');

	    // Sanitize inputs
	    $username = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
	    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
	    $password = trim($_POST['password']);

	    // Validate email format
	    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	        echo "<p class='text-danger'>Invalid email format.</p>";
	        exit;
	    }

	    // Hash the password for security
	    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

	    // Check if the email already exists
	    $check_query = "SELECT email FROM user_accounts WHERE email = ?";
	    $stmt = $db->prepare($check_query);
	    if (!$stmt) {
	        echo "<p class='text-danger'>Database error: " . $db->error . "</p>";
	        exit;
	    }

	    $stmt->bind_param("s", $email);
	    $stmt->execute();
	    $result = $stmt->get_result();

	    if ($result->num_rows > 0) {
	        echo "<p class='text-danger'>This email is already registered. Please use a different email.</p>";
	        $stmt->close();
	        $db->close();
	        exit;
	    }

	    // Insert new user into the database
	    $insert_query = "INSERT INTO user_accounts (username, email, password) VALUES (?, ?, ?)";
	    $stmt = $db->prepare($insert_query);
	    if (!$stmt) {
	        echo "<p class='text-danger'>Database error: " . $db->error . "</p>";
	        exit;
	    }

	    $stmt->bind_param("sss", $username, $email, $hashed_password);

	    if ($stmt->execute()) {
	        echo "<p class='text-success'>Registration successful. Redirecting to login...</p>";
	        header("Refresh: 2.5; url=login.php");
	    } else {
	        echo "<p class='text-danger'>Error registering account. Please try again.</p>";
	    }

	    $stmt->close();
	    $db->close();
	}
	require 'footer-logged-out.php';
?>
