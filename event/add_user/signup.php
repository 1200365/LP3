<?php
session_start();
//$check 0：登録画面 1：確認画面
$check = 0;
//エラーメッセージの初期化
 $errorMessage ="";


//特殊文字のキャンセルする関数h
function h($s){
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

//POST通信が行われたときに定義する
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $userid = $_POST['userid'];
    $grade = $_POST['grade'];
    $name = $_POST['name'];
    $mail = $_POST['mail'];
    $pswd = password_hash($_POST['pass'], PASSWORD_DEFAULT);
    $regester = date("Y-m-d H:i:s");
}



//～～～～～～～データベース追加の部分～～～～～～～～
//「登録」を押したとき
if(isset($_POST['signup'])){
    $db_name = "mysql:dbname=ems_db;host-localhost";
    $db_username = "yoko";
    $db_password = "yama";
    try {
        $db = new PDO($db_name, $db_username, $db_password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //学籍番号の重複確認
        $stmt = $db->prepare("SELECT COUNT(*) FROM students WHERE id = :id");
        $stmt->bindValue(":id", $userid, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        if($count > 0){
            $errorMessage = "この学籍番号はすでに登録されています";
            $chekc = 0; //登録画面にする
        }else{
            //データベースに入力情報を追加
            $stmt = $db->prepare( "INSERT INTO students(id, name, password, mail, grade, status, register, change_date) VALUES (:id, :name, :pass, :mail, :grade, 0, :regester, null);");
            $stmt->bindValue(":id", $userid, PDO::PARAM_INT);
            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":pass", $pswd, PDO::PARAM_STR);
            $stmt->bindParam(":mail", $mail, PDO::PARAM_STR);
            $stmt->bindParam(":grade", $grade, PDO::PARAM_STR);
            //$stmt->bindValue(":frage", 0, PDO::PARAM_INT);
            $stmt->bindValue(":regester", $regester, PDO::PARAM_STR);
            //$stmt->bindValue(":change_date", $change_date, PDO::PARAM_STR);
            $stmt->execute();
            header('Location: ./completion.php');
            exit();
        }
    } catch(PDOException $e){
        echo $e->getMessage();
        exit();
    }
}

//～～～～～～～画面遷移関連～～～～～～～～～
//「次へ」を押したとき確認画面に移動
if(isset($_POST["add_user"])){
    $check = 1; 
    
    //パスワードの長さが6文字未満なら入力画面へ
    if(!empty($_POST['pass']) && strlen($_POST['pass']) < 6){
        $errorMessage .= "パスワードを6文字以上にしてください<br>";
        $check = 0; //登録画面にする
    }
    if(empty($_POST['pass']) || empty($_POST['nextpass']) && $_POST['pass']!=$_POST['nextpass']){
        $check = 0;//同じ入力ではない時
        $errorMessage .= "確認用パスワードが違います<br>";
    }
    //学籍番号を7桁にしてください
    if(!empty($_POST['userid]']) || strlen($_POST['userid'])<7){
        $errorMessage .= "学籍番号を7のにしてください<br>";
        $check = 0; //登録画面にする
    }
    //学籍番号を数字にしてください
    if(!empty($_POST['userid]']) || !ctype_digit($_POST['userid'])){
        $errorMessage .= "学籍番号を数字で入力してください<br>";
        $check = 0; //登録画面にする
    }

    //全て空のときかつエラーメッセージが空じゃなければエラー文を付ける
    if(empty($_POST['userid']) || empty($_POST['grade']) || empty($_POST['name']) || empty($_POST['mail']) || empty($_POST['pass']) || empty($_POST['nextpass'])){
        $errorMessage .= "全てを入力してください";
        $check = 0; //登録画面にする
    }
}

//「変更する」を押したとき入力画面に戻る
if(isset($_POST["back"])){
    $check = 0;
}


?>
<!doctype html>
<html>
    <!--　～～～～～～～～～～～～～～～～利用者登録画面の表示～～～～～～～～～～～～～～～～～ -->
    <?php
    if($check == 0 ) {
    ?>
    <head>
    <title>利用者登録</title>
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
                        <tr><td>学籍番号</td><td><input type="text" name="userid" size="15" placeholder="学籍番号7桁" value="<?php if(!empty($_POST['userid'])){echo h($_POST['userid']);} ?>"></td>
                        <td>学年</td><td>
                        <select name="grade" size="1">
                            <option value="B1" <?php if(!empty($_POST['grade']) && $_POST['grade'] == "B1"){echo "selected";} ?>>学士1年</option>
                            <option value="B2" <?php if(!empty($_POST['grade']) && $_POST['grade'] == "B2"){echo "selected";} ?>>学士2年</option>
                            <option value="B3" <?php if(!empty($_POST['grade']) && $_POST['grade'] == "B3"){echo "selected";} ?>>学士3年</option>
                            <option value="B4" <?php if(!empty($_POST['grade']) && $_POST['grade'] == "B4"){echo "selected";} ?>>学士4年</option>
                            <option value="M1" <?php if(!empty($_POST['grade']) && $_POST['grade'] == "M1"){echo "selected";} ?>>修士1年</option>
                            <option value="M2" <?php if(!empty($_POST['grade']) && $_POST['grade'] == "M2"){echo "selected";} ?>>修士2年</option>
                            <option value="D1" <?php if(!empty($_POST['grade']) && $_POST['grade'] == "D1"){echo "selected";} ?>>博士1年</option>
                            <option value="D2" <?php if(!empty($_POST['grade']) && $_POST['grade'] == "D2"){echo "selected";} ?>>博士2年</option>
                            <option value="D3" <?php if(!empty($_POST['grade']) && $_POST['grade'] == "D3"){echo "selected";} ?>>博士3年</option>
                            <option value="TT" <?php if(!empty($_POST['grade']) && $_POST['grade'] == "TT"){echo "selected";} ?>>教授</option>
                        </select>
                        </td></tr>
                        <tr><td>氏名</td><td colspan="3"><input type="text" name="name" placeholder="フルネーム" value="<?php if(!empty($_POST['name'])){echo h($_POST['name']);}  ?>" size="51"></td></tr>
                        <tr><td>パスワード</td><td colspan="3"><input type="password" name="pass" placeholder="パスワード" value="<?php if(!empty($_POST['pass'])){echo h($_POST['pass']);}  ?>" size="51"></td></tr>
                        <tr><td>確認用パスワード</td><td colspan="3"><input type="password" name="nextpass" placeholder="もう一度" value="<?php if(!empty($_POST['nextpass'])){echo h($_POST['nextpass']);}  ?>" size="51"></td></tr>
                        <tr><td>メールアドレス</td><td colspan="3"><input type="text" name="mail" placeholder="メールアドレス" value="<?php if(!empty($_POST['mail'])){echo h($_POST['mail']);} ?>" size="51"></td></tr>
                    </table>
                    <br><input type="submit" value="次へ" id="add_user" name="add_user" style="WIDTH:150px; HEIGHT:30px">
                </form>
                <br>
            </div>
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
            <div align="center">
                <br><h2><b>この内容でよろしいですか？</b></h2>
                <br><h2><?php echo $errorMessage; ?></h2>
                <div class="box_gray">
                    <h2>利用者登録内容</h2>
                    <br>
                    <form id="check_user" name="check_user" action="" method="POST">
                    <!--登録を押したとき-->
                        <table>
                            <tr><td>学籍番号</td><td><?php echo $_POST['userid']; ?></td><td>学年</td><td><?php 
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
                            <tr><td>氏名</td><td colspan="3"><?php echo $_POST['name']; ?></td></tr>
                            <tr><td>パスワード</td><td colspan="3">表示されません</td></tr>
                            <tr><td>メールアドレス</td><td colspan="3"><?php echo $_POST['mail']?></td></tr>
                        </table>
                        <!--次の場所に伝えるための情報-->
                        <input type="hidden" id="userid" name="userid" size="15" value= "<?php echo $_POST['userid']; ?>"> <input type="hidden" id="grade" name="grade" size="15" value="<?php echo $_POST['grade']; ?>">
                        <input type="hidden" id="name" name="name" value="<?php echo $_POST['name']; ?>">
                        <input type="hidden" id="mail" name="mail" value="<?php echo $_POST['mail']; ?>">
                        <input type="hidden" name="pass" value="<?php if(!empty($_POST['pass'])){echo h($_POST['pass']);}  ?>">
                        <input type="hidden" name="nextpass" value="<?php if(!empty($_POST['nextpass'])){echo h($_POST['nextpass']);}  ?>">
                        <input type="submit" value="登録" name="signup"  style="WIDTH:70px; HEIGHT:30px;"> 
                    </form>
                    <!--変更するを押したとき-->
                    <form action="" method="POST">
                        <!--次の場所に伝えるための情報-->
                        <input type="hidden" id="userid" name="userid" size="15" value= "<?php echo $_POST['userid']; ?>"> <input type="hidden" id="grade" name="grade" size="15" value="<?php echo $_POST['grade']; ?>">
                        <input type="hidden" id="name" name="name" value="<?php echo $_POST['name']; ?>">
                        <input type="hidden" id="mail" name="mail" value="<?php echo $_POST['mail']; ?>">
                        <input type="hidden" name="pass" value="<?php echo $_POST['pass']; ?>">
                        <input type="hidden" name="nextpass" value="<?php echo $_POST['nextpass']; ?>">
                        <input type="submit" id="back" name="back" value="変更する" style="WIDTH:70px; HEIGHT:30px">
                    </form>
                    <br>
                </div>
            </div>
    </body>
    <?php
    }
    ?>
</html>