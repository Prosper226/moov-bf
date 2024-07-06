<?php

// autoload_psr4.php @generated by Composer

$vendorDir = dirname(__DIR__);
$baseDir = dirname($vendorDir);

return array(
    'lab\\Service\\' => array($baseDir . '/src/Service'),
    'lab\\Operator\\' => array($baseDir . '/src/Operator'),
    'lab\\Logger\\' => array($baseDir . '/src/Logger'),
    'Psr\\Log\\' => array($vendorDir . '/psr/log/src'),
    'Psr\\Http\\Message\\' => array($vendorDir . '/psr/http-factory/src', $vendorDir . '/psr/http-message/src'),
    'Psr\\Http\\Client\\' => array($vendorDir . '/psr/http-client/src'),
    'Monolog\\' => array($vendorDir . '/monolog/monolog/src/Monolog'),
    'GuzzleHttp\\Psr7\\' => array($vendorDir . '/guzzlehttp/psr7/src'),
    'GuzzleHttp\\Promise\\' => array($vendorDir . '/guzzlehttp/promises/src'),
    'GuzzleHttp\\' => array($vendorDir . '/guzzlehttp/guzzle/src'),
);