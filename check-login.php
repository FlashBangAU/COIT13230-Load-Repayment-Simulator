<script src="js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it's not already active
}

if (isset($_POST['email']) && isset($_POST['password'])) {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        echo "<p class='text-danger'>Email and/or Password not supplied. Reloading Login...</p>";
        header("Refresh: 3; url=login.php");
        return false;
    }

    require('db-connection.php');

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Query to validate email
    $query = "SELECT user_ID, username, password FROM user_accounts 
              WHERE email = ?";
    
    $stmt = $db->prepare($query);

    if (!$stmt) {
        echo "<p class='text-danger'>Failed to prepare Database Statement. Reloading Login...</p>" . $db->error;;
        header("Refresh: 5; url=login.php");
        $db->close();
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $stmt->close();

    if (!$result || $result->num_rows === 0) {
        echo "<p class='text-danger'>Invalid email or password. Reloading Login...</p>";
        header("Refresh: 2.5; url=login.php");
        $db->close();
        exit;
    }

    $user = $result->fetch_assoc();

    // Verify the password
    if (password_verify($password, $user['password'])) {
        // Set session variables for the logged-in user
        $_SESSION['valid-user'] = true; // Set to a boolean value to indicate login
        $_SESSION['username'] = $user['username'];
        $_SESSION['id-user'] = $user['user_ID'];

        // Redirect to home.php after successful login
        header('Location: home.php');
        exit();
    } else {
        echo "<p class='text-danger'>Login failed. Reloading Login...</p>";
        header("Refresh: 2.5; url=login.php");
    }

    $db->close();
}
?>
