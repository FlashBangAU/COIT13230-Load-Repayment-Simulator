<script src="js/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/custom.css">
<script src="js/bootstrap.min.js"></script>

<?php
echo <<<END
<h1>Please log in to continue to the site.</h1>
<form method="post" action="home.php">
	<p>Username: <input type="text" name="name" class="form-control"></p>
	<p>Password: <input type="password" name="password" class="form-control"></p>
	<p><input type="submit" name="submit" class="btn btn-primary" value="Log In"></p>
</form>
END;
require 'footer-logged-out.php';
?>