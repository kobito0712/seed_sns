<?php
    session_start();
    require('dbconnect.php');
// 前提：$_GET['tweet_id']でlikeしたいtweet_idが取得できる
    // ログインチェック
    if(isset($_SESSION['login_member_id'])){
    //指定されたtweet_idが、ログインユーザー本人のものかチェック(指定されたtweet_idのmember_idがlogin_member_idと同じ時)
    // DELETE文：ログインしてる人が指定したtweet_idのつぶやきをlikeした情報を削除するDELETE文を作成(物理削除)
      // WEHREの中ではANDを使う
      $sql = 'DELETE FROM `likes` WHERE `tweet_id`='.$_GET['tweet_id'].' AND `member_id`='.$_SESSION['login_member_id'] ;
    // SQLを実行
      $stmt = $dbh->prepare($sql);
      $stmt->execute();
    }
    // トップページに戻る
    header('Location: index.php');
    // 下にコードがないときはexitは書かなくても良い
    // exit();
?>