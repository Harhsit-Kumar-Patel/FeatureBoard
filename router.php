<?php
// router.php - Developmental router for the PHP built-in web server

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// 1. Serve root path
if ($uri === '/') {
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__ . '/public/index.html');
    exit;
}

// 2. Serve static files from the /public directory
$publicFile = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    $ext = pathinfo($publicFile, PATHINFO_EXTENSION);
    $contentTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'ico'  => 'image/x-icon',
        'svg'  => 'image/svg+xml',
        'html' => 'text/html'
    ];
    
    if (isset($contentTypes[$ext])) {
        header('Content-Type: ' . $contentTypes[$ext]);
    } else {
        // Fallback mime type detection
        $mime = mime_content_type($publicFile);
        if ($mime) {
            header('Content-Type: ' . $mime);
        }
    }
    
    readfile($publicFile);
    exit;
}

// 3. Route API endpoints to api.php
if (strpos($uri, '/api/') === 0) {
    require_once __DIR__ . '/api.php';
    exit;
}

// 4. Default 404 Response
header("HTTP/1.1 404 Not Found");
header('Content-Type: application/json');
echo json_encode(["error" => "Resource not found"]);
exit;
