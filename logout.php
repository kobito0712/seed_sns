<?php
// session変数を使うとき絶対必要
    session_start();
// セッション情報の削除(破棄)
// 中身が空の配列で上書き
    $_SESSION = array();
// セッションを呼び出すために使うクッキー(自分専用のidをブラウザ上に保存できる機能)情報の削除
    if(ini_get("session.use_cookies")){
      $params = session_get_cookie_params();
// クッキーの有効期限を過去にセットすると、既に無効な状態にできるので削除したのと同じ状態にできる(-42000は4万2千秒前を意味する)
      setcookie(session_name(),'',time()-42000,$params['path'],$params['domain'],$params['secuer'],$params['httponly']);
    }
// セッション情報を完全に消滅させる
    session_destroy();
    // まずはlogin.phpに設定して暫定的なチェックを行う
    header('Location:login.php');
// index.phpに戻る(ログインチェックのため)
    header('Location:index.php');
?>