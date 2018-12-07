<?php
require_once('../config.php');
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
    
    //studentsテーブルからユーザ情報を取得
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id not in (\'777777\') ORDER BY status, id');
    $stmt->execute();
} catch(PDOException $e) {
    // エラー処理
    $errorMessage = 'データベースエラー';
}
?>
<!doctype html>
<html>
    <head>
    	<title>利用者一覧画面</title>
		<meta charset="utf-8"/>
        <link rel="stylesheet" href="../css/select.css" type="text/css">
    </head>
    <body>    
        <!--規定画面-->
        <p id="back_main"><input type="button" onClick="location.href='../main.php'" value="メイン画面に戻る" style="WIDTH:150px; HEIGHT:30px"></p>
        <center><h1>イベント管理システム</h1></center>
        <div>      
        <h4 class="user">ようこそ<?php echo $name;?>さん</h4>
        </div>
        <div align="center">
            <br><h3>利用者一覧</h3><br>
            <table align="center" border="1">
                <tr><td>氏名</td><td>不在学情報</td></tr>
                <?php
                    while ($value = $stmt->fetch()) {
                        $username = $value['name'];
                        $userid = $value['id'];
                        $status = $value['status'];
                        if($status == 0){$now = "在学";}
                        if($status == 1){$now = "休学";}
                        if($status == 2){$now = "卒業";}
                        print("
                        <form form method ='post' action='change_user.php'>
                            <tr><td><input type='submit' value={$username} name='username'></td><td>{$now}</td></tr>
                            <input type='hidden' value={$userid} name='userid'>
                        </form>  
                        ");
                    }
                ?>
            </table>
        </div>
    </body>
</html>
