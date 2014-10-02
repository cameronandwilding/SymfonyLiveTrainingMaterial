<?php

namespace SymfonyLive\EventListener;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

class RouterListener {

  public function onKernelRequest(GetResponseEvent $getResponseEvent) {
    $request = $getResponseEvent->getRequest();

    $locator = new FileLocator(__DIR__ . '/../../../config');
    $loader = new YamlFileLoader($locator);
    $router = new Router($loader, 'routing.yml');
    $params = $router->matchRequest($request);
    $request->attributes->add($params);
  }

}
