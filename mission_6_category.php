<?php
//セッションからユーザーIDを取得
session_start(array('cookie_lifetime' => 86400));
$ID='personal_'.$_SESSION['ID'];

//ログアウト状態ならログインページへ
if($ID== NULL){
    header("Location:mission_6_login.php");
}

//データベース接続
$dsn = 'mysql:dbname=データベース;host=localhost';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn,$user,$password);

//カテゴリ一覧取得
$cats=$pdo->query("show columns from $ID");
$cats=$cats->fetchAll();


?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="mission_6_style.css">
<meta http-equiv="content-type" charset="utf-8">

</head>

<body>
<?php
//カテゴリがIDの場合
if($_GET['category']!=NULL && $_GET['category']!='ID'):
?>
<form method="post">
    <input type="submit" class="back_btn" name="back" value=""><br>
    <table border=2 style='border-collapse:collapse;' align="center">
        <tr>
            <td>
                <h2>カテゴリ名変更</h2>
            </td>
            <td>
                <table align="center">
                    <tr>
                        <td>
                            対象カテゴリ名
                        </td>
                        <td>
                            <input type="text" value="<?php echo $_GET['category'];?>" name="old_name" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            変更後
                        </td>
                        <td>
                            <input type="text" name="new_name">
                        </td>
                    </tr>
                </table>
                <input type="submit" value="" class="submit_btn small_btn">
            </td>
        </tr>
        <tr>
            <td>
                <h2>カテゴリ削除</h2>
            </td>
            <td>
                <input type="submit" class="delete_btn small_btn" value="" name="delete">
            </td>
        </tr>
        <tr>
            <td>
                <h2>カテゴリソート</h2>
            </td>
            <td>
                <input type="radio" name="order" value="asc">昇順
                <input type="radio" name="order" value="desc">降順<br>
                <input type="submit" class="submit_btn small_btn" value="" name="sort">
            </td>
        </tr>
        <tr>
            <td>
                <h2>カテゴリ移動</h2>
            </td>
            <td>
                <code>変更したい位置を選択して送信してください。</code>
                <br><select name="cat_after">
                <?php
                foreach($cats as  $value):
                    if($value[0]!=$_GET['category']){
                ?>
                    <option value=<?php echo"'$value[0]'disabled>$value[0]";?></option>
                    <option value=<?php echo"'$value[0]'>";?></option>
                <?php
                    }
                endforeach;
                ?>
                <br><br><input type="submit" class="submit_btn small_btn" value="" name="modify">
            </td>
        </tr>
    </table>
</form>
<?php
endif;
//ID以外のカテゴリの場合
if($_GET['category']=='ID'):
?>

<form method="post">
    <input type="submit" class="back_btn" name="back" value=""><br>
    <input type="hidden" value="<?php echo $_GET['category'];?>" name="old_name" readonly>
    <h2>カテゴリソート</h2>
    <input type="radio" name="order" value="asc">昇順
    <input type="radio" name="order" value="desc">降順
    <input type="submit" class="submit_btn small_btn" value="" name="sort">

</form>
<?php
endif;
if($_GET['category']==NULL){
    header("Location:mission_6_user.php");
}

//変数設定
$old=$_POST['old_name'];
$new=$_POST['new_name'];
$delete=$_POST['delete'];
$back=$_POST['back'];
$category=$_POST['cat_after'];
$modify=$_POST['modify'];
$sort=$_POST['sort'];
$order=$_POST['order'];

//送信時の動作
if($new!=""){   //名称変更
    $pdo->query("alter table $ID change $old $new text");
    echo $old."を".$new."に変更しました。<br>";
    echo '<form method="post"><input type="submit" class="back_btn small_btn" value="" name="back"></form>';
}elseif(isset($delete)){    //カテゴリ削除
    $pdo->query("alter table $ID drop $old");
    //echo"alter table $ID drop $old";
    echo $old."を削除しました。<br>";
    echo '<form method="post"><input type="submit" class="back_btn small_btn" value="" name="back"></form>';
}elseif(isset($modify)){    //カテゴリ移動
    $pdo->query("alter table $ID modify $old text after $category");
    echo $old."を $category の後に移動しました。";
    echo '<form method="post"><input type="submit" class="back_btn small_btn" value="" name="back"></form>';
}elseif(isset($sort)){  //カテゴリソート
    $_SESSION['order']=$old.' '.$order;
    echo "$old を";
    if($order=='asc')echo "昇順";
    elseif($order=='desc')echo "降順";
    echo "で並び替えました。";
    echo '<form method="post"><input type="submit" class="back_btn small_btn" value="" name="back"></form>';
}elseif(isset($back)){  //戻るボタンでページ遷移
    header("Location:mission_6_user.php");
}

?>


</body>
