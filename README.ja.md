# check_login_from_app

Laravel等他のフレームワークで作成したアプリケーションからWordPressのログイン状態と権限を確認するためのシンプルなプラグインです。

---

## 使用条件

Wordpressと他のフレームワークのCookieの範囲が同じであること
※通常、同一ドメイン配下であることが使用条件になります。

---

## 仕組み

```
クライアント（ブラウザ）
  │  WordPressのCookieの送出（当然、自動的に行われる）
  ▼
Laravel等のフレームワークのアプリケーション（サーバー）
  │  CookieをそのままWordPressエンドポイントに転送
  ▼
WordPress（同一サーバー）
  │  is_user_logged_in() でログイン状況確認
  │  wp_get_current_user()->roles　で権限を確認
  ▼
ログイン状況を
    True / False
権限を
    'administrator' => 5,
    'editor'        => 4,
    'author'        => 3,
    'contributor'   => 2,
    'subscriber'    => 1,
    null            => 非ログイン時
で返します。
```

クライアントのCookieをサーバー経由で転送するため、クライアントの状態を正確に反映し、改ざんの余地もありません。

---

## WordPress側のセットアップ

プラグインをダウンロードして、Wordpressの管理画面からインストール。インストール後有効化してください。

---

### カスタマイズ（wp-config.php）

```php
// アクセスを許可するIP（デフォルト: 127.0.0.1）
define('LLC_ALLOWED_IP', '127.0.0.1');

// クエリパラメータのキー（デフォルト: check_login）
define('LLC_QUERY_KEY', 'check_login');

// クエリパラメータの値（デフォルト: true）
define('LLC_QUERY_VALUE', 'true');
```

---

### エンドポイントURL例

```
https://example.com/?check_login=true
```

---

### 例）LaravelでWordpressのログイン状態を確認したい場合

```php
use Illuminate\Support\Facades\Http;

$cookies = $request->header('Cookie');
$response = Http::withHeaders([
  'Cookie' => $cookies
])->get('https://example.com/?check_login=true');
$condition = json_decode( $response->body() );
if( $condition->logged_in ){
  //ログイン時
} else {
  //未ログイン時
}
if( $condition->role == 'administrator' ){
  // 管理者権限を持つユーザー
}
```

---

### 開発意図その他

他のフレームワークの機能をWordpressのユーザー限定で使わせたい。
Wordpressの管理画面から他のフレームワークの機能を利用できるようにしたい。
そういったニーズがあったので作成しました。

| 方法 | 問題点 |
|------|--------|
| `wp-load.php` をrequire | `__()` 等のグローバル関数がLaravelと競合する |
| サーバーからREST APIを叩く（Cookieなし） | サーバーはWPにログインしていないので常にFalse |
| クライアントからAPIを叩かせる | 結果を改ざんしてLaravelに渡せる |
| **クライアントのCookieを転送してエンドポイントを叩く** | **✅ クライアントの状態を正確に反映、改ざん不可** |

上記の状況から最善の方法をプラグイン化しました。
WordPress側の `is_user_logged_in()` の内部実装が変わっても、このプラグインは影響を受けません。Laravel側のコードも変更不要です。

※作成にあたってClaude codeも使用しております。
