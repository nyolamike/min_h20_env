<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2fc33fbee59088ab62b7a97b1dbb04d4
{
    public static $prefixLengthsPsr4 = array (
        'l' => 
        array (
            'libphonenumber\\' => 15,
        ),
        'G' => 
        array (
            'Giggsey\\Locale\\' => 15,
        ),
        'E' => 
        array (
            'Emarref\\Jwt\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'libphonenumber\\' => 
        array (
            0 => __DIR__ . '/..' . '/giggsey/libphonenumber-for-php/src',
        ),
        'Giggsey\\Locale\\' => 
        array (
            0 => __DIR__ . '/..' . '/giggsey/locale/src',
        ),
        'Emarref\\Jwt\\' => 
        array (
            0 => __DIR__ . '/..' . '/emarref/jwt/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'Smtpapi' => 
            array (
                0 => __DIR__ . '/..' . '/sendgrid/smtpapi/lib',
            ),
            'SendGrid' => 
            array (
                0 => __DIR__ . '/..' . '/godpod/sendgrid/lib',
            ),
        ),
    );

    public static $classMap = array (
        'SimpleXLSX' => __DIR__ . '/..' . '/shuchkin/simplexlsx/src/SimpleXLSX.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2fc33fbee59088ab62b7a97b1dbb04d4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2fc33fbee59088ab62b7a97b1dbb04d4::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit2fc33fbee59088ab62b7a97b1dbb04d4::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit2fc33fbee59088ab62b7a97b1dbb04d4::$classMap;

        }, null, ClassLoader::class);
    }
}
