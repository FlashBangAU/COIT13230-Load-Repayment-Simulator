<!-- HTML Registration Form -->
<script src="js/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/custom.css">
<script src="js/bootstrap.min.js"></script>

<body>
	<div class="container d-flex flex-column min-vh-100">
		<h1 class="my-4">Create an Account</h1>
		<form method="post" action="">
		    <p>Username: <input type="text" name="name" maxlength="16" class="form-control"></p>
		    <p>Email: <input type="email" name="email" maxlength="50" class="form-control"></p>
		    <p>Password: <input type="password" name="password" maxlength="64" class="form-control"></p>
		    <p><input type="submit" name="submit" class="btn btn-primary" value="Register"></p>
		</form>

		<?php
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			    $error = '';

			    // Check if all fields are provided
			    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password'])) {
			        $error = "All fields are required.";
			    } else {
			        require('db-connection.php');

			        // Sanitize inputs
			        $username = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
			        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
			        $password = trim($_POST['password']);

			        // Validate email format
			        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			            $error = "Invalid email format.";
			        } else {
			            // Check if the email already exists
			            $check_query = "SELECT email FROM user_accounts WHERE email = ?";
			            $stmt = $db->prepare($check_query);

			            if ($stmt) {
			                $stmt->bind_param("s", $email);
			                $stmt->execute();
			                $result = $stmt->get_result();

			                if ($result->num_rows > 0) {
			                    $error = "This email is already registered. Please use a different email.";
			                } else {
			                    // Hash the password for security
			                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

			                    // Insert new user into the database
			                    $insert_query = "INSERT INTO user_accounts (username, email, password) VALUES (?, ?, ?)";
			                    $stmt = $db->prepare($insert_query);

			                    if ($stmt) {
			                        $stmt->bind_param("sss", $username, $email, $hashed_password);

			                        if ($stmt->execute()) {
			                            echo "<p class='text-success'>Registration successful. Redirecting to login...</p>";
			                            header("Refresh: 2.5; url=login.php");
			                        } else {
			                            $error = "Error registering account. Please try again.";
			                        }
			                    } else {
			                        $error = "Database error: " . $db->error;
			                    }
			                }
			                $stmt->close();
			            } else {
			                $error = "Database error: " . $db->error;
			            }
			        }
			        $db->close();
			    }

			    if ($error) {
			        echo "<p class='text-danger'>$error</p>";
			    }
			}
			require('footer-logged-out.php');
			?>
	</div>
</body>
