<script src="js/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/custom.css">
<script src="js/bootstrap.min.js"></script>

<?php
echo <<<END
<div class="container d-flex flex-column min-vh-100">
    <h1 class="my-4">Please log in to continue to the site.</h1>
    <form method="post" action="check-login.php"> <!-- Changed to check-login.php -->
        <p>Email: <input type="text" name="email" maxlength="50" class="form-control"></p>
        <p>Password: <input type="password" name="password" maxlength="64" class="form-control"></p>
        <p>
            <input type="submit" name="submit" class="btn btn-primary" value="Log In">
            <a href="register-account.php" class="btn btn-warning">Register Account</a>
        </p>
    </form>
</div>
END;

require 'footer-logged-out.php';
?>
