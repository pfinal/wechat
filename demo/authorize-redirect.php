<?php

//middleUrl

//仅供开发环境使用，请验证跳转url安全性

$code = urlencode(isset($_GET['code']) ? $_GET['code'] : '');
$state = urlencode(isset($_GET['state']) ? $_GET['state'] : '');
$url = isset($_GET['url']) ? $_GET['url'] : '';

if (empty($url)) {
    echo 'error: url is empty.';
    exit;
}
$url = urldecode($url);

if (strpos($url, '?') === false) {
    $url .= '?';
} else {
    $url .= '&';
}

$url .= "code=$code&state=$state";

header('Location: ' . $url);

exit;