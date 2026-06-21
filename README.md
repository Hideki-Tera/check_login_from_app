# check_login_from_app

WordPress plugin that returns login status and user role from external apps (Laravel, CakePHP, etc.).

---

## Requirements

The cookie scope must be the same between WordPress and the other framework.
※ Typically, this means both must be under the same domain.

---

## How It Works

Client (Browser)
  │  WordPress cookies are sent automatically
  ▼
Application built with Laravel or other frameworks (Server)
  │  Forwards cookies as-is to the WordPress endpoint
  ▼
WordPress (Same Server)
  │  Checks login status via is_user_logged_in()
  │  Checks user role via wp_get_current_user()->roles
  ▼
Returns login status:
    True / False
Returns user role:
    'administrator' => 5
    'editor'        => 4
    'author'        => 3
    'contributor'   => 2
    'subscriber'    => 1
    null            => not logged in

Since the client's cookies are forwarded via the server, the response accurately reflects the client's state and cannot be tampered with.

---

## WordPress Setup

Download the plugin and install it from the WordPress admin panel. After installation, activate it.

---

### Customization (wp-config.php)

```php
// IP address allowed to access (default: 127.0.0.1)
define('LLC_ALLOWED_IP', '127.0.0.1');

// Query parameter key (default: check_login)
define('LLC_QUERY_KEY', 'check_login');

// Query parameter value (default: true)
define('LLC_QUERY_VALUE', 'true');
```

---

### Endpoint URL Example

https://example.com/?check_login=true

---

### Example: Checking WordPress Login Status from Laravel

```php
use Illuminate\Support\Facades\Http;

$cookies = $request->header('Cookie');
$response = Http::withHeaders([
    'Cookie' => $cookies
])->get('https://example.com/?check_login=true');

$condition = json_decode( $response->body() );

if ($condition->logged_in) {
    // User is logged in
} else {
    // User is not logged in
}

if ($condition->role == 'administrator') {
    // User has administrator role
}
```

---

### Background and Intent

We needed a way to:
- Restrict features built with other frameworks to WordPress users only
- Allow features built with other frameworks to be triggered from the WordPress admin panel

The following approaches were considered and rejected:

| Method | Problem |
|--------|---------|
| `require wp-load.php` | Global functions like `__()` conflict with Laravel |
| Call REST API from server (without cookies) | Server is not logged into WordPress, always returns False |
| Have the client call the API | The client can tamper with the result before passing it to the server |
| **Forward client cookies to the endpoint** | **✅ Accurately reflects client state, cannot be tampered with** |

This plugin is the result of choosing the best approach above.
Even if WordPress changes the internal implementation of `is_user_logged_in()`, this plugin remains unaffected. No changes to the Laravel side are needed either.
