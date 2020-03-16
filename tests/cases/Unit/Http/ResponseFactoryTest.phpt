<?php declare(strict_types = 1);

namespace Tests\Cases;

use IPub\SlimRouter\Http;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

final class ResponseFactoryTest extends BaseMockeryTestCase
{

	public function testCreateResponse(): void
	{
		$factory = new Http\ResponseFactory();

		$response = $factory->createResponse(201);

		Assert::type(ResponseInterface::class, $response);
		Assert::same(201, $response->getStatusCode());
	}

}

$test_case = new ResponseFactoryTest();
$test_case->run();
