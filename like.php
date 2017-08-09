<?php
    session_start();
    require('dbconnect.php');
// 前提：$_GET['tweet_id']でlikeしたいtweet_idが取得できる
    // ログインチェック
    if(isset($_SESSION['login_member_id'])){
    //指定されたtweet_idが、ログインユーザー本人のものかチェック(指定されたtweet_idのmember_idがlogin_member_idと同じ時)
    // INSERT文：ログインしてる人が指定したtweet_idのつぶやきをlikeした情報を保存するINSERT文を作成(文字列連結版)
      // 要復習：文字列連結
      $sql = 'INSERT INTO `likes` SET `tweet_id`='.$_GET['tweet_id'].',`member_id`='.$_SESSION['login_member_id'] ;
    // INSERT文：ログインしてる人が指定したtweet_idのつぶやきをlikeした情報を保存するINSERT文を作成(sprintf版)
    // $sql = sprintf('INSERT INTO `likes` SET `tweet_id`=%d,`member_id`=%d',$_GET['tweet_id'],$_SESSION['login_member_id']);
    // SQLを実行
      $stmt = $dbh->prepare($sql);
      $stmt->execute();
    }
    // トップページに戻る
    header('Location: index.php');
    // 下にコードがないときはexitは書かなくても良い
    // exit();
?>