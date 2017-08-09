<?php
    // 前の画面から送られてきたidを使ってSQL文を作成
    // 前の画面から送られてきたidが何か判別できる
// get送信の使い方はmetod=getから受け取る方法と、urlの?以降の値を受信ページのurlにリンクとして表示させ、送信先で受け取る方法の2通りがある
    var_dump($_GET['tweet_id']);
    session_start();

    require('dbconnect.php');
    // $sql = 'SELECT * FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id` WHERE `tweet_id`=$GET_['tweet_id']';
    $sql = 'SELECT * FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id` WHERE `tweet_id`=?';
    // SQL文実行
    $data = array($_GET['tweet_id']);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    // データ取得
    while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
      // $recordにfalseが代入されたとき、処理が終了します(データの一番最後まで取得してしまい、次に取得するデータが存在しないとき)
      // $tweets[]:配列に新しいデータを追加
      $tweets[] = array(
        "tweet"=>$record['tweet'],
        "nick_name"=>$record['nick_name'],
        "created"=>$record['created'],
        "tweet_id"=>$record['tweet_id'],
        "picture_path"=>$record['picture_path'],
        "member_id"=>$record['member_id']
        );
    }
    // 繰り返し処理を用いなくても書ける
    // $record = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/form.css" rel="stylesheet">
    <link href="assets/css/timeline.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">

  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.html"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.html">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>
<!-- 取得したデータを表示に使用 -->
  <div class="container">
    <div class="row">
      <div class="col-md-4 col-md-offset-4 content-margin-top">
        <?php foreach ($tweets as $tweet_each) { ?>
          <div class="msg">
            <img src="member_picture/<?php echo $tweet_each['picture_path']; ?>" width="100" height="100">
              <p>投稿者 : <span class="name"> (<?php echo $tweet_each['nick_name']; ?>) </span></p>
              <p>
                つぶやき : <br>
                <?php echo $tweet_each['tweet']; ?>
              </p>
              <p class="day">
                <?php echo $tweet_each['created']; ?>
              <!-- [<a href="#" style="color: #F33;" >削除</a>] -->
              <!-- javascriptで最終確認のポップアップを作る -->
              <!-- 他人のつぶやきは削除できないようにする -->
              <!-- <?php //if($_GET['tweet_id'] == $_SESSION['login_member_id']){ ?> -->
              <?php if($tweet_each['member_id'] == $_SESSION['login_member_id']){ ?>
              [<a href="delete.php?tweet_id=<?php echo $tweet_each['tweet_id']; ?>" style="color: #F33;" onclick="return confirm('本当に削除しますか?');">削除</a>]
              <?php } ?>

              </p>
          </div>
        <?php } ?>
        <a href="index.php">&laquo;&nbsp;一覧へ戻る</a>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>
