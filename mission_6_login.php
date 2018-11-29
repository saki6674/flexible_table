<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="mission_6_style.css">
<meta http-equiv="content-type" charset="utf-8">

</head>

<body>
<table class="table_center"><tr><td>
    <h3>ログイン</h3>
    <form method="post">
        ユーザーID<br><input type="text" placeholder="ユーザーID" name="ID"><br>
        パスワード<br><input type="password" placeholder="パスワード" name="pass"><br><br>
        <input type="submit" name="submit" value="" class="small_btn login_btn">
</td><td width=10%></td><td>
        <h3>新規登録</h3>
        未登録の方はこちら<br>
        <input type="submit" name="signup" value="" class="small_btn signup_btn">
    </form>
</td></tr></table>

<?php
//データベース接続
$dsn = 'mysql:dbname=データベース;host=localhost';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn,$user,$password);

if(isset($_POST['signup'])){
    header("Location:mission_6_signup.php");
    exit();
}

if($_POST!=NULL){
    $ID=$_POST['ID'];
    //変数の設定
    $sql="select hash_pass from user where ID='$ID'";
    $A=$pdo->query($sql);
    $A=$A->fetch();

    $check=$A['hash_pass'];
    $pass=$_POST['pass'];

    //hash化
    $pepper='hgfisdo';
    $hash=sha1($pass).sha1($pepper);
    $hash=sha1($hash);

    //IDパスワード照合
    if($check==$hash){
        echo"CHECK";    //checker
        session_start(array('cookie_lifetime' => 86400));
        $_SESSION['ID']=$ID;
        header("Location:mission_6_user.php");
    }else{
        echo "ユーザーIDかパスワードをご確認ください。";
    }
}

?>


</body>
</html>