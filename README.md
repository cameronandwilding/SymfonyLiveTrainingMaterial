Symfony - Building the AppKernel
================================

**Slides**
- https://speakerdeck.com/jakzal/into-the-kernel-and-back

**Code example**
- https://github.com/cameronandwilding/SymfonyLiveTrainingMaterial/commits/master (consume code example per commit to see the evolution)


Composer
--------

- create new web folder and init composer: https://getcomposer.org/ 

```
$ composer init --name symfonylive/workshop --description SymfonyLiveWorkshop --license proprietary --require symfony/http-kernel:~2.3 --no-interaction
```

- add autoload to composer.json and also create ./src folder:

```
"autoload": {
   "psr-4": {
       "": "src/"
   }
}
```

```
$ composer dump-autoload
$ composer install
```

- verify that ./vendor/autoload.php is created


HttpKernelInterface
-------------------

- http://symfony.com/doc/current/components/http_kernel/introduction.html 
- http://api.symfony.com/2.0/Symfony/Component/HttpFoundation/Request.html
- http://api.symfony.com/2.0/Symfony/Component/HttpFoundation/Response.html 
- implement HttpKernelInterface in src/SymfonyLive/HttpKernel/WorkshopKernel.php:

```PHP
namespace SymfonyLive\HttpKernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class WorkshopKernel implements HttpKernelInterface {

 public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
   $response = new Response('Hello World!');
   return $response;
 }

}
```

- add web app in web/app.php:

```PHP
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

$request = Request::createFromGlobals();
$kernel = new \SymfonyLive\HttpKernel\WorkshopKernel();
$response = $kernel->handle($request);
$response->send();
```

- load it in a browser


Routing
-------

- http://symfony.com/doc/current/book/routing.html 
- add routing:

```
$ composer require symfony/routing:~2.3
```

- move response creation to a new controller into src/SymfonyLive/Controller/HelloController.php:

```PHP
namespace SymfonyLive\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HelloController {

 public static function hello(Request $request) {
   return new Response('Hello: ' . $request->query->get('name'));
 }

}
```

- replace the HttpKernelInterface::handler code to a router handling code:

```PHP
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
```

- load url: PATH_TO_WEB_APP_PHP/hello?name=Jack


Advanced routing
----------------

- http://api.symfony.com/2.5/Symfony/Component/HttpKernel/Controller/ControllerResolverInterface.html 
- fetch composer items:

```
$ composer require symfony/config:~2.3 symfony/yaml:~2.3
```

- create router yaml in ./config/routing.yml

```
hello:
 path: /hello/{name}
 defaults:
   _controller: SymfonyLive\Controller\HelloController::hello
```

- add route param to HelloController:

```
public static function hello(Request $request, $name) {
 return new Response("Hello $name!");
}
```

- add route file loading and argument handling to HttpKernelInterface::handle:

```PHP
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
```

- load url: PATH_TO_WEB_APP_PHP/hello/Jack


Event dispatcher and dependency injection
-----------------------------------------

- http://symfony.com/doc/current/components/event_dispatcher/introduction.html 
- http://symfony.com/doc/current/cookbook/service_container/event_listener.html 
- create a new event listener and move routing logic there at SymfonyLive/EventListener/RouterListener.php:

```PHP
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
```

- create event dispatcher and controller resolver in app.php for injection into the kernel:

```PHP
use Symfony\Component\EventDispatcher\EventDispatcher;
...
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
...

$controllerResolver = new ControllerResolver();
$eventDispatcher = new EventDispatcher();

$eventDispatcher->addListener('kernel.request', array(new \SymfonyLive\EventListener\RouterListener(), 'onKernelRequest'));
...
$kernel = new WorkshopKernel($controllerResolver, $eventDispatcher);
...
```

- inject event dispatcher to the kernel:

```PHP
private $controllerResolver;

private $eventDispatcher;

public function __construct(ControllerResolver $controllerResolver, EventDispatcher $eventDispatcher) {
 $this->controllerResolver = $controllerResolver;
 $this->eventDispatcher = $eventDispatcher;
}
```

- use it in the Kernel's handle method:

```PHP
public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
 $getResponseEvent = new GetResponseEvent($this, $request, $type);
 $this->eventDispatcher->dispatch('kernel.request', $getResponseEvent);

 $controller = $this->controllerResolver->getController($request);
 $arguments = $this->controllerResolver->getArguments($request, $controller);

 $response = call_user_func_array($controller, $arguments);

 return $response;
}
```


Kernel view event
-----------------

- create a new route for returning simple objects (not response) in routing.yml:

```
hello_json:
 path: /hello/{name}/json
 defaults:
   _controller: SymfonyLive\Controller\HelloController::helloJSON
```

- add the controller handler into HelloController to return an object:

```PHP
public static function helloJSON(Request $request, $name) {
 return (object) array(
   'name' => $name,
   'IP' => $request->getClientIp(),
 );
```

- in the kernel call the kernel.view event when the response is not a real Response:

```PHP
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
```

- create a serializer listener to turn non responses into a Response into SymfonyLive/EventListener/SerializerListener:

```PHP
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
```

- listen to it from the app.php:

```PHP
$eventDispatcher->addListener('kernel.view', array(new SerializerListener(), 'onKernelView'));
```

- load url: PATH_TO_WEB_APP_PHP/hello/Jack/json


Services
--------

- add DI composer component:

```
$ composer require symfony/dependency-injection:~2.3
```

- create services yaml file at config/services.yml to define the class used in kernel creation:

```
services:
 controller_resolver:
   class: Symfony\Component\HttpKernel\Controller\ControllerResolver

 router_listener:
   class: SymfonyLive\EventListener\RouterListener

 serializer_listener:
   class: SymfonyLive\EventListener\SerializerListener

 event_dispatcher:
   class: Symfony\Component\EventDispatcher\EventDispatcher
   calls:
     - [addListener, [kernel.request, [@router_listener, onKernelRequest]]]
     - [addListener, [kernel.view, [@serializer_listener, onKernelView]]]

 workshop_kernel:
   class: SymfonyLive\HttpKernel\WorkshopKernel
   arguments: [@controller_resolver, @event_dispatcher]
```

- replace the code to the use of service container in app.php:

```PHP
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
```
