<?php

namespace SymfonyLive\HttpKernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

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
    $routes = new RouteCollection();
    $routes->add(
      'hello',
      new Route('/hello', array('_controller' => 'SymfonyLive\Controller\HelloController::hello'))
    );
    $requestContext = new RequestContext();
    $requestContext->fromRequest($request);
    $urlMatcher = new UrlMatcher($routes, $requestContext);
    $params = $urlMatcher->matchRequest($request);

    $response = call_user_func_array($params['_controller'], array($request));

    return $response;
  }

}
