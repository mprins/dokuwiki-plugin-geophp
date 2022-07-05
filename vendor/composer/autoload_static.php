<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8c920a9ce68d92f821701ab2e03316d3
{
    public static $prefixLengthsPsr4 = array (
        'g' => 
        array (
            'geoPHP\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'geoPHP\\' => 
        array (
            0 => __DIR__ . '/..' . '/funiq/geophp/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8c920a9ce68d92f821701ab2e03316d3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8c920a9ce68d92f821701ab2e03316d3::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit8c920a9ce68d92f821701ab2e03316d3::$classMap;

        }, null, ClassLoader::class);
    }
}
