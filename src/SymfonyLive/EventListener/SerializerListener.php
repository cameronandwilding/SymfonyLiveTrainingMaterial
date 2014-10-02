<?php

namespace SymfonyLive\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class SerializerListener {

  public function onKernelView(GetResponseForControllerResultEvent $getResponseForControllerResultEvent) {
    $json_encoded = json_encode($getResponseForControllerResultEvent->getControllerResult());
    $response = new Response($json_encoded);
    $getResponseForControllerResultEvent->setResponse($response);
  }

}
