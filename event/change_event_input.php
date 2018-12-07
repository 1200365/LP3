<?php

function createMail($title, $content, $startTime, $finishTime, $deadline) {
	$str = $title + "\n\n";
	$str += "内容\n";
	$str += $content + "\n\n";
	$str += "実施日\n";
	$str += $startTime + " ~ " + $finishTime + "\n\n";
	$str += "出欠回答締め切り\n";
	$str += $deadline;
	return $str;
}

session_start();

require('./config.php');

// page = 0 入力画面
// page = 1 確認画面
// page = 2 登録画面
$page = 0;

if (!isset($_SESSION['ID'])) {
	header('Location: ./index.php');
	exit();
} 

$name = array('event_name', 'event_content', 'date', 'start', 'finish', 'deadlineDate', 'deadlineTime', 'mile1Before', 'mile1Time', 'mile2Before', 'mile2Time');

$errorMessage = ''; 


if (isset($_POST['submit'])) {
	// 入力値を保存
	$event_name = htmlspecialchars($_POST['event_name']);
	$event_content = htmlspecialchars($_POST['event_content']);
	$date = htmlspecialchars($_POST['date']);
	$start = htmlspecialchars($_POST['start']);
	$finish = htmlspecialchars($_POST['finish']);
	$deadlineDate = htmlspecialchars($_POST['deadlineDate']);
	$deadlineTime = htmlspecialchars($_POST['deadlineTime']);
	$mile1Before = htmlspecialchars($_POST['mile1Before']);
	$mile1Time = htmlspecialchars($_POST['mile1Time']);
	$mile2Before = htmlspecialchars($_POST['mile2Before']);
	$mile2Time = htmlspecialchars($_POST['mile2Time']);
	// 入力値がすべて埋まっているか確認
	foreach ($name as $val) {
		if (empty($_POST[$val])) {
			$errorMessage = '入力項目がすべて埋まっていません';
			break;
		} 
	}
	// エラーメッセージが空か確認する
	if (empty($errorMessage)) {
		// 参加対象者がいるか確認
		if (empty($_POST['allMember'])) {
			$errorMessage = '参加対象者が存在しません';
		} else {
			$page = 1;
			if (strtotime(date('Y/m/d')) >= strtotime($date)) {
				$errorMessage = "イベント日付を正しく設定してください";
				$page = 0;
			} else if (strtotime(date('Y/m/d')) >= strtotime($deadlineDate)) {
				$errorMessage = "期限を正しく設定してください";
				$page = 0;
			} else if (strtotime($date) < strtotime($deadlineDate)) {
				$errorMessage = "イベント日付か期限を正しく設定してください";
				$page = 0;
			} else if (strtotime($start) >= strtotime($finish)) {
				$errorMessage = "イベントの時間を正しく設定してください";
				$page = 0;
			} else if (strtotime($date) == strtotime($deadlineDate) && strtotime($start) <= strtotime($deadlineTime)) {
				$errorMessage = "出欠時刻かイベント開始時刻を正しく設定してください";
				$page = 0;
			}
			if ($page) {
				$event_start = date("Y/m/d H:i:s", strtotime("${date} ${start}"));
				$event_finish = date("Y/m/d H:i:s", strtotime("${date} ${finish}"));
				$event_deadline = date("Y/m/d H:i:s", strtotime("${deadlineDate} ${deadlineTime}"));
				// マイルストンの表示の仕方を設定
				$milestone_first = date("Y/m/d", strtotime("${date} - ${mile1Before} days")) . " " . $mile1Time . ":00:00";
				$milestone_second = date("Y/m/d", strtotime("${date} - ${mile2Before} days")) . " " . $mile2Time . ":00:00";

				$milestone_first = date("Y/m/d H:i:s", strtotime($milestone_first));
				$milestone_second = date("Y/m/d H:i:s", strtotime($milestone_second));

				if (strtotime($milestone_first) > strtotime($milestone_second)) {
					$tmp = $milestone_first;
					$milestone_first = $milestone_second;
					$milestone_second = $tmp;
				}
			}
		}
	}
} else if (isset($_POST['check'])) {  // 予約確定
	// データベースの設定
	$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
	$user = DB_USER;
	$password = DB_PASSWORD;
	$page = 2;
	try {
		$pdo = new PDO($dsn, $user, $password);
	} catch (PDOException $e) {
		header('Location: ./error.php');
		exit();
	}


	$today = date('Y/m/d');

	// イベント予約
	$stmt = $pdo->prepare("INSERT INTO events (event_name, event_applicant, event_start, event_finish, event_content, event_deadline, milestone_first, milestone_second, note, register_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, ?)");
	$stmt->execute(array($_POST['event_name'], $_SESSION['ID'], $_POST['event_start'], $_POST['event_finish'], $_POST['event_content'], $_POST['event_deadline'], $_POST['milestone_first'], $_POST['milestone_second'], $today));

	// イベント対象者をテーブルに登録するため、イベントIDを取得
	$stmt = $pdo->prepare("SELECT id FROM events WHERE event_name = ? AND event_applicant = ? AND event_start = ? AND event_finish = ? AND event_content = ? AND event_deadline = ? AND milestone_first = ? AND milestone_second = ? AND register_date = ?");
	$stmt->execute(array($_POST['event_name'], $_SESSION['ID'], $_POST['event_start'], $_POST['event_finish'], $_POST['event_content'], $_POST['event_deadline'], $_POST['milestone_first'], $_POST['milestone_second'], $today));

	$id = $stmt->fetch(PDO::FETCH_ASSOC);
	//$id = (int)$id['id'];
	// イベント対象者を登録
	foreach ($_POST['allMember'] as $value) {
		$stmt = $pdo->prepare("INSERT INTO event_target (event_id, id) VALUES (?, ?)");
		$stmt->execute(array($id['id'], $value));
	}

	// メール内容
	$mailContent = createMail($_POST['event_name'], $_POST['event_content'], $_POST['event_start'], $_POST['event_finish'], $_POST['deadlineTime']);

	mb_language('Japanese');
	mb_internal_encoding('UTF-8');
	// メール送信のためメールアドレス取得
	foreach ($_POST['allMember'] as $value) {
		$stmt = $pdo->prepare("SELECT mail FROM students WHERE id = ?");
		$stmt->execute(array($value));
		$mailAddr = $stmt->fetch();

		//mail($mailAddr, "横山研究室のイベントについて", $mailContent);
	}

	$pdo = null;

} else {
	$page = 0;
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>イベント予約</title>
		<script type="text/javascript" src="./js/reserve.js"></script> 
	</head>
	<body>
		<div>
			<center>
				<div>
					<center>
						<div>
							<h3>イベント予約</h3>
						</div>
					</center>
					<!-- 確認画面 -->
					<?php if ($page == 1) { ?>
						<div>
							<form action="" method="post">
								イベント名: <strong><?php echo $event_name; ?></strong><br>
								イベント内容: <br>
								<strong><?php echo $event_content; ?></strong><br>
								イベント日付: <strong><?php echo str_replace('-', '/', $date); ?></strong><br>
								イベント開始時刻: <strong><?php echo $start; ?></strong> ～ イベント終了時刻: <strong><?php echo $finish; ?></strong><br>
								イベント参加対象者: <br>
								<?php 
								foreach ($_POST['allMember'] as $key => $value) {
									echo "<strong>${key}</strong>";
									echo "<input type=\"hidden\" name=\"allMember[]\" value=\"${value}\">";
								}
								?><br>
								出欠登録期限日: <strong><?php echo str_replace('-', '/', $deadlineDate); ?></strong><br>
								出欠登録期限時刻: <strong><?php echo $deadlineTime; ?></strong><br>
								マイルストン1: <strong><?php echo str_replace('-', '/', $milestone_first); ?></strong><br>
								マイルストン2: <strong><?php echo str_replace('-', '/', $milestone_second); ?></strong><br>
								<input type="hidden" name="event_name" value="<?php echo $event_name; ?>">
								<input type="hidden" name="event_content" value="<?php echo $event_content; ?>">
								<input type="hidden" name="date" value="<?php echo $date; ?>">
								<input type="hidden" name="start" value="<?php echo $start; ?>">
								<input type="hidden" name="finish" value="<?php echo $finish; ?>">
								<input type="hidden" name="deadlineDate" value="<?php echo $deadlineDate; ?>">
								<input type="hidden" name="deadlineTime" value="<?php echo $deadlineTime; ?>">
								<input type="hidden" name="mile1Before" value="<?php echo $mile1Before; ?>">
								<input type="hidden" name="mile1Time" value="<?php echo $mile1Time; ?>">
								<input type="hidden" name="mile2Before" value="<?php echo $mile2Before; ?>">
								<input type="hidden" name="mile2Time" value="<?php echo $mile2Time; ?>">
								<input type="hidden" name="milestone_first" value="<?php echo $milestone_first; ?>">
								<input type="hidden" name="milestone_second" value="<?php echo $milestone_second; ?>">
								<input type="hidden" name="event_start" value="<?php echo $event_start; ?>">
								<input type="hidden" name="event_finish" value="<?php echo $event_finish; ?>">
								<input type="hidden" name="event_deadline" value="<?php echo $event_deadline; ?>">
								<input type="button" value="戻る">
								<input type="submit" name="check" value="予約する">
							</form>
						</div>
					<?php } else if ($page == 2) { ?>
					<!-- 予約登録 -->
						<div>
							<center>
								登録が完了しました<br>
								<input type="button" value="メイン画面へ" onclick="location.href = 'main.php'">
							</center>
						</div>
						
					<?php } else if ($page == 0) { ?>
					<div>
						<font color="red"><?php echo $errorMessage; ?></font>
						<form action="" method="post">
							<label for="event_name">イベント名: </label>
							<input type="text" id="event_name" name="event_name" value="<?php if (!empty($event_name)) echo $event_name; ?>"><br>
							<label for="event_content">イベント内容: </label><br>
							<textarea id="event_content" name="event_content" rows="4" cols="50"><?php if (!empty($event_content)) echo $event_content; ?></textarea><br>

							<label for="date">イベント日付: </label>
							<input id="date" type="date" min="<?php echo date('Y-m-d'); ?>" name="date" value="<?php if (!empty($date)) echo $date; ?>"><br>
							<font><label for="start">イベント開始時刻: </label><input id="start" type="time" name="start" value="<?php if (!empty($start)) echo $start; ?>"> ～<label for="finish">イベント終了時刻: </label><input id="finish" type="time" name="finish" value="<?php if (!empty($finish)) echo $finish; ?>"></font><br>
							イベント参加対象者:<br>
<?php
// データベースの設定
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
$user = DB_USER;
$password = DB_PASSWORD;

try {
	$pdo = new PDO($dsn, $user, $password);
} catch (PDOException $e) {	// 例外処理
	header('Location: ./error.php');
	exit();
}
// 学年の配列
$grade = array('D3', 'D2', 'D1', 'M2', 'M1', 'B4', 'B3', 'B2');
// 学年がいるかどうか
$gradeFlag = array(
	'D3' => false,
	'D2' => false,
	'D1' => false,
	'M2' => false,
	'M1' => false,
	'B4' => false,
	'B3' => false,
	'B2' => false,
);
	

$sql = "SELECT id, name, grade FROM students WHERE status = 0 ORDER BY grade = 'D3' desc, grade = 'D2' desc, grade = 'D1' desc, grade = 'M2' desc, grade = 'M1' desc, grade = 'B4' desc, grade = 'B3' desc, grade = 'B2'";
//$sql = 'SELECT * from students';
// sql実行
$stmt = $pdo->query($sql);

// 学生の情報が入る配列
$student;
// 学生の数
$cnt = 0;
// 学年ごとの数
foreach ($grade as $index) {
	$cntDiv[$index] = 0;
}

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$checkFlag = true;

	// 登録ユーザの学年が$gradeの配列になければcontinue
	foreach ($grade as $check) {
		if ($check == $row['grade']) {
			$checkFlag = false;
			break;
		}
	}

	if ($checkFlag)
		continue;
	if (!$gradeFlag[$row['grade']])
		$gradeFlag[$row['grade']] = true;
	$student[$cnt]['id'] = $row['id'];
	$student[$cnt]['name'] = $row['name'];
	$student[$cnt]['grade'] = $row['grade'];
	$cnt++;
	$cntDiv[$row['grade']]++;
}

$pdo = null;	// データベース切断

?>
							<input class="allClass" type="checkbox" onClick="alla()" name="all" id="all"><label for="all">全員</label>
<?php
// グループでのチェックボックスの表示
foreach ($grade as $str) {
	if ($gradeFlag[$str]) {
		echo "<input class=\"${str}Class\" type=\"checkbox\" onClick=\"allGroupCheck('${str}')\" name=\"${str}\" id=\"${str}\"><label for=\"${str}\">${str}</label>";
	}
}

echo "<br>";
// 学生のチェックボックスでの表示
if (!empty($student)) {
	foreach ($student as $row) {
		echo "<input class=\"${row['grade']}\" type=\"checkbox\" onClick=\"check('${row['grade']}')\" name=\"allMember[${row['name']}]\" value=\"${row['id']}\" id=\"${row['id']}\"><label for=\"${row['id']}\">${row['name']}</label>";
	}
}
echo '<br>';
?>
							<label for="deadlineDate">出欠登録期限日: </label>
							<input type="date" id="deadlineDate" min="<?php echo date('Y-m-d'); ?>" name="deadlineDate" value="<?php if (!empty($deadlineDate)) echo $deadlineDate ?>"><br>
							<label for="deadlineTime">出欠登録期限時刻: </label>
							<input type="time" id="deadlineTime" name="deadlineTime" value="<? if (!empty($deadlineTime)) echo $deadlineTime; ?>"><br>

							<label for="mile1">マイルストン1:  イベント日</label>
							<input type="text" id="mile1" name="mile1Before" size="1" value="<?php if (!empty($mile1Before)) echo $mile1Before; ?>">
							日前の
							<input type="text" name="mile1Time" size="1" value="<?php if (!empty($mile1Time)) echo $mile1Time ?>">
							時<br>

							<label for="mile1">マイルストン2:  イベント日</label>
							<input type="text" id="mile2" name="mile2Before" size="1" value="<?php if (!empty($mile2Before)) echo $mile2Before; ?>">
							日前の
							<input type="text" name="mile2Time" size="1" value="<?php if (!empty($mile2Time)) echo $mile2Time; ?>">
							時<br>
							<input type="button" onClick="javascript:location.href = './main.php'" value="メイン画面へ戻る">
							<input type="submit" name="submit" value="入力確認へ">
						</form>
					</div>
				<?php } ?>
				</div>
			</center>
		</div>
	</body>
</html>

