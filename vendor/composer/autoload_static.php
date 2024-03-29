<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5ddfcf229b392467ff1e79bfa7da82dd
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'ScssPhp\\ScssPhp\\' => 16,
        ),
        'E' => 
        array (
            'Everblock\\Tools\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ScssPhp\\ScssPhp\\' => 
        array (
            0 => __DIR__ . '/..' . '/scssphp/scssphp/src',
        ),
        'Everblock\\Tools\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5ddfcf229b392467ff1e79bfa7da82dd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5ddfcf229b392467ff1e79bfa7da82dd::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5ddfcf229b392467ff1e79bfa7da82dd::$classMap;

        }, null, ClassLoader::class);
    }
}
