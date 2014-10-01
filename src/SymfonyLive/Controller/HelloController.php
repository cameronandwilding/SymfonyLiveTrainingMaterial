<?php

namespace SymfonyLive\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HelloController {

  public static function hello(Request $request, $name) {
    return new Response("Hello $name!");
  }

}
