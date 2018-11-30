<?php
//セッションからユーザーIDを取得
session_start(array('cookie_lifetime' => 86400));
$ID='personal_'.$_SESSION['ID'];

//ログアウト状態ならログインページへ
if($ID== NULL){
    header("Location:mission_6_login.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="mission_6_style.css">
<meta http-equiv="content-type" charset="utf-8">
</head>

<body>
<!--項目選択-->
<form method="post">
<input type="submit" class="back_btn" value="" name="back"><br>
ユーザーID<br>
<input type="text" name="ID" value='<?php echo $_SESSION['ID'];?>'readonly><br>
問い合わせ内容<br>
<textarea name="content" rows="4" cols="100"></textarea><br>
<input type="submit" class="submit_btn" value="" name="submit">
</form>

<?php
//ページ遷移
if(isset($_POST['back']) || isset($_POST['submit'])){
    header("Location:mission_6_user.php");
}

//問い合わせ内容の保存
//データベース接続
$dsn = 'mysql:dbname=データベース;host=localhost';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn,$user,$password);

$ID=$_POST['ID'];
$content=$_POST['content'];

$pdo->query("create table advice (ID text,content text)");
$pdo->query("insert into advice (ID,content) values ('$ID','$content')");

?>

</body>

</html>
