<?php declare(strict_types = 1);

namespace Tests\Cases;

use IPub\SlimRouter\Controllers;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;
use Tests\Fixtures\ControllerMethod;

require_once __DIR__ . '/../../../bootstrap.php';

require_once __DIR__ . '/../../../fixtures/Controllers/ControllerMethod.php';
require_once __DIR__ . '/../../../fixtures/Controllers/ControllerInvoke.php';

final class ControllerResolverTest extends BaseMockeryTestCase
{

	public function testResolveString(): void
	{
		$resolver = new Controllers\ControllerResolver();

		$result = $resolver->resolve('\Tests\Fixtures\ControllerMethod:someAction');

		Assert::true(is_callable($result));

		$result = $resolver->resolve('\Tests\Fixtures\ControllerInvoke');

		Assert::true(is_callable($result));
	}

	public function testResolveCallable(): void
	{
		$resolver = new Controllers\ControllerResolver();

		$result = $resolver->resolve(function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
			return $response;
		});

		Assert::true(is_callable($result));
	}

	public function testResolveCallableArray(): void
	{
		$resolver = new Controllers\ControllerResolver();

		$controller = new ControllerMethod();

		$result = $resolver->resolve([$controller, 'someAction']);

		Assert::true(is_callable($result));
	}

}

$test_case = new ControllerResolverTest();
$test_case->run();
