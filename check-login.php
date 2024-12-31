<?php
if (isset($_POST['email']) && isset($_POST['password'])) {
    if (empty($_POST['email'])) {
        echo "Email not supplied.";
        return false;
    }
    if (empty($_POST['password'])) {
        echo "Password not supplied.";
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
        echo "Failed to prepare the statement: " . $db->error;
        $db->close();
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $stmt->close();

    if (!$result || $result->num_rows === 0) {
        echo "Invalid email or password.";
        $db->close();
        exit;
    }

    $user = $result->fetch_assoc();

    // Verify the password
    if (password_verify($password, $user['password'])) {
        // Start session and set session variables
        $_SESSION['valid-user'] = $email;
        $_SESSION['username'] = $user['username'];
        $_SESSION['id-user'] = $user['user_ID'];
    } else {
        echo "Invalid email or password.";
    }

    $db->close();
}
?>
