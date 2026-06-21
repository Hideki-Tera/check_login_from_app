<?php
/**
 * Plugin Name: Login Check from App
 * Description: LaravelなどのフレームワークからWordPressのログイン状態を確認するためのエンドポイントを提供します
 * Version: 0.99
 * Author: Hideki.T
 * License: MIT
 */

if (!defined('ABSPATH')) exit;

// 設定定数（必要に応じてwp-config.phpで上書き可能）
if (!defined('LLC_ALLOWED_IP')) {
    define('LLC_ALLOWED_IP', '127.0.0.1');
}

if (!defined('LLC_QUERY_KEY')) {
    define('LLC_QUERY_KEY', 'check_login');
}

if (!defined('LLC_QUERY_VALUE')) {
    define('LLC_QUERY_VALUE', 'true');
}

add_action('template_redirect', function() {
    // クエリパラメータが一致しない場合はスルー
    if (!isset($_GET[LLC_QUERY_KEY]) || $_GET[LLC_QUERY_KEY] !== LLC_QUERY_VALUE) {
        return;
    }

    // IPアドレスチェック（127.0.0.1 または設定されたIP以外は403）
    $server_ip = gethostbyname(gethostname());
    $remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($remote_ip !== '127.0.0.1' && $remote_ip !== $server_ip && $remote_ip !== LLC_ALLOWED_IP) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    // ログイン状態と管理者権限を返す
    header('Content-Type: application/json');
    echo json_encode([
        'logged_in' => is_user_logged_in(),
        'role' => wp_get_current_user()->roles[0] ?? null,
    ]);
    exit;

});
