<?php

//データベース接続
$dsn = 'mysql:dbname=データベース;host=localhost';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn,$user,$password);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="mission_6_style.css">
<meta http-equiv="content-type" charset="utf-8">
<style>
    .bold{
        font-size:2rem;
        font-weight:bold;
    }
</style>
</head>

<body>

<?php
//ユーザーテーブルの作成
$sql= "CREATE TABLE user (ID text,name text,mail TEXT,hash_pass TEXT);";
$stmt = $pdo->query($sql);

//userデータベースの検索
$user=$pdo->query("select ID from user");
$user=$user->fetchAll();

//変数設定
$name=$_POST['name'];
$mail=$_POST['mail'];
$ID=$_POST['ID'];
$pass=$_POST['pass'];
$check=$_POST['pass_check'];
$time=$_POST['time'];
$submit=$_POST['submit'];
$id_exsit=0;

//ID登録確認
foreach($user as $value){
    if(in_array($ID,$value)){
        $id_exsit=1;
    }
}

//ログインページに遷移
if(isset($_POST['login'])){
    header("Location:mission_6_login.php");
}

if(isset($submit)){
    if($pass!=$check){
        //パスワード再入力check
        echo "パスワードが一致しません。もう一度入力してください。";
        echo "<input type='button' value='戻る' onClick='window.history.back(-1)'>";
    }elseif($id_exsit==1){
        //ID固有check
        echo "IDはすでに使用されています。別のIDを入力してください。";
        echo "<input type='button' value='戻る' onClick='window.history.back(-1)'>";
    }else{
        //hash化
        $pepper='hgfisdo';
        $hash=sha1($pass).sha1($pepper);
        $hash=sha1($hash);

        //ユーザーテーブルへの追加
        $sql=$pdo->prepare("insert into user (ID,mail,name,hash_pass) values (:ID,:mail,:name,:pass)");
        $sql->bindParam(":ID",$ID,PDO::PARAM_STR);
        $sql->bindParam(":mail",$mail,PDO::PARAM_STR);
        $sql->bindParam(":name",$name,PDO::PARAM_STR);
        $sql->bindParam(":pass",$hash,PDO::PARAM_STR);
        $sql->execute();

        //個人テーブルの作成
            //企業ID、企業名、コメント、ステータス、志望度、//タスク、期限、画像
        $sql= "CREATE TABLE personal_$ID (ID INT PRIMARY KEY,name text,comment TEXT,image TEXT)";  //未完成
        $stmt = $pdo->query($sql);

        //カラムタイプの設定
        $pdo->query("insert into personal_$ID (ID,name,comment,image) values ('99999','text','textarea','image')");

        //登録完了メッセージ    
        //ログインページへのリンク表示
        echo "<span class='bold'>登録完了</span><br>";
        echo "<form method='post'><input type='submit' class='login_btn' value='' name='login'></form>";
    }
}
?>

</body>
</html>