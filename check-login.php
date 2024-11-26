<?php

if (isset($_POST['name']) || isset($_POST['password'])) {
    if (!isset($_POST['name']) || empty($_POST['name'])) {
        echo "Name not supplied";
        return false;
    }
    if (!isset($_POST['password']) || empty($_POST['password'])) {
        echo "Password not supplied";
        return false;
    }

    require('db-connection.php');
    $name = $_POST['name'];
    $password = $_POST['password'];

    $query = "SELECT user_ID 
              FROM user_accounts 
              WHERE username = ? AND password = SHA2(?, 256)";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("ss", $name, $password);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $stmt->close();

    if (!$result || $result->num_rows === 0) {
        echo "Couldn't check credentials or no matching user found.";
        $db->close();
        exit;
    }

    $user = $result->fetch_assoc();

    if ($user) {
        $_SESSION['valid-user'] = $name;
        $_SESSION['id-user'] = $user['user_ID'];
        $db->close();
        return true;
    } else {
        echo "<b>Username and Password Incorrect.</b><br>";
        $db->close();
        return false;
    }
}
return false;
?>
