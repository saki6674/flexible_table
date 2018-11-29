<?php
//データベース接続
$dsn = 'mysql:dbname=データベース;host=localhost';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn,$user,$password);

//確認メールのトークン受け取り
$token = $_GET['token'];
$sql="select * from pre where token='".$token."'";
$pre=$pdo->query($sql);
$pre=$pre->fetch();

//仮登録データ設定
$name=$pre['name'];
$mail=$pre['mail'];
$time=time()-$pre['date'];
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="mission_6_style.css">
<meta http-equiv="content-type" charset="utf-8">
</head>

<body>
<?php
if($time>=24*60*60):
?>
    仮登録から24時間以上が経過しているか、仮登録がされていません。<br>再度登録をお願いします。
    <input type='button' value='再登録' onClick='window.open("mission_6_signup.php")'>
<?php
endif;
if($time<24*60*60):
?>

<h3>新規登録</h3>
<form method="post" action="mission_6_complete.php">
<!--仮登録項目表示-->
名前<input type="text" value="<?php echo $name;?>" name="name" readonly><br>
メールアドレス<input type="text" value="<?php echo $mail;?>" name="mail" readonly><br>
<!--その他質問項目ユーザーID、パスワード-->
ユーザーID<input type="text" name="ID" required><br>
パスワード<input type="password" name="pass" required><br>
パスワード(確認用)<input type="password" name="pass_check" required><br><br>
<input type="submit" name="submit" value="" class="small_btn submit_btn">
</form>

<?php
endif;
?>

</body>
</html>