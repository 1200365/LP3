<?php
session_start();
if (isset($_SESSION['ID'])) {
	$message = "logout";
} else {
	$message = "please login";
}

// session var clear
$_SESSION = array();

// session destroy
@session_destroy();

?>

<!doctype html>
<html>
	<head>
		<title>Logout | EventManagementSystem</title>
		<meta charset='utf-8'>
	</head>
	<body>
		<div align='center'>
			<h1>イベント管理システム</h1>
			<div><?php echo $message;?></div>
			<a href='signin.php'>Signin</a><br>
			<a href='index.php'>Top</a>	
		</div>
	</body>
</html>

