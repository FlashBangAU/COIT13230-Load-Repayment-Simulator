<?php
	if (isset($_SESSION['valid-user'])){
		return true;
	}else{
		return false;
	}
?>