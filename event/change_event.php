<?php                                                                                                                         
session_start();
//エラーメッセージの初期化
$errorMessage ="";

if(!isset($_SESSION['ID'])) {
header('Location: ./main.php');
exit();
}
//$check 0:登録画面 1:確認画面
$check = 0;
$event_id = $_POST['id'];			
$dbname = 'ems_db';	#データベース名
$host = 'localhost';	#ホスト
$username = 'yoko';	#ユーザ名
$password = 'yama';	#パスワード
$dns = 'mysql:dbname='.$dbname.';host='.$host.';charset=utf8';

#データベースに接続
try {
$pdo = new PDO($dns, $username, $password,
array(PDO::ATTR_EMULATE_PREPARES => false));
} catch (PDOException $e) {
exit('データベースとの接続に失敗しました。'.$e->getMessage());
}

#データの抽出
try{
$stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
$stmt->execute(array($event_id));
} catch (PDOException $e) {
exit('データベースの抽出に失敗しました。'.$e->getMessage());
}

$e_check = array('event_name', 'event_content', 'event_start', 'event_finish', 'event_deadline');
 

$value = $stmt->fetch();
if(!empty($_POST['event_name'])) {
$name = $_POST['event_name'];
} else { $name = $value[1]; }

if(!empty($_POST['event_content'])) {
$content = $_POST['event_content'];
} else { $content = $value[5]; }

if(!empty($_POST['event_start'])) {
$start = $_POST['event_start'];
} else { $start = $value[3]; }

if(!empty($_POST['event_finish'])) {
$finish = $_POST['event_finish'];
} else { $finish = $value[4]; }

if(!empty($_POST['event_deadline'])) {
$deadline = $_POST['event_deadline'];
} else { $deadline = $value[6]; }


if (isset($_POST['student_name']) && is_array($_POST['student_name'])) {
//foreach ($_POST['student_name'] as $v) {
//if ($v != null) {
//$stmt = $pdo->prepare('SELECT name FROM students WHERE id = ?');
//$stmt->execute(array($v));
//echo $v;

//$student_name_name[] = $stmt;
//}
//}

$student_name_char = implode("、", $_POST["student_name"]);
$student_name = $_POST["student_name"];
$_SESSION['student_name'] = $student_name;
}

$m_first = $value['7'];
$m_second = $value['8'];

try {
$stmt = $pdo->prepare('SELECT id, name FROM students WHERE id != 777777');
$stmt->execute();

} catch (PDOException $e) {
exit('データベースの抽出に失敗しました。'.$e->getMessage());
}

//画面遷移
//入力確認へを押したとき確認画面へ遷移
if(isset($_POST["change"])) {
$check = 1;

//入力項目が全て埋まっていない場合エラー
foreach ($e_check as $val) {
if (empty($_POST[$val]) || empty($student_name_char)) {
$errorMessage .= "全ての項目を埋めてください<br>" ;
$check = 0;
break;
}}

//イベント開始日時が正しくない場合エラー
if(strtotime(date('Y/m/d H:i:s')) >= strtotime($start)) {
$errorMessage .= "イベント開始日時を正しく設定してください<br>";
$check = 0;
}

//イベント終了日時が正しくない場合エラー
if(strtotime(date('Y/m/d H:i:s')) >= strtotime($finish) || strtotime(date($start)) >= strtotime($finish)) {
$errorMessage .= "イベント終了日時を正しく設定してください<br>";
$check = 0;
}

//出欠登録期限日時が正しくない場合エラー
if(strtotime(date('Y/m/d H:i:s')) >= strtotime($deadline) || strtotime(date($deadline)) >= strtotime($start)) {
$errorMessage .= "出欠登録期限日時を正しく設定してください<br>";
$check = 0;
}
}


//イベント変更へ戻るを押したときイベント変更入力画面へ遷移
if(isset($_POST["back"])) {
$check = 0;
}

//データベースのアップデート
//変更するを押したときデータベースを更新する
if(isset($_POST["last_change"])){
try {

//イベントの内容の変更
$stmt = $pdo->prepare("UPDATE events SET event_name=?, event_start=?, event_finish=?, event_content=?, event_deadline=? WHERE id=?");
$stmt->execute(array($name, $start, $finish, $content, $deadline, $event_id));

//イベント参加対象者の削除
$stmt = $pdo->prepare("DELETE FROM event_target WHERE event_id=?");
$stmt->execute(array($event_id));

//イベント参加対象者の追加
$passed_array = $_SESSION['student_name'];//$_POST['input_name'];
foreach($passed_array as $value) {
if($value != null){
try {
$stmt = $pdo->prepare("INSERT INTO event_target(event_id,id) values(?,?)");
$stmt->execute(array($event_id, $value));

} catch (PDOException $e) {
exit('データベースの抽出に失敗しました。'.$e->getMessage());
}}}


header('Location: ./change_event_last.php');

} catch (PDOException $e) {
exit('データベースの更新に失敗しました。'.$e->getMessage());
}}

?>

<!doctype html>
<!-- イベント変更画面の表示 -->
<html>
<?php
if($check == 0) {
?>
<head>
<meta charset="UTF-8">
<title>イベント変更</title>
</head>
<body>
<center>
<h1>イベント変更</h1>
<h2><?php echo $errorMessage; ?></h2><br>
</center>
<div id="event">
<a href="./main.php">メインメニューへ戻る</a>
<center>

<form action="" method="POST">

<label>イベント名：</label>
<input type="text" id="event_name" name="event_name" value="<?php echo $name; ?>"><br>
<label>イベント内容：</label><br>
<textarea id="event_content" name="event_content" rows="4" cols="50"><?php echo $content; ?></textarea><br>
<label>イベント開始日時：</label>
<input type="text" id="event_start" name="event_start" value="<?php echo $start; ?>"><br>
<label>イベント終了日時：</label>
<input type="text" id="event_finish" name="event_finish" value="<?php echo $finish; ?>"><br>
<label>イベント参加対象者：</label><br>
<?php
while ($value = $stmt->fetch()) {
$student = $value['name'];
print("<input type='checkbox' name='student_name[]' value='{$value['id']}'>{$student}");
}
?><br>
<label>出欠登録期限日時：</label>
<input type="text" id="event_deadline" name="event_deadline" value="<?php echo $deadline; ?>"><br>
<input type="submit" id="change" name="change" value="入力確認へ">
<input type="hidden" id="id" name="id" value="<?php echo $event_id; ?>">
</form>
<form action="change_event_list.php">
<input type="submit" value="変更可能イベント一覧へ戻る">
</form>
</div>
</body>
<?php
}
?>

<!-- 確認画面の表示 -->
<?php
if($check == 1) {
?>

<head>
<meta charset="UTF-8">
<title>イベント変更</title>
</head>
<body>
<center>
<h1>変更内容確認</h1>
<h2><?php echo $errorMessage; ?></h2>
</center>
<div id="event">
<a href="./main.php">メインメニューへ戻る</a>
<center>
<b>イベント名</b>：<?php echo $name; ?><br>
<b>イベント内容</b>：<?php echo $content; ?><br>
<b>イベント開始日時</b>：<?php echo $start; ?><br>
<b>イベント終了日時</b>：<?php echo $finish; ?><br>
<b>イベント参加対象者</b> <?php echo $student_name_char; ?><br>
<b>出欠登録期限日時</b>：<?php echo $deadline; ?><br>
この内容で変更してよろしいですか？
<form action="" method=POST>
<input type="submit" id="back" name="back" value="イベント変更に戻る">
<input type="submit" id="last_change" name="last_change" value="変更する">
<input type="hidden" id="id" name="id" value="<?php echo $event_id; ?>">
<input type="hidden" id="event_name" name="event_name" value="<?php echo $name; ?>">
<input type="hidden" id="event_content" name="event_content" value="<?php echo $content; ?>">
<input type="hidden" id="event_start" name="event_start" value="<?php echo $start; ?>">
<input type="hidden" id="event_finish" name="event_finish" value="<?php echo $finish; ?>">
<input type="hidden" id="event_deadline" name="event_deadline" value="<?php echo $deadline; ?>">
<!--<input type="hidden" name="input_name[]" value="<?php //echo $_POST['student_name']; //htmlentities(serialize($student_name));//$_POST['student_name']; ?>"> -->
</form>
</div>
</body>
<?php
}
?>
</html>






