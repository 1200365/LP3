<?php

session_start();
if (!isset($_SESSION['ID'])) {
header('Location: ./main.php');
exit();
}

$userid = $_SESSION['ID'];
$dbname = 'ems_db';	#データベース名
$host = 'localhost';	#ホスト
$username = 'yoko';	#ユーザ名
$password = 'yama';	#パスワード
$dns = 'mysql:dbname='.$dbname.';host='.$host.';charset=utf8';

#データベースに接続
try {
$pdo = new PDO($dns, $username, $password,
array(PDO::ATTR_EMULATE_PREPARES => false));
} catch (PDOException $e) {
exit('データベースとの接続に失敗しました。'.$e->getMessage());
}

#データの抽出
#一覧で表示させるデータを取り出す
try {
if($userid == 777777) {
$stmt = $pdo->prepare('SELECT * FROM events');
$stmt->execute();
} else {
$stmt = $pdo->prepare('SELECT * FROM events WHERE event_applicant = ?');
$stmt->execute(array($userid));
}

} catch (PDOException $e) {
exit('データベースの抽出に失敗しました。'.$e->getMessage());
}
?>

<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>変更可能イベント一覧</title>
</head>
<body>
<center>
<h1>変更可能イベント一覧</h1>
</center>
<div id="event">
<a href="./main.php">メインメニューへ戻る</a>
<table>
<tr><th>イベント名</th><th>イベント内容</th><th>イベント開始日時</th><th>出欠回答期限</th><th>イベント申請者</th><tr>
<?php
while ($value = $stmt->fetch()) {
$event_id = $value['id'];
$name = $value['event_name'];
$content = $value['event_content'];
$start = $value['event_start'];
$deadline = $value['event_deadline'];
$applicant = $value['event_applicant'];
if (strtotime(date('Y/m/d H:i:s')) < strtotime($start)) {
print("<form method = 'POST' action = change_event.php><tr><td>{$name}</td><td>{$content}</td><td>{$start}</td><td>{$deadline}</td><td>{$applicant}</td><td><button type = 'submit' name = 'id' value = {$event_id}>変更</button></td></tr></form>");
}}
?> 
</table>
</div>
</body>
</html>
