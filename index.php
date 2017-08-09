<?php
// セッション変数を使うときは必ず書く
    session_start();
// データベースに接続
// 外部ファイルからの読み込み
    require('dbconnect.php');
// ログインチェック
// ログイン中とみなせる条件
// １．セッションにログインしている人のmember_idが保存されている
// ２．最後のアクションから１時間以内であること
    if(isset($_SESSION['login_member_id']) && ($_SESSION['time'] + 3600 > time())){
      // ログインしている
      // 最終アクション時間を更新
      $_SESSION['time'] = time();
      // 問題
      // login.phpを参考にログインしている人のデータを取得してください。
      $sql = 'SELECT * FROM `members` WHERE `member_id`=?';
      // 何故＄＿SESSIONを使うのか？
      // login.phpではメールアドレスとパスワードを打つ画面があるため、POST送信が使えたが、
      // index.phpではPOST送信されるデータがないため、$_POSTでは何のデータも受け取ることは出来ない
      $data = array($_SESSION['login_member_id']);
      $stmt = $dbh->prepare($sql);
      $stmt->execute($data);
      $record = $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      // ログインしていない
      header('Location: login.php');
      // 投稿を記録する(「つぶやく」ボタンをクリックしたとき)
    }
    if(!empty($_POST)){
        // つぶやき欄に何か書かれていたら、DBに値を登録する
        if($_POST['tweet'] != ''){
    // 投稿用INSERT文作成
    // ?はsqlインジェクションを防ぐため(サニタイズ)
    // reply_tweet_idは後で使用するため、0にしておく
        //$sql = 'INSERT INTO `tweets` SET `tweet`=?,`member_id`=?,`reply_tweet_id`=0,`created`=NOW()';
      //replytweetidが受けとれるようにする
          $sql = 'INSERT INTO `tweets` SET `tweet`=?,`member_id`=?,`reply_tweet_id`=?,`created`=NOW()';
    // SQL文の実行
    // ?が二個あるので、$dataには２つの?を入れたい
          //$data = array($_POST['tweet'],$_SESSION['login_member_id']);
          //reply_tweet_id`=?のぶんも追加
          $data = array($_POST['tweet'],$_SESSION['login_member_id'],$_POST['reply_tweet_id']);
          $stmt = $dbh->prepare($sql);
          $stmt->execute($data);
          // 画面再表示(再送信防止)
          header('Location: index.php');
          exit();
        }
    }
    // つぶやき表示準備
    // 配列(復習)
    // $tweets = array('aaa','bbb','ccc');

    // SELSCT文作成(一覧表示用のデータを表示)
    // $sql = 'SELECT * FROM `tweets`;';
    // テーブル結合後のSELSCT文に書き換え
    // $sql = 'SELECT * FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id` ;
    // 作成日が新しい順盤になるように表示
    // DESC 降順(数字が大きいものから小さいものに並べる),ASC(省略可) 昇順(数字が小さいものから大きいものに並べる)
    //名前も並べ替えることができる(アスキーコード順になる仕組み)
    // $sql ='SELECT * FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id` ORDER BY `tweets`.`created` DESC';
    // delete_flagを使って論理削除機能をつける
    // $sql ='SELECT * FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id` WHERE `tweets`.`delete_flag`= 0 ORDER BY `tweets`.`created` DESC ';

// ページング機能
    $page = '';
// パラメータが存在したら、ページ番号を取得
    if(isset($_GET['page'])){
      $page = $_GET['page'];
    }
// パラメータが存在しない場合は、ページ番号を1とする
    if($page == ''){
      $page = 1;
    }

// 1以下のイレギュラーな数値が入ってきた場合はページ番号を1とする
// max(中の複数の数値の中で最大の数値を返す関数)
// max(-1,1)という指定の場合、大きい方の1が結果として返される
    $page = max($page,1) ;

// データの件数から最大ページ数を計算する
    $max_page = 0;
// データベースから件数を取得する
    $sql ='SELECT COUNT(*)AS `cnt` FROM `tweets` WHERE `delete_flag`=0' ;
// SQL文の実行、取得したデータをvar_dumpで表示
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $cnt = $stmt->fetch(PDO::FETCH_ASSOC);
    var_dump($cnt['cnt']);
// 次の5件を表示ボタンの準備
    $start = 0 ;
    // 1ページ目：0
    // 2ページ目：5
    // 3ページ目；10

// 1ページに何個つぶやきを取得するか指定
    $tweet_number = 10 ;

    // 最大ページ数を計算(小数点を切り上げ)
    $max_page = ceil($cnt['cnt'] / $tweet_number) ;

// パラメータのページ番号が最大ページ数を超えていれば、最後のページ数に設定する
// min():指定された複数の数値の中で最小の数値を返す関数
// min(100,3)と指定されていたら、3を返す
    $page = min($page,$max_page) ;

    $start = ($page -1) * $tweet_number;

    // 1ページに5件だけ表示するようにする
    // $sql ='SELECT * FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id` WHERE `tweets`.`delete_flag`= 0 ORDER BY `tweets`.`created` DESC limit 0,5';
    // sprintfを用いて書き換え
    $sql =sprintf('SELECT * FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id` WHERE `tweets`.`delete_flag`= 0 ORDER BY `tweets`.`created` DESC limit %d,%d',$start,$tweet_number);

    // SQL文の実行
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    // ステップ２
    // データを取得して配列に保存
    while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
      // $recordにfalseが代入されたとき、処理が終了します(データの一番最後まで取得してしまい、次に取得するデータが存在しないとき)
      // $tweets[]:配列に新しいデータを追加
      // $tweets[] = array(
      //   "tweet"=>$record['tweet'],
      //   "nick_name"=>'Seedkun',
      //   "created"=>$record['created'],
      //   "tweet_id"=>$record['tweet_id']
      //   );

      // like数の取得
      // データベースからtweet_idを取ってくるだけなので、サニタイズする必要なし
      $sql = 'SELECT COUNT(*)AS `like_count` FROM `likes` WHERE `tweet_id`= '.$record['tweet_id'];
// SQL文の実行
    $stmt_cnt = $dbh->prepare($sql);
    $stmt_cnt->execute();
    $like_cnt = $stmt_cnt->fetch(PDO::FETCH_ASSOC);

          // like状態の取得(ログインユーザーごと)
    // ANDの前にスペースを入れる
      $sql = 'SELECT COUNT(*)AS `like_count` FROM `likes`
      WHERE `tweet_id`= '.$record['tweet_id'].' AND `member_id`='.$_SESSION['login_member_id'];
// SQL文の実行
    $stmt_flag = $dbh->prepare($sql);
    $stmt_flag->execute();
    $like_flag_cnt = $stmt_flag->fetch(PDO::FETCH_ASSOC);

// お気に入り指定されているか、いないか条件分岐
    if($like_flag_cnt['like_count'] == 0){
      $like_flag = false;
    }else{
      $like_flag = true ;
    }

      // nick_nameも取得したい→ニックネームのデータはtweetsテーブルにはない(membersテーブルにある)→テーブル結合を用いる
      // 書き方:SELECT * FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id`
      // $tweets[] = array(
      //   "tweet"=>$record['tweet'],
      //   "nick_name"=>$record['nick_name'],
      //   "created"=>$record['created'],
      //   "tweet_id"=>$record['tweet_id'],
      //   "picture_path"=>$record['picture_path'],
      //   "reply_tweet_id"=>$record['reply_tweet_id'],
      //   "member_id"=>$record['member_id']
      //   );
    // like数を取得するためのデータも追加
      $tweets[] = array(
        "tweet"=>$record['tweet'],
        "nick_name"=>$record['nick_name'],
        "created"=>$record['created'],
        "tweet_id"=>$record['tweet_id'],
        "picture_path"=>$record['picture_path'],
        "reply_tweet_id"=>$record['reply_tweet_id'],
        "member_id"=>$record['member_id'],
        "like_flag"=>$like_flag,
        "like_count"=>$like_cnt['like_count']
        );

    }

    // 復習(連想配列)
    // $tweets = array(array("tweet" => "はろー1","nick_name" => "seedkun","created" => "2017-07-13","tweet_id" => 1),
              // array("tweet" => "おらしんのすけだぞ","nick_name" => "shinchan","created" => "2017-07-13","tweet_id" => 2),
              // array("tweet" => "カツオー","nick_name" => "sazaesan","created" => "2017-07-13","tweet_id" => 3)
      // );
    // var_dump($tweets[0]);
    // var_dump($tweets[1]);
    // var_dump($tweets[2]);

    // $tweet_eachを使って、一覧のつぶやきの内容を書き換えてください
    //返信ボタン(re)が押された時の処理
    if(isset($_GET['tweet_id'])){
    //返信したいつぶやきデータを取得(ニックネームも一緒に)
    //SQL文作成
      $sql = 'SELECT * FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id` WHERE `tweet_id`=?';
    //SQL文実行
      $data = array($_GET['tweet_id']);
      $stmt = $dbh->prepare($sql);
      $stmt->execute($data);
    //データ取得

      // while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
      // // $recordにfalseが代入されたとき、処理が終了します(データの一番最後まで取得してしまい、次に取得するデータが存在しないとき)
      // // $tweets[]:配列に新しいデータを追加
      //   $tweets[] = array(
      //     "tweet"=>$record['tweet'],
      //     "nick_name"=>$record['nick_name'],
      //     "created"=>$record['created'],
      //     "tweet_id"=>$record['tweet_id'],
      //     "picture_path"=>$record['picture_path']
      //    );
      //   }
      $record = $stmt->fetch(PDO::FETCH_ASSOC);
    //テキストエリアに表示する文字を作成(@返信したいつぶやき(つぶやいた人のニックネーム))
      // 文字列連結を用いて@つぶやき(ニックネーム)を$re_strに代入
      $re_str = '@'.$record["tweet"].'('.$record["nick_name"].')';
      // whileを用いた場合
      //$re_str = '@'.$tweets[0]["tweet"].'('.$tweets[0]["nick_name"].')';
      $reply_tweet_id = $_GET['tweet_id'] ;
      var_dump($re_str);
    }else{
      $reply_tweet_id = 0 ;
    }
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
<!--   <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          Brand and toggle get grouped for better mobile display
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.html"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          Collect the nav links, forms, and other content for toggling
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.php">ログアウト</a></li>
              </ul>
          </div>
          /.navbar-collapse
      </div>
      /.container-fluid
  </nav> -->
  <!-- ヘッダーを別ファイルから読み込む -->
  <!-- 外部ファイルから処理の読み込みにはrequireもある。ではrequireとの違いは？ -->
  <!-- requireは外部ファイル内でエラーが出ると、処理を中断する -->
  <!-- includeはエラーが外部ファイル内で発生したとしても処理を継続する。つまり表示系の処理におく使われる -->
  <!-- ※includeはphpファイルのみしか読み込めない -->
  <?php include('header.php') ?>

  <div class="container">
    <div class="row">
      <div class="col-md-4 content-margin-top">
        <legend>ようこそ<?php echo $record['nick_name'] ; ?>さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"><?php if(isset($re_str))
                {echo $re_str ;} ?>
                </textarea>
                <!-- inputタグを作ってreply_tweet_idをPOST送信で送れるようにする -->
                <input type="hidden" name="reply_tweet_id" value="<?php echo $reply_tweet_id; ?>">
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="つぶやく">
              &nbsp;&nbsp;&nbsp;&nbsp;
                <!-- リンク先を変更 -->
                <!-- <li><a href="index.html" class="btn btn-default">前</a></li> -->
                <!-- <li><a href="index.php?page=<?php //echo $page - 1; ?>" class="btn btn-default">前</a></li>
                &nbsp;&nbsp;|&nbsp;&nbsp; -->
                <!-- ページが1ページ目のときは前ボタンを押せなくする -->
            <li>
              <?php if($page > 1){ ?>
              <a href="index.php?page=<?php echo $page - 1; ?>" class="btn btn-default">前</a>
              <?php }else{ ?>
              前
              <?php } ?>
            </li>
              &nbsp;&nbsp;|&nbsp;&nbsp;
                <!-- <li><a href="index.html" class="btn btn-default">次</a></li> -->
                <!-- <li><a href="index.php?page=<?php //echo $page + 1; ?>"" class="btn btn-default">次</a></li> -->
                <!-- ページがこれ以上ないときは次ボタンを押せなくする -->
            <li>
              <?php if($page < $max_page){ ?>
              <a href="index.php?page=<?php echo $page + 1; ?>"" class="btn btn-default">次</a>
              <?php }else{ ?>
              次
              <?php } ?>
            </li>
          </ul>
        </form>
      </div>
<!--つぶやきの表示方法
    １DBからデータを取得
    ２取得したデータを配列に保存
    ３配列からデータを取り出して表示(データの個数分だけ繰り返し) -->
<!--       <?php //$tweet_each = $tweets[0]; ?>
        <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="48" height="48">
     <p>
            つぶやき4<span class="name"> (Seed kun) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.php">
              2016-01-28 18:03
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p> -->

          <!-- ステップ１ -->
          <!-- $tweet_eachを使って、一覧のつぶやきの内容を書き換えてください -->
          <!-- <p>
            <?php //echo $tweet_each['tweet']; ?><span class="name"> (<?php //echo $tweet_each['nick_name']; ?>) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
          <a href="view.php">
            <a href="view.php?tweet_id=<?php //echo $tweet_each['tweet_id']; ?>">
              <?php //echo $tweet_each['created']; ?>
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div> -->

<!--         <?php //$tweet_each = $tweets[1]; ?> -->
        <!-- この下に２番目の連想配列のデータを使ったつぶやきを表示記述 -->
<!--         <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="48" height="48">
          <p>
            <?php //echo $tweet_each['tweet']; ?><span class="name"> (<?php //echo $tweet_each['nick_name']; ?>) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.php?tweet_id=<?php //echo $tweet_each['tweet_id']; ?>">
              <?php //echo $tweet_each['created']; ?>

            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
        <?php //$tweet_each = $tweets[2]; ?>
        <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="48" height="48">
          <p>
            <?php //echo $tweet_each['tweet']; ?><span class="name"> (<?php //echo $tweet_each['nick_name']; ?>) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.php?tweet_id=<?php //echo $tweet_each['tweet_id']; ?>">
              <?php //echo $tweet_each['created']; ?>
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div> -->
        <!-- ステップ３ -->
        <!-- 上の記述を繰り返し処理を用いて記述し直す -->
      <div class="col-md-8 content-margin-top">
      <!-- foreach:$tweets配列の中から$tweet_eachをキーとして繰り返し処理 -->
        <?php foreach ($tweets as $tweet_each) { ?>
        <div class="msg">
          <img src="member_picture/<?php echo $tweet_each['picture_path']; ?>" width="48" height="48">
          <p>
            <?php echo $tweet_each['tweet']; ?><span class="name"> (<?php echo $tweet_each['nick_name']; ?>) </span>
      <!-- リンク先を変更、reを押したらurlにtweet_idが反映されるようにする-->
      <!-- [<a href="#">Re</a>] -->
            [<a href="index.php?tweet_id=<?php echo $tweet_each['tweet_id']; ?>">Re</a>]
          </p>
          <p class="day">
            <a href="view.php?tweet_id=<?php echo $tweet_each['tweet_id']; ?>">
              <?php echo $tweet_each['created']; ?>
            </a>
            <!-- つぶやいた人だけが編集削除ボタンを押せるようにする -->
            <?php if($tweet_each['member_id'] == $_SESSION['login_member_id']){ ?>
            <!-- [<a href="#" style="color: #00994C;">編集</a>][<a href="#" style="color: #F33;" >削除</a>] -->
            <!-- [<a href="#" style="color: #00994C;">編集</a>][<a href="#" style="color: #F33;" onclick="return confirm('本当に削除しますか？')">削除</a>] -->
            <!-- 削除ボタンを押した時にそのtweet_idを取得してdelete.phpで取得できるようにする -->
            <!-- リンク先ファイル?送りたい値でGET送信できる -->
            [<a href="edit.php?tweet_id=<?php echo $tweet_each['tweet_id']; ?>" style="color: #00994C;">編集</a>][<a href="delete.php?tweet_id=<?php echo $tweet_each['tweet_id']; ?>" style="color: #F33;" onclick="return confirm('本当に削除しますか？')">削除</a>]
            <?php } ?>
            <!-- お気に入りボタンを作る -->
            <!-- font-awesomeを使ってアイコンも作る -->
            <small><i class="fa fa-thumbs-up"></i><?php echo $tweet_each['like_count']  ?></small>
            <!-- empty:中身が空でもfalseでも処理を行える -->
            <!-- $tweet_each['like_flag'] == falseでも可 -->
            <?php if(empty($tweet_each['like_flag'])){ ?>
              <a href="like.php?tweet_id=<?php echo $tweet_each['tweet_id']; ?>"><small>いいね！</small></a>
            <?php  }else{ ?>
            <a href="unlike.php?tweet_id=<?php echo $tweet_each['tweet_id']; ?>"><small>いいね!を取り消す</small></a>
            <?php } ?>
            <!-- 返信元のつぶやきを押したらそこに飛べるようにする -->
            <!-- <a href="view.php?tweet_id=<?php //echo $tweet_each['tweet_id']; ?>">返信元のつぶやき</a> -->
            <!-- reply_tweet_idがないときは返信元のつぶやきを表示しない -->
            <?php if(!empty($tweet_each['reply_tweet_id'])){ ?>
            <a href="view.php?tweet_id=<?php echo $tweet_each['reply_tweet_id']; ?>">返信元のつぶやき</a>
            <?php } ?>
          </p>
        </div>
        <?php } ?>
      </div>

    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>
