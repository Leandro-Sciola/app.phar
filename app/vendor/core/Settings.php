<?php
/**
 * Dev
 *
 * @version 1.0.0
 */
namespace Core;

class Settings
{
    private static $dir = '';

    /**
     * Initialize the application
     *
     * @param string $dir
     * @return void
     * @access public
     */
    public static function init($dir) : void
    {
        ob_get_clean();
        define('APP_PATH', $dir);
        define('APP_NAME', basename($dir));
        define('FILE_NAME', basename($_SERVER["SCRIPT_FILENAME"]));
        define('FILE_EXT', pathinfo(FILE_NAME, PATHINFO_EXTENSION));
        self::$dir = $dir;
        self::php_ini();
        self::route();
    }

    /**
     * Define php.ini file settings
     *
     * @return void
     * @access private
     */
    private static function php_ini() : void
    {
        $list = parse_ini_file(self::$dir . '/php.ini', true)['PHP'];
        foreach ($list as $key => $value) {
            if (!str_contains($key, '#') || !str_contains($key, ';')) {
                ini_set($key, $value);
            }
        }
    }

    /**
     * Manage access to routes
     *
     * @return mixed
     * @access private
     */
    private static function route() : mixed
    {
        $request = '/';
        if (isset($_SERVER['REQUEST_URI'])) {
            $request = $_SERVER['REQUEST_URI'];
        }
        if ($request === '/') {
            return false;
        }
        if (FILE_EXT === 'phar') {
            $file = 'phar://' . APP_NAME . $request;
        } else {
            $file = APP_PATH . "/public$request";
        }
        if (file_exists($file)) {
            File::getStaticFile($file);
        } else {
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html><head><title>404 - Not found</title>',
                 '</head><body><h1 style="background: #ccc; padding: 25px">',
                 'HTTP Status - 404 - Not found</h1><p>The requested URL ',
                 'was not found on this server.</p><hr><address>' .
                 $_SERVER['REQUEST_URI'] . '</address></body></html>';
        }
        exit;
    }
}
