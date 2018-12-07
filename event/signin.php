<?php
require_once('config.php');

session_start();

if (isset($_SESSION['ID'])) {
	header('Location: ./main.php');
	exit();
}

$db['host'] = DB_HOST;
$db['user'] = DB_USER;
$db['pass'] = DB_PASSWORD;
$db['dbname'] = DB_NAME;

$errorMessage = "";

if (isset($_POST['login'])) {
	if (empty($_POST['userid'])) {
		$errorMessage = '学籍番号が未入力です';
	} else if (empty($_PSOT['pass'])) {
		$errorMessage = 'パスワードが未入力です';
	}

	if(!empty($_POST['userid'])&&!empty($_POST['pass'])) {
		// 学籍番号を格納
		// 特殊文字を変換
		$userid = htmlspecialchars($_POST['userid'], ENT_QUOTES);

		// データベースの情報を格納
		$dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8", $db['host'], $db['dbname']);

		try {
			// データベース接続
			$pdo = new PDO($dsn, $db['user'], $db['pass']);
			
			// ユーザ情報を取得
			$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
			$stmt->execute(array($userid));

			if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				// 特殊文字を変換
				$pass = htmlspecialchars($_POST['pass'], ENT_QUOTES);

				//passwordが一致しているか確認
				if (password_verify($pass, $row['password'])) {
					// セッションにidを格納
					$_SESSION['ID'] = $row['id'];
					$_SESSION['NAME'] = $row['name'];
					header("Location: main.php");	// メニュー画面へ
					exit();	// 処理終了
				} else {
					$errorMessage = "パスワードに誤りがあります。";
				}
			} else {
				$errorMessage = "ユーザ情報がないです。";
			}
		} catch(PDOException $e) {
			// エラー処理
			$errorMessage = 'データベース接続エラー';
		}
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>ログイン画面</title>
		<link rel="stylesheet" href="css/signin.css">
	</head>
	<body>
		<div id="contents">
			<center>
				<div id="main">
					<div>
						<h1>イベント管理システム</h1>
					</div>
					<div>
						<form id="loginForm" name="loginForm" action="" method="post">
								<div><font color="#ff0000"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></font></div>
								<label for="userid">学籍番号: </label><input id="userid" type="text" name="userid" placeholder="学籍番号を入力" value="<?php if(!empty($_POST['userid'])) echo htmlspecialchars($_POST['userid'], ENT_QUOTES); ?>">
								<br>
								<label for="pass">パスワード: </label><input id="pass" type="password" name="pass" placeholder="パスワードを入力">
								<br>
								<input type="submit" id="login" name="login" value="ログイン">
						</form>
					</div>
				</div>
			</center>
		</div>
	</body>

</html>
