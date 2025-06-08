<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang'])) {
    $lang = trim($_GET['lang']);
    
    if (preg_match('/^[a-zA-Z0-9\-_]+$/', $lang)) {
        $_SESSION['lang'] = $lang;
    }
}

$lang = $_SESSION['lang'] ?? 'en-US';

$lang_file = __DIR__ . '/../lang/' . $lang . '.php';

if (file_exists($lang_file)) {
    $translations = include($lang_file);
} else {
    $translations = include(__DIR__ . '/../lang/en-US.php');
}

function __($key) {
    global $translations;
    return $translations[$key] ?? $key;
}

function get_available_languages(): array {
    $langs = [];
    $lang_dir = __DIR__ . '/../lang/';
    foreach (glob($lang_dir . '*.php') as $file) {
        $code = basename($file, '.php');
        $data = include($file);
        if (isset($data['language_name'])) {
            $langs[$code] = $data['language_name'];
        } else {
            $langs[$code] = $code; 
        }
    }
    return $langs;
}
