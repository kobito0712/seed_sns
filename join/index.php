<?php
    // $errorという変数を用意して、入力チェックに引っかかった項目の情報を保存する
    // $errorはhtmlの表示部分で、入力を促す表示を作る時に使用
// 例)もし、nick_nameに何も入っていなかった場合
// $error['nick_name'] = 'blank' ; という情報を保存

// SESSITIONを使うときには必ず一番上に指定する
  session_start();

// フォームからデーターが送信されたとき
if(!empty($_POST)){
  // エラー項目の確認
  // ニックネームが未入力
  if($_POST['nick_name'] == ''){
    $error['nick_name'] = 'blank' ;
  }
  // emailが未入力
  if($_POST['email'] == ''){
    $error['email'] = 'blank' ;
  }
  // パスワードが未入力
  if($_POST['password'] == ''){
    $error['password'] = 'blank' ;
    // パスワードは4文字以上という条件をつける
  }else{
    if(strlen($_POST['password']) < 4){
      $error['password'] = 'length' ;
    }
  }
  // 画像ファイルの拡張子チェック
  // jpg,gif,pngの3つのファイルを許可して他はエラーにする
  // ※注意：画像ファイルの拡張子を自分出て入力して変えないこと、画像サイズは2MB以下のものにすること
  // $_FILESはPOSTでアップロードされた値を取得するファイルアップロード変数
  // ['name']はファイル名を$_FILESに格納する方法
  $file_name = $_FILES['picture_path']['name'];
  // ファイルが指定された時に実行
  if(!empty($file_name)){
    // 拡張子を取得
    // $file_nameに「3.png」が代入されている場合、後ろ3文字を取得する
    // substr()は文字列と場所を指定して一部分切り出す関数
    // $ext = substr($file_name, -3);
    // ファイルの拡張子が対応していないときの処理
    // if($ext != 'jpg' && $ext != 'gif' && $ext != 'png'){
    //   $error['picture_path'] = 'type';
    // }
    // チャレンジ問題：チェックする拡張子にjpegを追加
    $ext = substr($file_name, -3);
    $ext2 = substr($file_name, -4);
    // $ext2を指定しない方法
    // if($ext != '.jpg' && $ext != '.gif' && $ext != '.png' && $ext2 != 'jpeg')
    if($ext != 'jpg' && $ext != 'gif' && $ext != 'png' && $ext2 != 'jpeg'){
      $error['picture_path'] = 'type';
    }

  }
  // エラーがない場合
  if (empty($error)) {

  // 画像をアップロードする
  // アップロード後のファイル名を作成
  // 日付とファイル名を文字連結
  // 日付を連結するメリット；他者が同じファイルを持っていた時に区別しやすくする
  // Y:年,4 桁の数字 m:月,数字,先頭にゼロをつける。 d:日,二桁の数字（先頭にゼロがつく場合も)
  // H:時,数字,24 時間単位 i:分,先頭にゼロをつける s:秒,先頭にゼロをつける
    $picture_path = date('YmdHis').$_FILES['picture_path']['name'];
  // $_FILES['picture_path']['tmp_name']:ファイル名、../member_picture:移動先
    move_uploaded_file($_FILES['picture_path']['tmp_name'],'../member_picture/'.$picture_path);

  // セッションに値を保存(一度POST送信を使っているため、もう一度POSTを使うことは難しい)
  // POST送信されたデータを代入
    $_SESSION['join'] = $_POST;
    $_SESSION['join']['picture_path'] = $picture_path;
  // SESSION:どの画面でもアクセス可能なスーパーグローバル変数

  // check.phpへ移動
  // 直接クリックをコードで
    header('Location: check.php');
    exit();
  // ここで処理が終了する
  }

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
    <link href="../assets/css/bootstrap.css" rel="stylesheet">
    <link href="../assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="../assets/css/form.css" rel="stylesheet">
    <link href="../assets/css/timeline.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <!--
      designフォルダ内では2つパスの位置を戻ってからcssにアクセスしていることに注意！
     -->

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
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3 content-margin-top">
        <legend>会員登録</legend>
        <!-- form method="post" action="" class="form-horizontal" role="form">
          フォームで画像を送信するときはentypeを使う -->
        <form method="post" action="" class="form-horizontal" role="form" enctype="multipart/form-data">
          ニックネーム
          <div class="form-group">
            <label class="col-sm-4 control-label">ニックネーム</label>
            <div class="col-sm-8">
<!-- <!       <input type="text" name="nick_name" class="form-control" placeholder="例： Seed kun"> -->
              <!-- 未入力時のエラー出力 -->
              <!-- 他の項目が未入力エラーの時に入力した内容が消えてしまう -->
              <!-- <?php //if(isset($error['nick_name']) && ($error['nick_name'] == 'blank')){ ?>
              <p class="error">* ニックネームを入力してください。</p>
              <?php // } ?> -->

              <!-- 以下で未入力項目があっても他の項目は消えないようにする -->
              <!--HTMLには特殊な意味（役割）を持っている文字（特殊文字）がある。「htmlspecialchars」はその文字をHTMLでの意味（役割）としてではなく、
              ただの文字（見た目は記号）として表示する（させる）ために文字参照値（という言い方が正しいかどうかは分からないが）に置き換える機能を持つ関数。
              フォームなどでユーザー（他者）からの悪意ある入力を無害化するのに用いられ、安全性を高めるためにも指定しておくことが望ましい。 -->

              <!-- 書き方：htmlspecialchars('文字列',エスケープの種類,'文字コード')-->
              <!-- ENT_QUOTESはシングルクォートとダブルクォートの両方を置き換える -->
<!--          isset:変数の存在を確認する
              empty:変数は存在する前提で、中身が空かどうか判定する関数
              emptyが認識するからの状態:null,0,false,'' -->
              <!-- 使い分け -->
              <!-- !empty($_POST):存在することは分かっていて中身を知りたい場合使用 -->
              <!-- isset($error['nick_name']):if文などの場合分けで -->

              <!-- $_POST['nick_name']が存在したら=POST送信されたデータの中にnick_nameの項目があったら=ユーザーがnicknameを入力して「確認」ボタンを押した後だったら -->
              <?php if(isset($_POST['nick_name'])){ ?>
              <input type="text" name="nick_name" class="form-control" placeholder="例： Seed kun"
              value="<?php echo htmlspecialchars($_POST['nick_name'],ENT_QUOTES,'utf-8'); ?>">
              <?php }else{ ?>
              <input type="text" name="nick_name" class="form-control" placeholder="例： Seed kun">
              <?php } ?>
              <!-- 本当は$error['nick_name'] == 'blank'の判定だけやりたい。しかしこれだけを記述した場合、$error['nick_name']が存在しない場合に文法エラーが画面に表示されてしまうので、条件の最初に存在チェックを付けている -->
              <?php if(isset($error['nick_name']) && ($error['nick_name'] == 'blank')){ ?>
              <p class="error">* ニックネームを入力してください。</p>
              <?php } ?>
            </div>
          </div>
          <!-- メールアドレス -->
          <div class="form-group">
            <label class="col-sm-4 control-label">メールアドレス</label>
            <div class="col-sm-8">
<!--         <input type="email" name="email" class="form-control" placeholder="例： seed@nex.com">
 -->         <!-- 未入力時のエラー出力 -->
              <!-- 他の項目が未入力エラーの時に入力した内容が消えてしまう -->
<!--          <?php // if(isset($error['email']) && ($error['email'] == 'blank')){ ?>
 -->         <!-- <p class="error">* メールアドレスを入力してください。</p> -->
<!--               <?php //} ?>

                  <! 以下で未入力項目があっても他の項目は消えないようにする -->
             <?php if(isset($_POST['email'])){ ?>
              <input type="text" name="email" class="form-control" placeholder="例： Seed kun"
              value="<?php echo htmlspecialchars($_POST['email'],ENT_QUOTES,'utf-8'); ?>">
              <?php }else{ ?>
              <input type="text" name="email" class="form-control" placeholder="例： Seed@nex.com">
              <?php } ?>
              <?php if(isset($error['email']) && ($error['email'] == 'blank')){ ?>
              <p class="error">* メールアドレスを入力してください。</p>
              <?php } ?>
            </div>
          </div>
          <!-- パスワード -->
          <div class="form-group">
            <label class="col-sm-4 control-label">パスワード</label>
            <div class="col-sm-8">
              <!-- <input type="password" name="password" class="form-control" placeholder="4文字以上入力"> -->
              <!-- 未入力時のエラー出力 -->
              <!-- 他の項目が未入力エラーの時に入力した内容が消えてしまう -->
              <!-- <?php //if(isset($error['password']) && ($error['password'] == 'blank')){ ?>
              <p class="error">* パスワードを入力してください。</p>
              <?php// } ?> -->

               <!-- 以下で未入力項目があっても他の項目は消えないようにする -->
               <?php if(isset($_POST['password'])){ ?>
               <input type="password" name="password" class="form-control" placeholder="4文字以上入力"
               value="<?php echo htmlspecialchars($_POST['password'],ENT_QUOTES,'utf-8'); ?>">
               <?php }else{ ?>
              <input type="password" name="password" class="form-control" placeholder="">
              <?php } ?>
               <?php if(isset($error['password']) && ($error['password'] == 'blank')){ ?>
              <p class="error">* パスワードを入力してください。</p>
              <?php } ?>
              <?php if(isset($error['password']) && ($error['password'] == 'length')){ ?>
              <p class="error">* パスワードは4文字以上入力してください。</p>
              <?php } ?>
            </div>
          </div>
          <!-- プロフィール写真 -->
          <div class="form-group">
            <label class="col-sm-4 control-label">プロフィール写真</label>
            <div class="col-sm-8">
            <!-- type="file"でボタンを作れる -->
            <!-- $error['picture_path']がtypeだったら、「ファイルはjpg、gif、pngのいずれかを指定してくださいというエラーメッセージを表示 -->
              <input type="file" name="picture_path" class="form-control">
              <?php if(isset($error['picture_path']) && ($error['picture_path'] == 'type')){ ?>
              <p class="error">* ファイルはjpg、gif、png、jpegのいずれかを指定してください。</p>
              <?php } ?>
            </div>
          </div>

          <input type="submit" class="btn btn-default" value="確認画面へ">
        </form>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../assets/js/jquery-3.1.1.js"></script>
    <script src="../assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="../assets/js/bootstrap.js"></script>
  </body>
</html>
