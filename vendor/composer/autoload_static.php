<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc44e5ac729cb9f81a689ee15bff0603d
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
            $loader->prefixLengthsPsr4 = ComposerStaticInitc44e5ac729cb9f81a689ee15bff0603d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc44e5ac729cb9f81a689ee15bff0603d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc44e5ac729cb9f81a689ee15bff0603d::$classMap;

        }, null, ClassLoader::class);
    }
}
