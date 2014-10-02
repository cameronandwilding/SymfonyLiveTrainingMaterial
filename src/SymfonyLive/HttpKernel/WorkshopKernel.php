<?php

namespace SymfonyLive\HttpKernel;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

class WorkshopKernel implements HttpKernelInterface {

  /**
   * @var \Symfony\Component\HttpKernel\Controller\ControllerResolver
   */
  private $controllerResolver;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcher
   */
  private $eventDispatcher;

  public function __construct(ControllerResolver $controllerResolver, EventDispatcher $eventDispatcher) {
    $this->controllerResolver = $controllerResolver;
    $this->eventDispatcher = $eventDispatcher;
  }

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
    $getResponseEvent = new GetResponseEvent($this, $request, $type);
    $this->eventDispatcher->dispatch('kernel.request', $getResponseEvent);

    $controller = $this->controllerResolver->getController($request);
    $arguments = $this->controllerResolver->getArguments($request, $controller);

    $response = call_user_func_array($controller, $arguments);

    if (!($response instanceof Response)) {
      $responseForControllerResultEvent = new GetResponseForControllerResultEvent($this, $request, $type, $response);
      $this->eventDispatcher->dispatch('kernel.view', $responseForControllerResultEvent);

      $response = $responseForControllerResultEvent->getResponse();

      if (!($response instanceof Response)) {
        throw new InvalidParameterException();
      }
    }

    return $response;
  }

}
