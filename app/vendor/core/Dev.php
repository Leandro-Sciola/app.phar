<?php
/**
 * Dev
 *
 * @version 1.0.0
 */
namespace Core;

class Dev
{
    /**
     * Web server for development
     *
     * @param string $host
     * @param integer $port
     * @return void
     * @access public
     */
    public static function server($host, $port) : void
    {
        if (defined('APP_NAME')) {
            if (FILE_EXT === 'phar') {
                $app = APP_NAME;
            } else {
                $app = '-t ' . APP_PATH . '/public ' . APP_PATH . '/index.php';
            }
            shell_exec("(lsof -Pi :{$port} -sTCP:LISTEN -t >/dev/null) || " .
                       "php -q -S {$host}:{$port} $app");
            self::debug(true);
        } else {
            exit;
        }
    }

    /**
     * Debug app in development environment
     *
     * @param boolean $status
     * @return void
     * @access public
     */
    public static function debug($status = false) : void
    {
        if ($status) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }
}
