<?php declare(strict_types = 1);

namespace Tests\Cases;

use IPub\SlimRouter\Routing;
use Mockery;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Psr\Http\Server\MiddlewareInterface;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

final class RouteGroupTest extends BaseMockeryTestCase
{

	public function testCreateGroup(): void
	{
		$routeCollector = Mockery::mock(Routing\IRouteCollector::class);
		$routeCollector
			->shouldReceive('addMiddleware')
			->withArgs(function ($middleware): bool {
				Assert::type(MiddlewareInterface::class, $middleware);

				return true;
			})
			->times(1);

		$group = new Routing\RouteGroup(
			'/group/pattern',
			$routeCollector
		);

		Assert::same('/group/pattern', $group->getPattern());
		Assert::type(Routing\IRouteCollector::class, $group->getRouteCollector());

		$middleware = Mockery::mock(MiddlewareInterface::class);

		$group->addMiddleware($middleware);
	}

}

$test_case = new RouteGroupTest();
$test_case->run();
