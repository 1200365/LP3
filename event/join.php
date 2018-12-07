<?php
require_once('config.php');
session_start();
if (!isset($_SESSION['ID'])) {
    header('Location: ./main.php');
    exit();
}
$name = $_SESSION['NAME'];
if (isset($_POST['action'])) {
    $_SESSION['event_id'] = $_POST['action'];
}
$event_id = $_SESSION['event_id'];

// Databese
$db['host'] = DB_HOST;
$db['user'] = DB_USER;
$db['pass'] = DB_PASSWORD;
$db['dbname'] = DB_NAME;
$dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8", $db['host'], $db['dbname']);

try {
    // データベース接続
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
} catch(PDOException $e) {
    // エラー処理
    $errorMessage = 'データベースエラー';
} 

$message = "";
if (isset($_POST['join'])) {
    $stmt = $pdo->prepare('UPDATE event_target SET state = ?, register_date = ? WHERE event_id = ? AND id = ?');
    $stmt->execute(array($_POST['join'], date("Y-m-d"), $event_id, $_SESSION['ID']));
    if ($_POST['join']) {
        $message = "＜出席＞登録しました。";
    } else {
        $message = "＜欠席＞登録しました。";
    }
}
?>

<!doctype html>
<html>
    <head>
        <title>出欠登録 | イベント管理システム</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="./css/join.css" type="text/css">
    </head>
    <body>
        <div>
            <h1>イベント管理システム</h1>
            <div>
                ようこそ<?php echo $name ?>さん
            </div>
            <div class="main">
                <!-- イベント情報を表示する -->
		<table border="1">
			<tr><th>イベント名</th><th>イベント内容</th><th>開始日時</th><th>終了日時</th><th>出欠登録締切</th></tr>
                	<?php
                    	$stmt = $pdo->prepare('SELECT event_name, event_content, event_start, event_finish, event_deadline FROM events WHERE id = ?');
                    	$stmt->execute(array($event_id));
                    	$value = $stmt->fetch();
                    	print("<tr><td>{$value['event_name']}</td>
				<td>{$value['event_content']}</td>	
				<td>{$value['event_start']}</td>
				<td>{$value['event_finish']}</td>
				<td>{$value['event_deadline']}</td></tr>");
                	?>
                </table>
                <div class="join">
                    <!-- 出欠登録フォーム -->
                    このイベントに出席しますか？
                    <form method='post' action='join.php'><p>
                        <input type="radio" name="join" value="1">出席
                        <input type="radio" name="join" value="0">欠席
                        <br><input type="submit" value="送信">
                    </p></form>
                </div>
                <div class="member">
                    <!-- イベント参加者 -->
                    イベント参加者出欠状況<br>
                    <p>
			<table align="center">
                        <?php
                            $stmt = $pdo->prepare('SELECT students.name as name, event_target.state as state FROM students, events, event_target 
                                                   WHERE students.id = event_target.id AND events.id = event_target.event_id AND events.id = ?');
                            $stmt->execute(array($event_id));
                            while ($value = $stmt->fetch()) {
                                print("<tr><td>{$value['name']}</td><td>：</td><td>");
                                if ($value['state'] == null) {
                                    print("未回答");
                                } elseif ($value['state']) {
                                    print("出席");
                                } else {
                                    print("欠席");
                                }
				print("</td></tr>");
                            }

                        ?>
			</table>
                    </p>
                </div>
                <p><?php echo $message;?></p>
            </div>
            <p>
                <a href="./main.php">メインメニューへ戻る</a>
            </p>
        </div>
    </body>
</html>
