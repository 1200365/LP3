<?php
require_once('../config.php');
session_start();
if (!isset($_SESSION['ID'])) {
    header('Location: ./main.php');
    exit();
}
//特殊文字のキャンセルする関数h
function h($s){
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

$name = $_SESSION['NAME'];
//$check 0：登録画面 1：確認画面
$check = 0;
//エラーメッセージの初期化
 $errorMessage ="";

// Databese
$db['host'] = DB_HOST;
$db['user'] = DB_USER;
$db['pass'] = DB_PASSWORD;
$db['dbname'] = DB_NAME;
$dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8", $db['host'], $db['dbname']);

//定義
//空白はデータベースから取得、他はPOST通信
try {
    // データベース接続
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
    //studentsテーブルからユーザ情報を取得
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id=:id');
    $stmt->bindValue(":id", $_POST['userid'], PDO::PARAM_INT);
    $stmt->execute();
    $value = $stmt->fetch();
    if(!empty($_POST['name'])) {
        $name = $_POST['name'];
    }else{$name = $value['name'];}

    if(!empty($_POST['userid'])) {
        $userid=$_POST['userid'];
    }else{$userid = $value['id'];}

    if(!empty($_POST['mail'])) {
        $mail=$_POST['mail'];
    }else{$mail = $value['mail'];}

    if(!empty($_POST['grade'])) {
        $grade=$_POST['grade'];
    }else{$grade = $value['grade'];}

    if(!empty($_POST['status'])) {
        $status=$_POST['status'];
    }else{$status = $value['status'];}
    
    if(!empty($_POST['pass'])){
    $pswd = password_hash($_POST['pass'], PASSWORD_DEFAULT);
    }
    $register = $value['register'];
    $change_date = date("Y-m-d H:i:s");
} catch(PDOException $e) {
    // エラー処理
    $errorMessage = 'データベースエラー';
}

//～～～～～～～データベース追加の部分～～～～～～～～
//「変更」を押したとき
if(isset($_POST['signup'])){
    $db_name = "mysql:dbname=ems_db;host-localhost";
    $db_username = "yoko";
    $db_password = "yama";
    try {
        $db = new PDO($db_name, $db_username, $db_password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        //データベースに入力情報を追加
        $stmt = $db->prepare( "UPDATE students  SET name=?, mail=?, grade=?, status=?, register=?, change_date=? WHERE id=?");
        $stmt->execute(array($name, $mail, $grade, $status, $register, $change_date, $userid));
        if(!empty($_POST['pass'])){
             $stmt = $pdo->prepare('UPDATE students SET password=? WHERE id = ?');
             $stmt->execute(array($pswd, $userid));
        }
        header('Location: ./completion.php');
        exit();
    
    } catch(PDOException $e){
        echo $e->getMessage();
        exit();
    }
}


//～～～～～～～画面遷移関連～～～～～～～～～
//「次へ」を押したとき確認画面に移動
if(isset($_POST["add_user"])){
    $check = 1; 
}
if((!empty($_POST['pass']) || !empty($_POST['nextpass'])) && $_POST['pass']!=$_POST['nextpass']){
    $check = 0;//パスワードがどちらかに入っていて、かつ同じ入力ではない時
    $errorMessage .= "パスワードを確認用と同じにするか、両方を空白にしてください<br>";
    echo $_POST['pass'];
    echo $_POST['nextpass'];
}
//「変更する」を押したとき入力画面に戻る
if(isset($_POST["back"])){
    $check = 0;
}

//パスワードの長さが6文字未満なら入力画面へ
if(!empty($_POST['pass']) && strlen($_POST['pass']) < 6 ){
    $errorMessage .= "パスワードを6文字以上にしてください<br>";
    $check = 0; //登録画面にする
}

?>
<html>
    <!--　～～～～～～～～～～～～～～～～利用者変更画面の表示～～～～～～～～～～～～～～～～～ -->
    <?php
    if($check == 0 ) {
    ?>
    <head>
    <title>利用者変更</title>
		<meta charset="utf-8"/>
        <link rel="stylesheet" href="../css/main.css" type="text/css">
    </head>
    <body>
        <!--規定画面-->
        <p id="back_main"><input type="button" onClick="location.href='../main.php'" value="メイン画面に戻る" style="WIDTH:150px; HEIGHT:30px"></p>
        <center><h1>イベント管理システム</h1></center>
        <div align="center">
            <h2><?php echo $errorMessage; ?></h2><br>
            <div class="box_gray">
                <br>
                <form action="" method="POST">
                    <table>
                        <tr><td>学籍番号</td><td><input type="hidden" name="userid" size="15" placeholder="学籍番号" value="<?php if(!empty($userid)){echo h($userid);} ?>"><?php echo h($userid)?></td>
                        <td>学年</td><td>
                        <select name="grade" size="1">
                            <option value="B1" <?php if(!empty($grade) && $grade == "B1"){echo "selected";} ?>>学士1年</option>
                            <option value="B2" <?php if(!empty($grade) && $grade == "B2"){echo "selected";} ?>>学士2年</option>
                            <option value="B3" <?php if(!empty($grade) && $grade == "B3"){echo "selected";} ?>>学士3年</option>
                            <option value="B4" <?php if(!empty($grade) && $grade == "B4"){echo "selected";} ?>>学士4年</option>
                            <option value="M1" <?php if(!empty($grade) && $grade == "M1"){echo "selected";} ?>>修士1年</option>
                            <option value="M2" <?php if(!empty($grade) && $grade == "M2"){echo "selected";} ?>>修士2年</option>
                            <option value="D1" <?php if(!empty($grade) && $grade == "D1"){echo "selected";} ?>>博士1年</option>
                            <option value="D2" <?php if(!empty($grade) && $grade == "D2"){echo "selected";} ?>>博士2年</option>
                            <option value="D3" <?php if(!empty($grade) && $grade == "D3"){echo "selected";} ?>>博士3年</option>
                            <option value="TT" <?php if(!empty($grade) && $grade == "TT"){echo "selected";} ?>>教授</option>
                        </select>
                        </td></tr>
                        <tr><td>氏名</td><td colspan="3"><input type="text" name="name" placeholder="フルネーム" value="<?php if(!empty($name)){echo h($name);}  ?>" size="51"></td></tr>
                        <tr><td>パスワード</td><td colspan="3"><input type="password" name="pass" placeholder="パスワード(変更がない場合は空白)" value="<?php if(!empty($_POST['pass'])){echo h($_POST['pass']);}  ?>" size="51"></td></tr>
                        <tr><td>確認用パスワード</td><td colspan="3"><input type="password" name="nextpass" placeholder="もう一度" value="<?php if(!empty($_POST['nextpass'])){echo h($_POST['nextpass']);}  ?>" size="51"></td></tr>
                        <tr><td>メールアドレス</td><td colspan="3"><input type="text" name="mail" placeholder="メールアドレス" value="<?php if(!empty($mail)){echo h($mail);} ?>" size="51"></td></tr>
                        <tr><td>在籍情報</td><td colspan="3">
                        <select name="status" size="1">
                            <option value=0 <?php if(!empty($status) && $status == 0){echo "selected";} ?>>在学</option>
                            <option value=1 <?php if(!empty($status) && $status == 1){echo "selected";} ?>>休学</option>
                            <option value=2 <?php if(!empty($status) && $status == 2){echo "selected";} ?>>卒業等</option>
                        </select>
                        </td></tr>
                    </table>
                    <br><input type="submit" value="次へ" id="add_user" name="add_user" style="WIDTH:150px; HEIGHT:30px">
                </form>
                <br>
            </div>
            <br><br><input type="button" value="利用者一覧へ戻る" onClick="location.href='./select.php'" style="HEIGHT:30px"> 
        </div>
    </body>
    <?php 
    }
    ?>

    <!--　～～～～～～～～～～～～～～～登録確認画面の表示～～～～～～～～～～～～～～～～～　-->
    <?php 
    if($check == 1) {
    ?>
    <head>
    <title>利用者登録確認画面</title>
		<meta charset="utf-8"/>
        <link rel="stylesheet" href="../css/main.css" type="text/css">
    </head>
    <body>
        <!--規定画面-->
        <p id="back_main"><input type="button" onClick="location.href='../main.php'" value="メイン画面に戻る" style="WIDTH:150px; HEIGHT:30px"></p>
        <center><h1>イベント管理システム</h1></center>
        <form id="addUser" name="addUser" action="" method="post">
            <div align="center">
                <br><h2><b>この内容でよろしいですか？</b></h2>
                <h2><?php echo $errorMessage; ?></h2>
                <div class="box_gray">
                    <h2>利用者変更内容</h2>
                    <br>
                    <form id="check_user" name="check_user" action="" method="POST">
                    <!--登録を押したとき-->
                        <table>
                            <tr><td>学籍番号</td><td><?php echo $userid; ?></td><td>学年</td><td><?php 
                            if($_POST['grade'] == "B1"){echo "学士1年";} 
                            if($_POST['grade'] == "B2"){echo "学士2年";} 
                            if($_POST['grade'] == "B3"){echo "学士3年";} 
                            if($_POST['grade'] == "B4"){echo "学士4年";}
                            if($_POST['grade'] == "M1"){echo "修士1年";} 
                            if($_POST['grade'] == "M2"){echo "修士2年";} 
                            if($_POST['grade'] == "D1"){echo "博士1年";}
                            if($_POST['grade'] == "D2"){echo "博士2年";} 
                            if($_POST['grade'] == "D3"){echo "博士3年";} 
                            if($_POST['grade'] == "TT"){echo "教授";} 
                            ?></td></tr>
                            <tr><td>氏名</td><td colspan="3"><?php echo $name; ?></td></tr>
                            <tr><td>パスワード</td><td><?php if(empty($_POST['pass'])){echo "前回設定のまま";}else{echo "今回設定したもの";}?> </td></tr>
                            <tr><td>メールアドレス</td><td colspan="3"><?php echo $mail?></td></tr>
                            <tr><td>在籍情報</td><td colspan="3"><?php 
                            if($status == 0){echo "在学";} 
                            if($status == 1){echo "休学";} 
                            if($status == 2){echo "卒業等";} 
                            ?>
                            </td></tr>
                        </table>
                        <!--次の場所に伝えるための情報-->
                        <input type="hidden" id="userid" name="userid" size="15" value= "<?php echo $userid ?>"> <input type="hidden" id="grade" name="grade" size="15" value="<?php echo $grade; ?>">
                        <input type="hidden" id="name" name="name" value="<?php echo $name; ?>">
                        <input type="hidden" id="mail" name="mail" value="<?php echo $mail; ?>">
                        <input type="hidden" name="pass" value="<?php if(!empty($_POST['pass'])){echo h($_POST['pass']);}  ?>">
                        <input type="hidden" name="nextpass" value="<?php if(!empty($_POST['nextpass'])){echo h($_POST['nextpass']);}  ?>">
                        <input type="hidden" id="status" name="status" value="<?php echo $status; ?>">
                        <input type="submit" value="変更" name="signup" style="WIDTH:70px; HEIGHT:30px;"> 
                    </form>
                    <!--変更するを押したとき-->
                    <form action="" method="POST">
                        <!--次の場所に伝えるための情報-->
                        <input type="hidden" id="userid" name="userid" size="15" value= "<?php echo $_POST['userid']; ?>"> <input type="hidden" id="grade" name="grade" size="15" value="<?php echo $_POST['grade']; ?>">
                        <input type="hidden" id="name" name="name" value="<?php echo $_POST['name']; ?>">
                        <input type="hidden" id="mail" name="mail" value="<?php echo $_POST['mail']; ?>">
                        <input type="hidden" name="pass" value="<?php echo $_POST['pass']; ?>">
                        <input type="hidden" name="nextpass" value="<?php echo $_POST['nextpass']; ?>">
                        <input type="hidden" id="status" name="status" value="<?php echo $_POST['status']; ?>">
                        <input type="submit" id="back" name="back" value="前に戻る" style="WIDTH:70px; HEIGHT:30px">
                    </form>
                    <br>
                </div>
            </div>
        </form>
    </body>
    <?php
    }
    ?>
</html>