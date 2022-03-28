<?php
// ***************************
// メール送信
// ***************************
function db_mail(){

    global $server,$user,$dbname,$password,$message;

    // ***************************
    // DB 接続
    // ***************************
    $mysqli = @ new mysqli($server, $user, $password, $dbname);
    if ($mysqli->connect_error) {
        print "接続エラーです : ({$mysqli->connect_errno}) ({$mysqli->connect_error})";
        exit();
    }
    // ***************************
    // クライアントの文字セット
    // ***************************
    $mysqli->set_charset("utf8"); 

    // シングルクォートをエスケープ
    $to = str_replace("'", "''", $_POST["to"]);
    $subject = str_replace("'", "''", $_POST["subject"]);
    $text = str_replace("'", "''", $_POST["text"]);
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\n", "\r\n", $text);

    // 入力内容を SQL に埋め込む
    $insert = "insert into maildb (to_address,subject,one_comment,create_date) values('{$to}','{$subject}','{$text}',NOW())";

    // maildb への登録が失敗した場合は( 初回 ) maildb を作成
    if( false === $mysqli->query($insert) ) {
        $create = "create table maildb ( mail_id serial, to_address varchar(100), subject varchar(100), one_comment varchar(100), create_date datetime, primary key(mail_id) )";
        $mysqli->query($create);
        // 作成されたので再度登録
        $mysqli->query($insert);
    }

    // メール送信の準備( サーバ上のメールアドレスを使用 )
    $mail_address = "a@b.c.jp";
    $from_header = "From: " . mb_encode_mimeheader( mb_convert_encoding("差出人の名前","iso-2022-jp") );
    $from_header .= " <{$mail_address}>";

    // メール送信
    $result = mb_send_mail($_POST["to"], $_POST["subject"], $_POST["text"], $from_header);

    // 新しい投稿用のクラス作成
    $json = new stdClass;
    
    $json->to = $_POST["to"];
    $json->subject = $_POST["subject"];
    $json->text = $_POST["text"];
    $json->status = $result;

    print json_encode( $json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
}

// **************************
// デバック
// **************************
function debug_print(){

    print "<pre class=\"m-5\">";
    print_r( $_GET );
    print_r( $_POST );
    print_r( $_SESSION );
    print_r( $_FILES );
    print "</pre>";

}

