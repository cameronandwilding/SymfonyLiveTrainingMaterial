<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

$request = Request::createFromGlobals();
$kernel = new \SymfonyLive\HttpKernel\WorkshopKernel();
$response = $kernel->handle($request);
$response->send();
