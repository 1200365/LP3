<?php
session_start();
$errorMessage ="";


?>
<!doctype html>
<html>
    <head>
    <title>利用者登録完了</title>
		<meta charset="utf-8"/>
        <link rel="stylesheet" href="../css/main.css" type="text/css">
    </head>
    <body>
        <!--規定画面-->
        <p id="back_main"><input type="button" onClick="location.href='../main.php'" value="メイン画面に戻る" style="WIDTH:150px; HEIGHT:30px"></p>
        <center><h1>イベント管理システム</h1></center>
        <br><br>
        <div align="center">
            <h2>登録が完了しました</h2>
            <h2><?php echo $errorMessage;?></h2><br>
            <br><br>
            <input type="button" onClick="location.href='../main.php'" value="メイン画面に戻る" style="WIDTH:150px; HEIGHT:30px">
        </div>
    </body>
</html>