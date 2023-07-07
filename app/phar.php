#!/usr/bin/env php
<?php

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
                    new RecursiveDirectoryIterator(__DIR__ . '/static',
                        RecursiveDirectoryIterator::SKIP_DOTS));
        foreach($list as $path) {
            $path = explode('static', $path)[1];
            $file = __DIR__ . '/static' . $path;
            if (!is_dir($file)) {
                $phar[$path] = "<?php echo '" .
                                file_get_contents($file) . "'; ?>";
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

function include_file($file) {
    include_once 'phar://' . APP_NAME . "/$file";
}

function server($host, $port) {
    if (defined('APP_NAME')) {
        exec("(lsof -Pi :{$port} -sTCP:LISTEN -t >/dev/null) || " .
             "php -S {$host}:{$port} " . APP_NAME);
        ob_get_clean();
    } else {
        exit;
    }
}
