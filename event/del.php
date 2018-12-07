<?php

/* ------------
イベントを削除する 
--------------- */

require_once('config.php');
session_start();
if (!isset($_SESSION['ID'])) {
    header('Location: ./main.php');
    exit();
}
$name = $_SESSION['NAME'];
// $event_id = $_SESSION['event_id'];
// データベースの定義。定義の詳細はconfig.phpにて記載
$db['host'] = DB_HOST;
$db['user'] = DB_USER;
$db['pass'] = DB_PASSWORD;
$db['dbname'] = DB_NAME;
$dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8", $db['host'], $db['dbname']);

try {
    // データベースへの接続
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
    
    if(isset($_POST['id'])){
        // 「id = ? 」... ボタンを押したらexecuteに押したボタンの値(bodyの中にあるwhile文の「name=""」の値)が入る
        $stmt=$pdo->prepare('DELETE FROM events WHERE id = ?');
        $stmt->execute(array($_POST['id']));
        $stmt=$pdo->prepare('DELETE FROM event_target WHERE event_id = ?');
        $stmt->execute(array($_POST['id']));
    }

    // イベント情報と、イベント参加者
    $stmt = $pdo->prepare('SELECT * FROM events');
                           /*WHERE events.id = event_target.event_id AND event_target.id = ?');*/
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
        <title>イベントの削除一覧</title>
        <meta charset='UTF-8'>
        <link rel="stylesheet" href="./css/join.css" type="text/css">
    </head>
    <body>
        <?php
      
        ?>
        <h1>イベント管理システム</h1>
        <div id="event">
        <div align='center'>
                <p><!-- 予約されているイベント一覧<br> -->
                <h2>削除可能なイベント一覧</h2>
               
                <div class="box">
                <table border="1">
                    <tr><th>イベント名</th><th>イベント内容</th><th>イベント開始日時</th><th>出欠回答制限</th></tr>
                    <!--<p class="box" id="join">-->
                        <?php
                            /* IDが管理者である場合（管理者には全てのイベントが表示される）*/
                            if($_SESSION["ID"] == 777777){
                                while ($value = $stmt->fetch()) {
                                    $event_id = $value['id'];
                                    $name = $value['event_name'];
                                    $contnt = $value['event_content'];
                                    $start = $value['event_start'];
                                    $deadline = $value['event_deadline'];
                                    print("<form method='POST' action='del_cm.php'>
                                    <tr><td>{$name}</td><td>{$contnt}</td><td>{$start}</td><td>{$deadline}</td>
                                    <td><button type='submit' name='id' value={$event_id}>削除</button></td></form></tr>");
                                }
                            }else{
                                while ($value = $stmt->fetch()) {
                                    $event_id = $value['id'];
                                    $name = $value['event_name'];
                                    $contnt = $value['event_content'];
                                    $start = $value['event_start'];
                                    $deadline = $value['event_deadline'];
                                    $applicant = $value['event_applicant'];
                                    /* イベント申請者とユーザIDが一致する時、一致するイベントだけを表示 */
                                    if($_SESSION['ID'] == $applicant){
                                        print("<form method='POST' action='del_cm.php'>
                                        <tr><td>{$name}</td><td>{$contnt}</td><td>{$start}</td><td>{$deadline}</td>
                                        <td><button type='submit' name='id' value={$event_id}>削除</button></td></form></tr>");
                                    }
                                }
                            }
                        ?>
                    <!--</p>-->
                    </table>
                    <a href="./main.php">メインメニューへ戻る</a>
                </p>
            </div>
    </body>
</html>