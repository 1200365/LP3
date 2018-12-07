<?php

$db_id = "yoko";
$db_pw = "yama";
//$db_name = "mysql:dbname=ems_db;host=localhost";
$pass = password_hash("warabi", PASSWORD_DEFAULT);

try {
	$db = new PDO($db_name, $db_id, $db_pw);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $db->prepare("INSERT INTO students VALUES (?, ?, ?, ?, ?, ?, ?, null)");
	$stmt ->execute(array(1200000, "user", $pass, "200000z@ugs.kochi-tech.ac.jp", "TestUser", 0, date("Y-m-d")));
} catch (PDOException $e) {
	echo $e->getMessage();
	exit();
}

?> 
