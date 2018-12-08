<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="mission_6_style.css">
<meta http-equiv="content-type" charset="utf-8">

</head>

<body>
<h3>新規登録</h3>
<form method="post">
名前<br><input type="text" name="name" required><br>
メールアドレス<br><input type="text" name="mail" required><br><br>
<input type="submit" class="small_btn submit_btn" value="" name="submit">
</form>

<?php
//データベース接続
$dsn = 'mysql:dbname=データベース;host=localhost';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn,$user,$password);

//仮登録テーブル作成    //名前、メール、日付、トークン
$sql="create table pre (name text,mail text,date int,token text);";
$pdo->query($sql);

//変数設定
$name=$_POST['name'];
$mail=$_POST['mail'];
$date=date('Y/m/d H:i:s');
$submit=$_POST['submit'];

if(isset($submit) && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$mail)){
    echo "正しいメールアドレスを入力してください。";
    exit();
}

//トークンの設定
$token=hash("sha256",$name.$mail.$date);

if(isset($submit)){
    //メール送信
    $title="仮登録のお知らせ";
    $body="$name 様\n\n以下のURLから本登録手続きに進んでください。\n\nhttp://tt-53.99sv-coco.com/mission_6_confirm.php"."?token=".$token;
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");
    $send=mb_send_mail($mail,$title,$body);

    //仮登録テーブル(pre)登録
    $statement="INSERT INTO pre (name,mail,date,token) VALUES (:name,:mail,:date,:token)";
    $sql=$pdo->prepare($statement);
    $sql->bindParam(":name",$name,PDO::PARAM_STR);
    $sql->bindParam(":mail",$mail,PDO::PARAM_STR);
    $sql->bindParam(":date",time(),PDO::PARAM_INT);
    $sql->bindParam(":token",$token,PDO::PARAM_STR);
    $sql->execute();
    
    //期限切れデータ削除
    $pre=$pdo->query("select * from pre");
    $pre=$pre->fetchALL();
    foreach($pre as $value){
        if(time()-$value['date']>=24*60*60){
            $pdo->query("delete from pre where token='".$value['token']."'");
        }
    }

    //完了&注意事項表示
    echo "仮登録完了。24時間以内に本登録を行ってください。";

    //checker
    echo "<br><hr>⬇︎チェック用⬇︎<br><a href='http://tt-53.99sv-coco.com/mission_6_confirm.php"."?token=".$token."'>本登録はこちら</a><br><br>";
}

?>

</body>
</html>