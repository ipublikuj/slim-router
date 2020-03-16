# Quickstart

Using this library is super-easy. Router is build on top of [Fast Route](https://github.com/nikic/FastRoute) library which is fast and stable

## Installation

The best way to install ipub/slim-router is using [Composer](http://getcomposer.org/):

```sh
$ composer require ipub/slim-router
```

## Creating routes

As first step, you have to create instance of the router:

```php
use IPub\SlimRouter\Routing;

$router = new Routing\Router();
```

and now you could register your routes:

#### GET route:

```php
$router->get('/product/{id}', function ($request, $response, $args) {
    // Load product details by $args['id']
});
```

#### POST route:

```php
$router->post('/products', function ($request, $response, $args) {
    // Create product details
});
```

#### PUT route:

```php
$router->put('/product/{id}', function ($request, $response, $args) {
    // Update product details by $args['id']
});
```

#### PATCH route:

```php
$router->patch('/product/{id}', function ($request, $response, $args) {
    // Update product details by $args['id']
});
```

#### DELETE route:

```php
$router->delete('/product/{id}', function ($request, $response, $args) {
    // Delete product details by $args['id']
});
```

#### OPTIONS route:

```php
$router->options('/product/{id}', function ($request, $response, $args) {
    // Return response headers
});
```

#### ANY route:

```php
$router->any('/product/{id}', function ($request, $response, $args) {
    // Apply changes to product details by $args['id']
    // To check which method is used: $request->getMethod();
});
```

### Custom route:

```php
$router->map(['GET', 'POST', 'OPTIONS'], '/products', function ($request, $response, $args) {
    // Handle reques
});
```

## Route controllers or handlers or callbacks

Each route have to have a handler which have to return a response. Handlers could be defined with callbacks or classes:

#### Closure callback

The easiest way how to configure handler:

```php
$router->get('/product/{id}', function ($request, $response, $args) {
    // Handle incoming request

    return $response;
});
```

#### Class handler

```php
$router->get('/product/{id}', 'App\Controller:methodName');
```

In this case a controller resolver try to search for class `App\Controller` and if class exist t will create new instance and call given method `methodName`

```php
$router->get('/product/{id}', 'App\Controller');
```

A method name is not required. You could omit it, but a method `__invoke` have to be define in given class

#### Array handler

```php
$controller = new App\Controller();

$router->get('/product/{id}', [$controller, 'methodName']);
```

This method is similar to previous, but in this case controller is not creating new instance, sou this method is suitable for loading controller with DI.

## Route names

One of main advantages of this library is ability to give a name to the routes and then use this names for route generation

```php
$route = $router->get('/product/{id}', 'App\Controller');
$route->setName('show-product');
```

and now with a name, route could be created:

```php
$url = $router->urlFor('show-product', ['id' => 10]);
```

and result could be used eg. in responses.

## Route groups

Groups are here to help you organize routes into logical groups.

```php
$router->group('/eshop', function (Routing\RouteCollector $group): void {
    $group->group('/products', function (Routing\RouteCollector $group): void {
        $group->get('/{id}', [$productsController, 'index']);

        $group->get('/{id}/comments', [$productsController, 'comments']);
    });

    $group->group('/orders', function (Routing\RouteCollector $group): void {
        $group->get('/{id}', [$ordersController, 'index']);
    });

});
```

## Route middleware

Each route or even each group could have custom middleware:

```php
$router->group('/eshop', function (Routing\RouteCollector $group): void {
    $group->group('/products', function (Routing\RouteCollector $group): void {
        $route = $group->get('/{id}', [$productsController, 'index']);

        $route->addMidleware(new RouteMiddlewareOne);
        $route->addMidleware(new RouteMiddlewareTwo);
    });

    $group->addMidleware(new GroupMiddleware);
});
```

When a route for `/eshop/products/{id}` is matched a `GroupMiddleware` is executed first and then a `RouteMiddlewareTwo` and as last is executed `RouteMiddlewareOne`

## Make router run

After configuring router, it could be executed by one command:

```php
$router->handle($request);
```

Where `$request` is instance of `\Psr\Http\Message\ServerRequestInterface` and is up to you how you create this variable.

eg. with ReactPHP/http:

```php
$loop = \React\EventLoop\Factory::create();

$server = new \React\Http\Server(function (\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface {
    try {
        return $this->router->handle($request);

    } catch (Throwable $ex) {
        // Handle error here
    }
});

$socket = new \React\Socket\Server('127.0.0.1:8000', $loop);
$server->listen($socket);

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

$loop->run();
```

## Error handling?

Are you asking, how errors are handled? This library does not handle error, so it is up to you, but it is very easy with middleware.

```php
class ErrorMiddleware implements \Psr\Http\Server\MiddlewareInterface
{
    public function process(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Server\RequestHandlerInterface $handler
    ): \Psr\Http\Message\ResponseInterface {
        try {
            return $handler->handle($request);

        } catch (\Throwable $ex) {
            // Log error ext & create response
            $errResponse = new Response();

            return $errResponse;
        }
    }
}
```

And instance of this middleware have to be passed to router:

```php
$router = new Routing\Router();

// ...router config

$router->addMiddleware(new ErrorMiddleware());
```

and it is recommended to register this middleware as last one, to be executed as first.
