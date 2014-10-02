<?php

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use SymfonyLive\HttpKernel\WorkshopKernel;

require_once __DIR__ . '/../vendor/autoload.php';

$controllerResolver = new ControllerResolver();
$eventDispatcher = new EventDispatcher();

$eventDispatcher->addListener('kernel.request', array(new \SymfonyLive\EventListener\RouterListener(), 'onKernelRequest'));

$request = Request::createFromGlobals();
$kernel = new WorkshopKernel($controllerResolver, $eventDispatcher);
$response = $kernel->handle($request);
$response->send();
