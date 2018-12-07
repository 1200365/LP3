<?php
require_once('config.php');
session_start();
if (!isset($_SESSION['ID'])) {
    header('Location: ./main.php');
    exit();
}
$name = $_SESSION['NAME'];
$errorMessage = "";
$now = date("Y-m-d H:i:s");
// Databese
$db['host'] = DB_HOST;
$db['user'] = DB_USER;
$db['pass'] = DB_PASSWORD;
$db['dbname'] = DB_NAME;
$dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8", $db['host'], $db['dbname']);

try {
    // データベース接続
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
    
    // ユーザが参加対象のイベントをすべて出力
    // イベント名、イベント内容、開始日時、出欠回答期限
    //$dead_event = $pdo->prepare('SELECT count(*) as cnt FROM events WHERE id = ? AND event_deadline > ?');
    //$dead_event->execute(array($_SESSION['ID'], $now));
    //$val = $dead_event->fetch();
    //$dead_cnt = $val['cnt'];
    //echo $dead_cnt;
    $stmt = $pdo->prepare('SELECT event_id, event_name, event_content, event_start, event_deadline
                           FROM events, event_target
                           WHERE events.id = event_target.event_id AND event_target.id = ? AND event_deadline > ?');
    $stmt->execute(array($_SESSION['ID'], $now));
} catch(PDOException $e) {
    // エラー処理
    $errorMessage = 'データベースエラー';
}
?>
<!doctype html>
<html>
    <head>
        <title>メインメニュー画面</title>
        <meta charset="utf-8"/>
        <link rel="stylesheet" href="css/main.css" type="text/css">
    </head>
    <body>
	<div>
		<h1 align="center">イベント管理システム</h1>
		<?php echo $now;?><br>
		<h4 class="user">ようこそ<?php echo $name;?>さん</h4>
        </div>
	<div align="center">
		<?php echo $errorMessage;?>
		<?php
            // <!--管理者の場合の処理-->
            if($_SESSION['ID'] == 777777) {
                print('<div class="box2" id="admin">');
                print(
                    //ユーザ登録、ユーザ変更
                    "<br>管理者メニュー<br><br>
                    <form>
                    <input type='button' onClick=\"location.href='./add_user/signup.php'\" value='利用者登録'/><br>
                    <input type='button' onClick=\"location.href='./change_user/select.php'\" value='利用者変更'/><br>
                    </form>"
                );
                print('</div>'); 
		    }
		?>
            <div class="box" id="event">
                <p><!-- 予約されているイベント一覧<br> -->
                    <table border="1">
                        <tr><th>イベント名</th><th>イベント内容</th><th>イベント開始日時</th><th colspan="2">出欠回答制限</th></tr>
                        <!--<p class="box" id="join">-->
                            <?php
                                while ($value = $stmt->fetch()) {
                                    $event_id = $value['event_id'];
                                    $name = $value['event_name'];
                                    $contnt = $value['event_content'];
                                    $start = $value['event_start'];
                                    $deadline = $value['event_deadline'];
                                    print("
                                        <form method='post' action='join.php'>
                                            <tr><td>{$name}</td><td>{$contnt}</td><td>{$start}</td><td>{$deadline}</td>
                                            <td><button type='submit' name='action' value={$event_id}>出席回答</button></td></tr>
                                        </form>
                                    ");

                                }
                            ?>
                        <!--</p>-->
                    </table>
                </p>
            </div>
    
            <!--ユーザメニュー-->
            <div class="box" id="up-right">
                <h4>メニュー<br></h4>
                    <p><input type="button" onClick="location.href='./reserve.php'" value="イベント予約" style="WIDTH:150px; HEIGHT:30px"></p>
                    <p><input type="button" onClick="location.href='./change_event_list.php'" value="イベント変更" style="WIDTH:150px; HEIGHT:30px"></p>
                    <p><input type="button" onClick="location.href='./del.php'" value="イベント削除" style="WIDTH:150px; HEIGHT:30px"></p>
                    <p><input type="button" onClick="location.href='./log.php'" value="イベントログ" style="WIDTH:150px; HEIGHT:30px"></p><br>
                    <p><input type="button" onClick="location.href='./signout.php'" value="ログアウト" style="WIDTH:150px; HEIGHT:30px"></p>
            </div>
        </div>
    </body>
</html>
