〜動作環境〜
PHP Version 5.2.4

〜ページの関連性〜
①index.php
    TOPページ。
    ログイン(⑤mission_6_login.php)と新規登録(②mission_6_signup.php)への遷移。
②mission_6_signup.php
    名前とメールアドレスを入力し、認証メールを送信。
    仮登録テーブル「pre」に登録。
③mission_6_confirm.php
    ②で届く認証メールからリンク。
    tokenをgetで受け取り。
    IDとパスワードの設定。
④mission_6_complete.php
    ③の設定完了画面。
    ID被りや、パスワードの2回入力のチェック。
    ユーザー一覧テーブル「user」への登録。
    個人テーブル「personal_[ユーザーID]」の作成。
    ログインページへリンク。
⑤mission_6_login.php
    IDとパスワードでログイン。
    新規登録画面へのリンク。
⑥mission_6_user.php
    メイン画面。
    ユーザーの個人テーブル内容の表示
⑦mission_6_category.php
    メイン画面から、各カテゴリ(カラム)の操作。
    getでカテゴリ名取得。
    カテゴリ(カラム)の名称変更・削除・移動・ソート。
⑧mission_6_contact.php
    問い合わせフォーム。
    問い合わせ内容登録テーブル「advice」への登録。
⑨mission_6_style.css
    全体のスタイルシート。
    
    
〜テーブルの設定〜
①pre
    新規登録画面で登録。
    メールアドレス認証前。
    create table pre (name text , mail text , date int , token text);
②user
    メールアドレス認証、ID/パスワードの設定済みのユーザー一覧。
    CREATE TABLE user (ID text , name text , mail TEXT , hash_pass TEXT);
    hash_passはsalt&pepperでhash化。
③personal_[ユーザーID]
    各ユーザーのデータ一覧。
    デフォルトは以下で、その後メイン画面で操作可能。
    CREATE TABLE personal_$ID (ID INT PRIMARY KEY , name text , comment TEXT , image TEXT);
    imageはpathを保存。
    ID=99999には、カラムの形式が格納されている。(textとtextareaを分類する為に使用)
⑤advice
    問い合わせ画面の内容を登録。
    create table advice (ID text,content text);