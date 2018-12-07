<?php
require_once('config.php');
session_start();
if (!isset($_SESSION['ID'])) {
    header('Location: ./main.php');
    exit();
}
$name = $_SESSION['NAME'];

// データベースの定義。定義の詳細はconfig.phpにて記載
$db['host'] = DB_HOST;
$db['user'] = DB_USER;
$db['pass'] = DB_PASSWORD;
$db['dbname'] = DB_NAME;
$dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8", $db['host'], $db['dbname']);

try {
    // データベースへの接続
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
    
    // イベント情報と、イベント参加者n
    $stmt = $pdo->prepare('SELECT * FROM events');
                           /*WHERE events.id = event_target.event_id AND event_target.id = ?');*/
                           // ?の中にexecute()を順番に入れる
    //$stmt->execute(array($_SESSION['ID']));
    $stmt->execute();
} catch(PDOException $e) {
    // エラー処理
    $errorMessage = 'データベースエラー';
}
?>
<!doctype html>
<html>
    <head>
        <title>test</title>
        <meta charset='UTF-8'>
    </head>
    <body>
        <?php
      
        ?>
        <div align='center'>
        <h1>イベント管理システム</h1>
        <div id="event">
        
                <p><!-- 予約されているイベント一覧<br> -->
                <h2>イベントログ一覧</h2>
                <table border="1">
                    <tr><th>イベント名</th><th>イベント内容</th><th>イベント開始日時</th><th>出欠回答制限</th></tr>
                    <!--<p class="box" id="join">-->
                        <?php
                            while ($value = $stmt->fetch()) {
                                $event_id = $value['id'];
                                $name = $value['event_name'];
                                $contnt = $value['event_content'];
                                $start = $value['event_start'];
                                $deadline = $value['event_deadline'];
                                print("<tr><td>{$name}</td><td>{$contnt}</td><td>{$start}</td><td>{$deadline}</td></tr>");
                            }
                        ?>
                    <!--</p>-->
                    </table>
                    <a href="./main.php">メインメニューへ戻る</a>
                </p>
            </div>
    </body>
</html>