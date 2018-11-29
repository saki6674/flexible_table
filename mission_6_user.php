
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="mission_6_style.css">
<meta http-equiv="content-type" charset="utf-8">
</head>

<body>

<?php   
//データベース接続
$dsn = 'mysql:dbname=データベース;host=localhost';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn,$user,$password);

//ログアウト
if(isset($_POST['logout'])){
     session_start(array('cookie_lifetime' => 86400));
    $_SESSION['ID']=NULL;
    $_SESSION['order']=NULL;
}

//セッションからユーザーIDを取得
session_start(array('cookie_lifetime' => 86400));
if(!empty($_SESSION['ID'])){
    $ID='personal_'.$_SESSION['ID'];
}

//ログアウト状態ならログインページへ
if($ID== NULL){
    header("Location:mission_6_login.php");
}
//問い合わせは問い合わせページへ
if(isset($_POST['contact'])){
    header("Location:mission_6_contact.php");
}

//カテゴリ追加
if(isset($_POST['add_cat'])){
    $name=$_POST['add_cat_name'];
    $type=$_POST['add_cat_type'];
    $sql="alter table $ID add $name text";
    $pdo->query($sql);
    $pdo->query("update $ID set $name='$type' where ID=99999");
}

//カテゴリ設定
$cats=$pdo->query("show columns from $ID");
$cats=$cats->fetchAll();
$shows=$_POST['show_cate'];
if($shows==NULL){
    //echo"error_shows";//checker
    foreach($cats as $value){
        $shows[]=$value[0];
    }
}
if(!in_array("ID",$shows)){
    array_unshift($shows,"ID");
}

//新規登録
if(isset($_POST['new_company'])){
    //echo "新規企業登録<br>";    //checker
    //sql文作成
    $sql="insert into $ID (";
    //ループ
    foreach($shows as $value){
        $sql.=$value.",";
    }
    $sql=rtrim($sql,",");
    $sql.=") values (";
    //ループ
    foreach($shows as $value){
        $type=$pdo->query("select $value from $ID where ID=99999");
        $type=$type->fetch();
        if($type[0]!="image"){
            $sql.="'".$_POST["$value"]."',";
        }else{  //画像登録
            $image_array=$_FILES["$value"];
            $tmp_name=$image_array['tmp_name'];
            $name=$image_array['name'];
            $path="./upfiles/$name";
            $check=move_uploaded_file($tmp_name,$path);
            $time="./upfiles/".$ID.time().".jpg";
            if($check==1){
                rename($path,$time);
                $path=$time;
            }
            $sql.="'".$path."',";
        }
    }
    $sql=rtrim($sql,",");
    $sql.=")";
    //echo "newSQL:".$sql;//checker
    $pdo->query($sql);
}

//削除 
foreach($_POST as $key => $value){
    if(preg_match("/delete_[0-9]+$/",$key)){
        $del_ID=ltrim($key,"delete_");
    }
}
if($del_ID!=NULL){
    //echo "$del_ID の企業を削除";    //checker
    $sql="delete from $ID where ID=$del_ID";
    $pdo->query($sql);
    $i=$del_ID+1;
    if($i>=2){
        while($i>1){
            $sql=$pdo->query("select ID from $ID where ID=".$i);
            $sql=$sql->fetch();
            if($sql==NULL){
                break;
            }
            $sql="update $ID set ID=".--$i." where ID=".++$i;
            $pdo->query($sql);
            $i++;
        }
    }
}
//編集
foreach($_POST as $key => $value){
    if(preg_match("/edit_[0-9]{1,}$/",$key)){
        $edit_ID=ltrim($key,"edit_");
    }
}
//編集フォームの表示flag
if($edit_ID!=NULL){
    //echo "$edit_ID の企業の編集フォームを作成"; //checker
    $edit_exe=true;
}
//編集データの受け取り、更新
if(isset($_POST['edit_data'])){
    //echo $_POST['ID']."の企業情報を編集";   //checker
    $sql="update $ID set ";
    //ループ　A='A',B='B'
    foreach($shows as $value){
        //ID=99999のカラム名$valueを取得
        $type=$pdo->query("select $value from $ID where ID=99999");
        $type=$type->fetch();
        if($type[0]=="image"){
            if($_POST["$value"]!="delete"){
                if($_FILES["$value"]['error']==0){
                    //画像登録
                    $image_array=$_FILES["$value"];
                    $tmp_name=$image_array['tmp_name'];
                    $name=$image_array['name'];
                    $path="./upfiles/$name";
                    $check=move_uploaded_file($tmp_name,$path);
                    $time="./upfiles/".$ID.time().".jpg";
                    echo "check:".$check;
                    if($check==1){
                        rename($path,$time);
                        $path=$time;
                    }
                    $sql.="$value='".$path."',";
                }
            }else{
                $sql.="$value='',";
            }
        }elseif(!($value=="edit_data" || $value=="ID")){
            $sql.=$value."='".$_POST["$value"]."',";
        }
    }
    $sql=rtrim($sql,",");
    $sql.=" where ID=".$_POST['ID'];
    $pdo->query($sql);
}


//レコード取得
if(empty($_SESSION['order'])){
    $_SESSION['order']="ID";
}
$order="order by ".$_SESSION['order'];
$cells=$pdo->query("select * from $ID ".$order);
$cells=$cells->fetchAll();



//checker
/*
echo "<hr>cats<br>";
var_dump($cats);
echo "<hr>shows<br>";
var_dump($shows);
echo "<hr>cells<br>";
var_dump($cells);
echo "<hr>";
*/
?>


<table class="overall">
<tr style="height:3em;"><td><?php echo $_SESSION['ID']."さん"; ?></td><td style="wIDth:2em;"></td><td>
<!--ログアウト,コンタクトボタン-->
<form method="post" enctype="multipart/form-data">
    <input type="submit" name="logout" value="" class="logout_btn" >
    <input type="submit" name="contact" value="" class="contact_btn">
</form>
</td></tr>
<tr><td>
<!--表示項目選択欄　チェックボックス+submit(決定)-->
<form method="post" enctype="multipart/form-data">
<table class="none">
<tr><td></td><td><b>表示カテゴリ</b></td></tr>
<?php
foreach($cats as $key => $value){
    if(is_int($key)){
        if($shows!=NULL){
            if($value[0]=="ID"){
                echo "<tr>";
                echo "<td>";
            }else{
                echo "<tr>";
                echo "<td><input type='checkbox' name='show_cate[]' value='".$value[0]."'";
                if(in_array($value[0],$shows)){
                    echo "checked>";
                }else{
                    echo ">";
                }
            }
        }
        echo "</td>";
        echo "<td>".$value[0];
        if($key=="ID"){
            echo "※必須";
        }
        echo "</td>";
        echo "</tr>";
    }
}
?>
</table>
<input type="submit" value="" class="small_btn show_btn"> 
</form>
<!--カテゴリ追加-->
<table class="none">
<tr><td><b>カテゴリ追加</b></td></tr>
<form method="post" enctype="multipart/form-data">
<tr><td>名前　<input type="text" name="add_cat_name" required></td></tr>
<tr><td>タイプ</td></tr>
<tr><td><input type="radio" name="add_cat_type" value="text" checked>テキスト</td></tr>
<tr><td><input type="radio" name="add_cat_type" value="textarea" >テキスト(改行あり)</td></tr>
<tr><td><input type="radio" name="add_cat_type" value="number">整数</td></tr>
<tr><td><input type="radio" name="add_cat_type" value="datetime-local">日時</td></tr>
<tr><td><input type="radio" name="add_cat_type" value="image">画像</td></tr>
</table>
<input type="submit" value="" name="add_cat" class="small_btn add_btn">
</form>
</td><td>
</td><td>

<!--表示-->

<table class="solid" border=2;>
<tr>
<?php   //カラム名
echo "<th>操作</th>";
foreach($shows as $value){
    echo "<th><a href='mission_6_category.php?category=$value'>$value";
    if(preg_match("/$value/",$order)){
        if(preg_match('/asc/',$order)){
            echo " ▲";
        }else{
            echo " ▼";
        }
    }
    echo "</a>";
    if($value=="ID"){
        echo "<br>必須";
    }
    echo "</th>";
}
?>
</tr>
<form method="post" enctype="multipart/form-data">
<tr>
<td><input type="submit" name="new_company" value="" class="small_btn signup_btn"></td>
<?php   //新規登録
foreach($shows as $value){
    echo "<th>";
    //ID=99999のカラム名$valueを取得
    $type=$pdo->query("select $value from $ID where ID=99999");
    $type=$type->fetch();
    if($type[0]=="image"){
        echo "<input type='file' name='$value'accept='image/*'>";
    }elseif($value!="ID"){
        //ID=99999のカラム名$valueを取得
        $type=$pdo->query("select $value from $ID where ID=99999");
        $type=$type->fetch();
        //それぞれの$valueごとに<input>文を出力
        if($type[0]=="textarea"){
            echo "<textarea name='$value' rows='4' cols='40'></textarea>";
        }else{
            echo "<input type='".$type[0]."' name='$value'>";
        }
    }else{
        $cnt=count($cells);
        echo "<input type='hidden' value='$cnt' name='$value'readonly>";
        echo $cnt;
    }
    echo "</th>";
}
?>
</tr>
</form>
<?php   //編集
if($edit_exe==true):
?>

<form method="post" enctype="multipart/form-data">
<tr>
<td><input type="submit" name="edit_data" value="" class="small_btn edit_btn"></td>
<?php   //編集
foreach($shows as $value){
    echo "<td>";
    $val=$pdo->query("select $value from $ID where ID=$edit_ID");
    $val=$val->fetch();
    //ID=99999のカラム名$typeを取得
    $type=$pdo->query("select $value from $ID where ID=99999");
    $type=$type->fetch();
    if($type[0]=="image"){
        echo "<input type='file' name='$value' accept='image/*' value='".$val[0]."'><br>";
        echo "<input type='checkbox' name='$value' value='delete'>画像を削除";
        echo "<br>※未選択の場合、元の画像が維持されます。";
    }elseif($value!="ID"){
        //type変更
       //それぞれの$valueごとに<input>文を出力
       if($type[0]=="textarea"){
           echo "<textarea name='$value' rows='4' cols='40'>".$val[0]."</textarea>";
       }else{
           echo "<input type='".$type[0]."' name='$value' value='".$val[0]."'>";
       }
    }else{
        echo "$edit_ID";
        echo "<input type='hidden' value='$edit_ID' name='$value'readonly>";
    }
    echo "</td>";
}
?>
</tr>
</form>
<?php
endif;
?>
<?php   //レコード
foreach($cells as $value){
    if($value['ID']<1000){
        echo "<tr><td><form method='post'><input type='submit' value='' class='small_btn edit_btn' name='edit_".$value['ID']."'><br><input type='submit' value='' class='small_btn delete_btn' name='delete_".$value['ID']."'></form></td>";
        foreach($shows as $cat){
            //typeの取得 $type[0]がタイプ
            $type=$pdo->query("select $cat from $ID where ID=99999");
            $type=$type->fetch();
            if($type[0]=="image"){
                if($value[$cat]!="./upfiles/"){
                    echo "<td><img src=".$value[$cat]."></td>";
                }else{
                    echo "<td></td>";
                }
            }elseif($type[0]=="datetime-local"){
                $datetime=explode("T",$value[$cat]);
                echo "<td>";
                foreach($datetime as $v){
                    echo $v." ";
                }
                echo "</td>";
            }elseif($type[0]=="textarea"){
                echo "<td>";
                $text=$value[$cat];
                $text=preg_replace("/\n/","<br>",$text);
                echo $text;
                echo "</td>";
            }else{
                echo  "<td>".$value[$cat]."</td>";
            }
        }
        echo "</tr>";
    }
}
?>
</table>
</td></tr>
</body>
</html>

