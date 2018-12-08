
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

//定数設定
$col_type=99999;
$image_file="./upfiles/";

//〜アカウント〜
//ログアウト
if(isset($_POST['logout'])){
     session_start(array('cookie_lifetime' => 86400));
    $_SESSION['ID']=NULL;
    $_SESSION['order']=NULL;
}

//セッションからユーザーIDを取得
session_start(array('cookie_lifetime' => 86400));
if(isset($_SESSION['ID'])){
    $ID='personal_'.$_SESSION['ID'];
}

//〜ページ遷移〜
//ログアウト状態ならログインページへ
if(is_null($ID)){
    header("Location:mission_6_login.php");
}

//問い合わせは問い合わせページへ
if(isset($_POST['contact'])){
    header("Location:mission_6_contact.php");
}

//〜カテゴリ〜
//カテゴリ追加
if(isset($_POST['add_cat'])){
    $name=$_POST['add_cat_name'];
    $type=$_POST['add_cat_type'];
    $sql="alter table $ID add $name text";
    $pdo->query($sql);
    $pdo->query("update $ID set $name='$type' where ID=$col_type");
}

//表示カテゴリリスト作成
$cats=$pdo->query("show columns from $ID");
$cats=$cats->fetchAll();
$shows=$_POST['show_cate'];
if(empty($shows)){   //表示カテゴリがなければ全て表示
    foreach($cats as $value){
        $shows[]=$value[0];
    }
}
if(!in_array("ID",$shows)){ //IDが入ってなければ追加
    array_unshift($shows,"ID");
}

//〜レコード操作〜
//新規登録
if(isset($_POST['new_row'])){
    $sql="insert into $ID (";
    foreach($shows as $value){      //追加するカテゴリを設定
        $sql.=$value.",";
    }
    $sql=rtrim($sql,",");
    $sql.=") values (";
    foreach($shows as $value){
        $type=$pdo->query("select $value from $ID where ID=$col_type"); //各カテゴリの形式を取得
        $type=$type->fetch();
        if($type[0]!="image"){  //画像以外
            $sql.="'".$_POST["$value"]."',";
        }else{  //画像
            $image_array=$_FILES["$value"];
            $tmp_name=$image_array['tmp_name'];
            $name=$image_array['name'];
            $path=$image_file.$name;
            $check=move_uploaded_file($tmp_name,$path);
            $file_name=$image_file.$ID.time().".jpg";
            if($check==1){
                rename($path,$file_name);
                $path=$file_name;
            }
            $sql.="'".$path."',";
        }
    }
    $sql=rtrim($sql,",");
    $sql.=")";
    $pdo->query($sql);
}

//削除 
//削除するIDの取得
foreach($_POST as $key => $value){
    if(preg_match("/delete_[0-9]+$/",$key)){
        $del_ID=ltrim($key,"delete_");
    }
}
//削除SQL
if($del_ID!=NULL){
    $sql="delete from $ID where ID=$del_ID";
    $pdo->query($sql);
    $shift_ID=$del_ID+1;
    while($shift_ID>1){
        $sql=$pdo->query("select ID from $ID where ID=".$shift_ID);
        $sql=$sql->fetch();
        if(is_null($sql)){
            break;
        }
        $sql="update $ID set ID=".--$shift_ID." where ID=".++$shift_ID;
        $pdo->query($sql);
        $shift_ID++;
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
    $edit_exist=true;
}
//編集データの受け取り、更新
if(isset($_POST['edit_data'])){
    $sql="update $ID set ";
    foreach($shows as $value){  //表示する全てのカテゴリで
        //ID=$col_typeのカラム名$valueを取得
        $type=$pdo->query("select $value from $ID where ID=$col_type"); //形式の取得
        $type=$type->fetch();
        if($type[0]=="image"){  //画像の場合
            if($_POST["$value"]!="delete" && $_FILES["$value"]['error']==0){ //画像を削除せず画像エラーもない場合
                //画像登録
                $image_array=$_FILES["$value"];
                $tmp_name=$image_array['tmp_name'];
                $name=$image_array['name'];
                $path=$image_file.$name;
                move_uploaded_file($tmp_name,$path);
                $file_name=$image_file.$ID.time().".jpg";
                if($check==1){
                    rename($path,$file_name);
                    $path=$file_name;
                }
                $sql.="$value='".$path."',";
            }else{  //画像を削除する場合
                $sql.="$value='',";
            }
        }elseif($value!="ID"){    //画像でない場合
            $sql.=$value."='".$_POST["$value"]."',";
        }
    }
    $sql=rtrim($sql,",");
    $sql.=" where ID=".$_POST['ID'];
    $pdo->query($sql);
}

//レコード取得
if(is_null($_SESSION['order'])){
    $_SESSION['order']="ID";
}
$order="order by ".$_SESSION['order'];
$cells=$pdo->query("select * from $ID ".$order);
$cells=$cells->fetchAll();

?>


<table class="overall">
<tr style="height:3em;"><td><?php echo $_SESSION['ID']."さん"; ?></td><td style="width:2em;"></td><td>
<!--ログアウト,コンタクトボタン-->
<form method="post">
    <input type="submit" name="logout" value="" class="logout_btn" >
    <input type="submit" name="contact" value="" class="contact_btn">
</form>
</td></tr>
<tr><td>
<!--表示項目選択欄　チェックボックス+submit(決定)-->
<form method="post">
<table class="none">
<tr><td></td><td><b>表示カテゴリ</b></td></tr>
<?php
foreach($cats as $key => $value){
    if(is_int($key)){   //配列からカテゴリ名を取り出して
        //check_box作成
        if($value[0]=="ID"){    //IDならboxなし
            echo "<tr><td>";
        }else{  //ID以外はbox作成
            echo "<tr><td><input type='checkbox' name='show_cate[]' value='".$value[0]."'";
            if(in_array($value[0],$shows)){ //表示しているカテゴリはcheck
                echo "checked>";
            }else{  //非表示のカテゴリはcheckしない
                echo ">";
            }
        }
        echo "</td><td>".$value[0];
        if($key=="ID"){
            echo "※必須";
        }
        echo "</td></tr>";
    }
}
?>
</table>
<input type="submit" value="" class="small_btn show_btn"> 
</form>
<hr>
<!--カテゴリ追加-->
<table class="none">
<tr><td><b>カテゴリ追加</b></td></tr>
<form method="post">
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
<?php   //カラム名表示
echo "<th>操作</th>";
foreach($shows as $value){
    //カテゴリ名にカテゴリ操作ページへのリンクを設定
    echo "<th><a href='mission_6_category.php?category=$value'>$value";
    //sortされてるカテゴリはsort順表示
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
<td><input type="submit" name="new_row" value="" class="small_btn signup_btn"></td>
<?php   //新規登録
foreach($shows as $value){
    echo "<th>";
    //$col_type行に格納されている形式のリストを取得
    $type=$pdo->query("select $value from $ID where ID=$col_type");
    $type=$type->fetch();
    if($type[0]=="image"){  //画像の入力フォーム
        echo "<input type='file' name='$value'accept='image/*'>";
    }elseif($value!="ID"){  //画像とID以外の入力フォーム
        //それぞれの$valueごとに<input>文を出力
        if($type[0]=="textarea"){
            echo "<textarea name='$value' rows='4' cols='40'></textarea>";
        }else{
            echo "<input type='".$type[0]."' name='$value'>";
        }
    }else{  //IDの送信用のhiddenフォーム
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
if($edit_exist==true):
?>

<form method="post" enctype="multipart/form-data">
<tr>
<td><input type="submit" name="edit_data" value="" class="small_btn edit_btn"></td>
<?php   //編集
foreach($shows as $value){
    echo "<td>";
    //変更前の値を取得
    $former_value=$pdo->query("select $value from $ID where ID=$edit_ID");
    $former_value=$former_value->fetch();
    //$col_type行に格納されている形式のリストを取得
    $type=$pdo->query("select $value from $ID where ID=$col_type");
    $type=$type->fetch();
    //編集フォームの作成
    if($type[0]=="image"){  //形式が画像の場合
        echo "<input type='file' name='$value' accept='image/*' value='".$former_value[0]."'><br>";
        echo "<input type='checkbox' name='$value' value='delete'>画像を削除";
        echo "<br>※未選択の場合、元の画像が維持されます。";
    }elseif($value!="ID"){  //画像でもIDでもない場合
       if($type[0]=="textarea"){
           echo "<textarea name='$value' rows='4' cols='40'>".$former_value[0]."</textarea>";
       }else{
           echo "<input type='".$type[0]."' name='$value' value='".$former_value[0]."'>";
       }
    }else{  //IDの場合、送信用のhiddenフォームを作成
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
<?php   //レコード一覧
foreach($cells as $value){
    if($value['ID']<1000){
        //編集削除ボタン
        echo "<tr><td><form method='post'><input type='submit' value='' class='small_btn edit_btn' name='edit_".$value['ID']."'><br><input type='submit' value='' class='small_btn delete_btn' name='delete_".$value['ID']."'></form></td>";
        foreach($shows as $cat){
            //$col_type行に格納されている形式のリストを取得
            $type=$pdo->query("select $cat from $ID where ID=$col_type");
            $type=$type->fetch();
            //各セルを表示
            echo "<td>";
            if($type[0]=="image"){  //画像の場合
                if($value[$cat]!=$image_file){   //画像がある場合
                    echo "<img src=".$value[$cat].">";
                }
            }elseif($type[0]=="datetime-local"){    //日付の場合
                $datetime=explode("T",$value[$cat]);
                foreach($datetime as $v){
                    echo $v." ";
                }
            }elseif($type[0]=="textarea"){  //テキストエリアの場合
                $text=$value[$cat];
                $text=preg_replace("/\n/","<br>",$text);
                echo $text;
            }else{  //その他
                echo  $value[$cat];
            }
            echo "</td>";
        }
        echo "</tr>";
    }
}
?>
</table>
</td></tr>
</body>
</html>

