<?php
echo <<<END
<h1>Please log in to continue to the site.</h1>
<form method="post" action="home.php">
<p>Username: <input type="text" name="name"></p>
<p>Password: <input type="password" name="password"></p>
<p><input type="submit" name="submit" value="Log In"></p>
</form>
END;
require 'footer-logged-out.php';
?>