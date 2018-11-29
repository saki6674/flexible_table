<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="mission_6_style.css">
<meta http-equiv="content-type" charset="utf-8">
</head>

<body>
<!--ログイン/新規 選択-->
<form method="post">
<input type="submit" class="login_btn" value="" name="login">
<input type="submit" class="signup_btn" value="" name="signup">
</form>

<?php
//ページ遷移
if(isset($_POST['login'])){
    header("Location:mission_6_login.php");
}elseif(isset($_POST['signup'])){
    header("Location:mission_6_signup.php");
}
?>

</body>

</html>