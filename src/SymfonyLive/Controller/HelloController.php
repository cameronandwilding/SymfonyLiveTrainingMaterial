<?php

namespace SymfonyLive\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HelloController {

  public static function hello(Request $request) {
    return new Response('Hello: ' . $request->query->get('name'));
  }

}
