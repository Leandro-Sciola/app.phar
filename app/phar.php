#!/usr/bin/env php
<?php

if (!pathinfo(basename(__DIR__), PATHINFO_EXTENSION)) {
    if (!isset($argv[1])) {
        echo shell_exec('php --define phar.readonly=0 ' . __DIR__ .
                        '/phar.php ' . basename(__DIR__) . '.phar');
    } else {
        compile($argv[1]);
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
