#!/usr/bin/env php
<?php

include_once 'vendor/autoload.php';

if (!pathinfo(basename(__DIR__), PATHINFO_EXTENSION)) {
    if (!isset($argv[1])) {
        echo shell_exec('php --define phar.readonly=0 ' . __DIR__ .
                        '/phar.php ' . basename(__DIR__) . '.phar');
    } else {
        compile($argv[1]);
    }
} else {
    define('APP_NAME', basename(__DIR__));
}

function php_ini() {
    $list = parse_ini_file('php.ini', true)['PHP'];
    foreach ($list as $key => $value) {
        if (!str_contains($key, '#') || !str_contains($key, ';')) {
            ini_set($key, $value);
        }
    }
}

function compile($pharFile) {
    try {
        // clean up
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        if (file_exists($pharFile . '.gz')) {
            unlink($pharFile . '.gz');
        }

        // create phar
        $phar = new Phar($pharFile);

        // start buffering. Mandatory to modify stub to add shebang
        $phar->startBuffering();

        // Get static files
        $list = new RecursiveTreeIterator(
                    new RecursiveDirectoryIterator(__DIR__ . '/public',
                        RecursiveDirectoryIterator::SKIP_DOTS));
        foreach($list as $path) {
            $path = explode('public', $path)[1];
            $file = __DIR__ . '/public' . $path;
            if (!is_dir($file)) {
                $phar[$path] = file_get_contents($file);
            }
        }

        // Create the default stub from main.php entrypoint
        $defaultStub = $phar->createDefaultStub('index.php');

        // Add the rest of the apps files
        $phar->buildFromDirectory(__DIR__);

        // Customize the stub to add the shebang
        $stub = "#!/usr/bin/env php \n" . $defaultStub;

        // Add the stub
        $phar->setStub($stub);

        $phar->stopBuffering();

        // plus - compressing it into gzip  
        $phar->compressFiles(Phar::GZ);

        # Make the file executable
        chmod(getcwd() . "/{$pharFile}", 0770);

        echo "$pharFile successfully created" . PHP_EOL;
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function get_public_file() {
    $request = '/';
    if (isset($_SERVER['REQUEST_URI'])) {
        $request = $_SERVER['REQUEST_URI'];
    }
    if ($request === '/') {
        return false;
    }
    $file = 'phar://' . APP_NAME . "/$request";
    if (file_exists($file)) {
        $type = array(
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'json'  => 'application/json',
            'xml'   => 'application/xml',
            'txt'   => 'text/plain',
            'html'  => 'text/html',
            'ttf'   => 'application/x-font-ttf',
            'woff'  => 'application/font-woff',
            'woff2' => 'application/font-woff2',
            'png'   => 'image/png',
            'jpe'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'jpg'   => 'image/jpeg',
            'gif'   => 'image/gif',
            'bmp'   => 'image/bmp',
            'ico'   => 'image/vnd.microsoft.icon',
            'tiff'  => 'image/tiff',
            'tif'   => 'image/tiff',
            'svg'   => 'image/svg+xml',
            'svgz'  => 'image/svg+xml',
            'zip'   => 'application/zip',
            'pdf'   => 'application/pdf',
            'mp3'   => 'audio/mpeg'
        );
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        header("Content-Type: {$type[$ext]}; charset=utf-8");
        include_once $file;
    } else {
        header("Content-Type: text/html; charset=utf-8");
        echo '<!DOCTYPE html><html><head><title>404 - Not found</title>',
             '</head><body><h1 style="background: #ccc; padding: 25px">',
             'HTTP Status - 404 - Not found</h1><p>The requested URL ',
             'was not found on this server.</p><hr><address>' .
             $_SERVER['REQUEST_URI'] . '</address></body></html>';
    }
    exit;
}

function server($host, $port) {
    if (defined('APP_NAME')) {
        shell_exec("(lsof -Pi :{$port} -sTCP:LISTEN -t >/dev/null) || " .
                   "php -q -S {$host}:{$port} " . APP_NAME);
        debug(true);
    } else {
        exit;
    }
}

function debug($status = false) {
    if ($status) {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    } else {
        error_reporting(0);
        ini_set('display_errors', '0');
    }
}
php_ini();
ob_get_clean();
get_public_file();
