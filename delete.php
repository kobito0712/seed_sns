<?php
    session_start();
    require('dbconnect.php');
    // ログインチェック
    if(isset($_SESSION['login_member_id'])){
    //指定されたtweet_idが、ログインユーザー本人のものかチェック(指定されたtweet_idのmember_idがlogin_member_idと同じ時)
      $sql = 'SELECT * FROM `tweets` WHERE `member_id`= ? AND`tweet_id` = ? ';
    // SQLを実行
    // arrayの中に取りたい値を入れる
    $data = array($_SESSION['login_member_id'],$_GET['tweet_id']);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    // while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //   $recordにfalseが代入されたとき、処理が終了します(データの一番最後まで取得してしまい、次に取得するデータが存在しないとき)
    //   $tweets[]:配列に新しいデータを追加
    //   $tweets[] = array(
    //     "tweet"=>$record['tweet'],
    //     "nick_name"=>$record['nick_name'],
    //     "created"=>$record['created'],
    //     "tweet_id"=>$record['tweet_id'],
    //     "picture_path"=>$record['picture_path'],
    //     "member_id"=>$record['member_id']
    //     );
    // }

    // 本人のものかどうか判別するif文を作成(書き方複数あり)
    // 該当データが一件もない場合は、$recordにfalseが代入される
    if($record != false){
    //本人のものであれば、削除処理(論理削除)
    // 論理削除のＳＱＬ文を作成し、実行
    // UPDATE文でdelete_flag=1に変更(指定されたtweet_idのみ)
      $sql = 'UPDATE `tweets` SET `delete_flag`=1 WHERE `tweet_id`=?';
      // SQLインジェクションを使うときはデータを配列に入れる必要あり
      $data = array($_GET['tweet_id']);
      // SQL実行
      $stmt = $dbh->prepare($sql);
      $stmt->execute($data);
    }


    }
    // トップページに戻る
    header('Location: index.php');
    // 下にコードがないときはexitは書かなくても良い
    // exit();
?>