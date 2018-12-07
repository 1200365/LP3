<?php
require_once('config.php');
session_start();
if (!isset($_SESSION['ID'])) {
    header('Location: ./main.php');
    exit();
}
$name = $_SESSION['NAME'];

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
    $stmt = $pdo->prepare('SELECT event_id, event_name, event_content, event_start, event_deadline
                           FROM events, event_target
                           WHERE events.id = event_target.event_id AND event_target.id = ?');
    $stmt->execute(array($_SESSION['ID']));
} catch(PDOException $e) {
    // エラー処理
    $errorMessage = 'データベースエラー';
}


?>