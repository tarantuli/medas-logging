<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\ErrorReporting\ErrorReportingPackage;
use Medas\FileSystem\FileSystemPackage;
use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\ServiceManager\{ServiceConfigBuilder, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfigBuilder {
    $config = new ServiceConfigBuilder(ObjectInstantiator::class);

    $config->addPackages([
        ConfigManagerPackage::instance(),
        ConfigOptionsPackage::instance(),
        ErrorReportingPackage::instance(),
        FileSystemPackage::instance(),
    ]);

    return $config;
});
