<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit569a23795674d52901ab1826a892f63e
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'Virtualorz\\Permission\\' => 22,
            'Virtualorz\\ActionLog\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Virtualorz\\Permission\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Virtualorz\\ActionLog\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit569a23795674d52901ab1826a892f63e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit569a23795674d52901ab1826a892f63e::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
