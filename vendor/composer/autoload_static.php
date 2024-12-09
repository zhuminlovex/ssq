<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9d386fdcf80c2641b556b6d5f4593530
{
    public static $prefixLengthsPsr4 = array (
        'Z' => 
        array (
            'Zm\\Ssq\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Zm\\Ssq\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit9d386fdcf80c2641b556b6d5f4593530::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9d386fdcf80c2641b556b6d5f4593530::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9d386fdcf80c2641b556b6d5f4593530::$classMap;

        }, null, ClassLoader::class);
    }
}