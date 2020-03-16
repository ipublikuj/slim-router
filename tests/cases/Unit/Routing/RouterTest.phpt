<?php declare(strict_types = 1);

namespace Tests\Cases;

use Fig\Http\Message\RequestMethodInterface;
use IPub\SlimRouter\Routing;
use Mockery;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

require_once __DIR__ . '/../../../fixtures/Controllers/ControllerMethod.php';

final class RouterTest extends BaseMockeryTestCase
{

	public function testRunRoute(): void
	{
		$uri = Mockery::mock(UriInterface::class);
		$uri
			->shouldReceive('getPath')
			->andReturn('/get/route');

		$route = Mockery::mock(Routing\IRoute::class);
		$route
			->shouldReceive('run')
			->times(1);

		$request = Mockery::mock(ServerRequestInterface::class);
		$request
			->shouldReceive('getAttribute')
			->andReturnUsing(function ($key) use ($route) {
				if ($key === Routing\Router::ROUTING_RESULTS) {
					return null;

				} elseif ($key === Routing\Router::ROUTE) {
					return $route;
				}

				return null;
			})
			->getMock()
			->shouldReceive('getUri')
			->andReturn($uri)
			->getMock()
			->shouldReceive('getMethod')
			->andReturn(RequestMethodInterface::METHOD_GET)
			->getMock()
			->shouldReceive('withAttribute')
			->withArgs(function ($key, $value): bool {
				if ($key === Routing\Router::ROUTING_RESULTS) {
					Assert::type(Routing\RoutingResults::class, $value);

				} elseif ($key === Routing\Router::ROUTE) {
					Assert::type(Routing\Route::class, $value);
				}

				return true;
			})
			->andReturn($request);

		$router = new Routing\Router();

		$router->get('/get/route', '\Tests\Fixtures\ControllerMethod:someAction');

		$response = $router->handle($request);

		Assert::type(ResponseInterface::class, $response);
	}

}

$test_case = new RouterTest();
$test_case->run();
