<?php

/* ---------------------------------------
イベント削除を行う前に本当に削除するかの確認を行う 
------------------------------------------ */

require_once('config.php');
session_start();
if (!isset($_SESSION['ID'])) {
    header('Location: ./main.php');
    exit();
}
$id = $_POST['id'];


$db['host'] = DB_HOST;
$db['user'] = DB_USER;
$db['pass'] = DB_PASSWORD;
$db['dbname'] = DB_NAME;
$dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8", $db['host'], $db['dbname']);

try {
    // データベースへの接続
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
    
    // イベント情報と、イベント参加者
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
                           /*WHERE events.id = event_target.event_id AND event_target.id = ?');*/
    //$stmt->execute(array($_SESSION['ID']));
    $stmt->execute(array($id));
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
        <div align='center'>
            <h1>イベント管理システム</h1>

        <!-- 削除確認画面 -->
        <?php
            $value = $stmt->fetch();
            $name = $value['event_name'];
            $contnt = $value['event_content'];
            $start = $value['event_start'];
            $deadline = $value['event_deadline'];
            print("{$name}:{$contnt}:{$start}<br>");

            print("このイベントを削除しますか？<br>");

            print("<form method='POST' action='./del.php'>
            <button type='submit' name='id' value={$id}>はい</button></form>
            <button type='button' onClick=\"location.href='del.php'\">いいえ</button>");
        ?>
        </div>
    </body>
</html>