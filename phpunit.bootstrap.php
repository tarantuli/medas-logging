<?php

declare(strict_types=1);

use Medas\ErrorLog\ErrorLogPackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        ErrorLogPackage::instance(),
    ]);

    return $config;
});
