<?php declare(strict_types = 1);

namespace Tests\Cases;

use Fig\Http\Message\RequestMethodInterface;
use IPub\SlimRouter\Controllers;
use IPub\SlimRouter\Http;
use IPub\SlimRouter\Middleware;
use IPub\SlimRouter\Routing;
use Mockery;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

final class RouteTest extends BaseMockeryTestCase
{

	public function testRegisterRoute(): void
	{
		$routeCollector = Mockery::mock(Routing\IRouteCollector::class);
		$routeCollector
			->shouldReceive('getPattern')
			->withNoArgs()
			->andReturn('/prefix')
			->times(1);

		$responseFactory = Mockery::mock(ResponseFactoryInterface::class);

		$controllerResolver = Mockery::mock(Controllers\IControllerResolver::class);

		$invocationHandler = Mockery::mock(Routing\Handlers\IHandler::class);

		$route = new Routing\Route(
			[RequestMethodInterface::METHOD_GET],
			'/url/path',
			'Callable::method',
			$routeCollector,
			$responseFactory,
			$controllerResolver,
			$invocationHandler
		);

		Assert::same([RequestMethodInterface::METHOD_GET], $route->getMethods());
		Assert::same('/prefix/url/path', $route->getPattern());

		$route->setName('Route name');
		Assert::same('Route name', $route->getName());

		Assert::same('Callable::method', $route->getCallable());

		Assert::true(Uuid::isValid($route->getIdentifier()));
	}

	public function testRunRoute(): void
	{
		$routeCollector = Mockery::mock(Routing\IRouteCollector::class);
		$routeCollector
			->shouldReceive('appendMiddlewareToDispatcher')
			->withArgs(function ($middleware): bool {
				Assert::type(Middleware\MiddlewareDispatcher::class, $middleware);

				return true;
			})
			->times(1);

		$responseFactory = Mockery::mock(ResponseFactoryInterface::class);
		$responseFactory
			->shouldReceive('createResponse')
			->andReturnUsing(function (): ResponseInterface {
				return Http\Response::text('');
			})
			->times(1);

		$controllerResolver = Mockery::mock(Controllers\IControllerResolver::class);
		$controllerResolver
			->shouldReceive('resolve')
			->andReturn(function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
				$response->getBody()->write('NEW RESPONSE CONTENT');

				return $response;
			})
			->times(1);

		$invocationHandler = new Routing\Handlers\RequestResponseHandler();

		$serverRequest = Mockery::mock(ServerRequestInterface::class);

		$route = new Routing\Route(
			[RequestMethodInterface::METHOD_GET],
			'/url/path',
			'Callable::method',
			$routeCollector,
			$responseFactory,
			$controllerResolver,
			$invocationHandler
		);

		$response = $route->run($serverRequest);

		Assert::type(Http\Response::class, $response);
		Assert::same('NEW RESPONSE CONTENT', (string) $response->getBody());
	}

}

$test_case = new RouteTest();
$test_case->run();
