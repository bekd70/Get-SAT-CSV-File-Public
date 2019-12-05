<?php
	$servername = "xxx.xxx.xxx.xxx";
	$username = "username";
	$password = "password";
	$dbname = "externalassments";
	

	$mysqli= new mysqli($servername, $username, $password, $dbname);
	if (mysqli_connect_errno()) {
	    printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

?>
