<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

$container = new ContainerBuilder();
$locator = new FileLocator(array(__DIR__ . '/../config'));
$loader = new YamlFileLoader($container, $locator);
$loader->load('services.yml');

$request = Request::createFromGlobals();

$kernel = $container->get('workshop_kernel');
$response = $kernel->handle($request);
$response->send();
