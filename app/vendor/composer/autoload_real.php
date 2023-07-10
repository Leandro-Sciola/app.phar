<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit71e54ebd6aa4db333670dce309a0a4fc
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit71e54ebd6aa4db333670dce309a0a4fc', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit71e54ebd6aa4db333670dce309a0a4fc', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit71e54ebd6aa4db333670dce309a0a4fc::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}