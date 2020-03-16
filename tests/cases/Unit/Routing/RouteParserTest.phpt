<?php declare(strict_types = 1);

namespace Tests\Cases;

use IPub\SlimRouter\Routing;
use Mockery;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

final class RouteParserTest extends BaseMockeryTestCase
{

	public function testCreateRelativeUrl(): void
	{
		$route = Mockery::mock(Routing\IRoute::class);
		$route
			->shouldReceive('getPattern')
			->withNoArgs()
			->andReturn('/route/path/{id}')
			->times(1);

		$router = Mockery::mock(Routing\IRouter::class);
		$router
			->shouldReceive('getNamedRoute')
			->withArgs(['route-name'])
			->andReturn($route)
			->times(1);

		$routeParser = new Routing\RouteParser($router);

		$url = $routeParser->relativeUrlFor(
			'route-name',
			[
				'id' => 'id_value',
			]
		);

		Assert::same('/route/path/id_value', $url);
	}

	public function testCreateRelativeUrlWithQuery(): void
	{
		$route = Mockery::mock(Routing\IRoute::class);
		$route
			->shouldReceive('getPattern')
			->withNoArgs()
			->andReturn('/route/path/{id}')
			->times(1);

		$router = Mockery::mock(Routing\IRouter::class);
		$router
			->shouldReceive('getNamedRoute')
			->withArgs(['route-name'])
			->andReturn($route)
			->times(1);

		$routeParser = new Routing\RouteParser($router);

		$url = $routeParser->relativeUrlFor(
			'route-name',
			[
				'id' => 'id_value',
			],
			[
				'param' => 'one',
				'other' => [
					'test' => 'value',
				],
			]
		);

		Assert::same('/route/path/id_value?param=one&other%5Btest%5D=value', $url);
	}

	public function testCreateUrl(): void
	{
		$route = Mockery::mock(Routing\IRoute::class);
		$route
			->shouldReceive('getPattern')
			->withNoArgs()
			->andReturn('/route/path/{id}')
			->times(1);

		$router = Mockery::mock(Routing\IRouter::class);
		$router
			->shouldReceive('getNamedRoute')
			->withArgs(['route-name'])
			->andReturn($route)
			->times(1)
			->getMock()
			->shouldReceive('getBasePath')
			->withNoArgs()
			->andReturn('/route/prefix')
			->times(1);

		$routeParser = new Routing\RouteParser($router);

		$url = $routeParser->urlFor(
			'route-name',
			[
				'id' => 'id_value',
			]
		);

		Assert::same('/route/prefix/route/path/id_value', $url);
	}

}

$test_case = new RouteParserTest();
$test_case->run();
