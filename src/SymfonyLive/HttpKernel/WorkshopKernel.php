<?php

namespace SymfonyLive\HttpKernel;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

class WorkshopKernel implements HttpKernelInterface {

  /**
   * Handles a Request to convert it to a Response.
   *
   * When $catch is true, the implementation must catch all exceptions
   * and do its best to convert them to a Response instance.
   *
   * @param Request $request A Request instance
   * @param int $type The type of the request
   *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
   * @param bool $catch Whether to catch exceptions or not
   *
   * @return Response A Response instance
   *
   * @throws \Exception When an Exception occurs during processing
   *
   * @api
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    $locator = new FileLocator(__DIR__ . '/../../../config');
    $loader = new YamlFileLoader($locator);
    $router = new Router($loader, 'routing.yml');
    $params = $router->matchRequest($request);
    $request->attributes->add($params);

    $controllerResolver = new ControllerResolver();
    $controller = $controllerResolver->getController($request);
    $arguments = $controllerResolver->getArguments($request, $controller);

    $response = call_user_func_array($controller, $arguments);

    return $response;
  }

}
