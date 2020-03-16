<?php declare(strict_types = 1);

namespace Tests\Cases;

use IPub\SlimRouter\Controllers;
use IPub\SlimRouter\Routing;
use Mockery;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

final class RouteCollectorTest extends BaseMockeryTestCase
{

	public function testCreateRoutes(): void
	{
		$responseFactory = Mockery::mock(ResponseFactoryInterface::class);

		$controllerResolver = Mockery::mock(Controllers\IControllerResolver::class);

		$router = Mockery::mock(Routing\IRouter::class);

		$routeParser = new Routing\RouteParser($router);

		$routeCollector = new Routing\RouteCollector(
			$responseFactory,
			$controllerResolver,
			$routeParser
		);

		$route = $routeCollector->get('/get/route', 'Controller:getAction');
		$route->setName('get-route');

		$routeCollector->post('/post/route', 'Controller:postAction');

		Assert::same(2, count($routeCollector->getRoutes()));
		Assert::type(Routing\IRoute::class, $routeCollector->getNamedRoute('get-route'));

		$routeCollector->group('/group-pattern', function (Routing\IRouteCollector $group): void {
			$route = $group->get('/grouped/get/route', 'Controller::getAction');
			$route->setName('get-route-2');
		});

		Assert::same(3, count($routeCollector->getRoutes()));
		Assert::type(Routing\IRoute::class, $routeCollector->getNamedRoute('get-route-2'));
		Assert::same('/group-pattern/grouped/get/route', $routeCollector->getNamedRoute('get-route-2')->getPattern());

		$route = $routeCollector->getNamedRoute('get-route-2');

		Assert::type(Routing\IRoute::class, $routeCollector->lookupRoute($route->getIdentifier()));

		Assert::true($routeCollector->removeNamedRoute('get-route'));

		Assert::same(2, count($routeCollector->getRoutes()));
		Assert::null($routeCollector->getNamedRoute('get-route', false));

		Assert::true($routeCollector->removeNamedRoute('get-route-2'));

		Assert::same(1, count($routeCollector->getRoutes()));
		Assert::null($routeCollector->getNamedRoute('get-route-2', false));
	}

}

$test_case = new RouteCollectorTest();
$test_case->run();
