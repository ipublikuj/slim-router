<?php declare(strict_types = 1);

namespace Tests\Cases;

use IPub\SlimRouter\Http;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

final class ResponseTest extends BaseMockeryTestCase
{

	public function testCreateTextResponse(): void
	{
		$response = Http\Response::text('Response content', 201);

		Assert::type(ResponseInterface::class, $response);
		Assert::same(201, $response->getStatusCode());
		Assert::same('Response content', (string) $response->getBody());
		Assert::true($response->hasHeader('Content-Type'));
		Assert::same('text/plain', $response->getHeaderLine('Content-Type'));
		Assert::false($response->hasHeader('Random-Header'));
	}

	public function testCreateHtmlResponse(): void
	{
		$response = Http\Response::html('Response content', 201);

		Assert::true($response->hasHeader('Content-Type'));
		Assert::same('text/html', $response->getHeaderLine('Content-Type'));
	}

	public function testCreateXmlResponse(): void
	{
		$response = Http\Response::xml('Response content', 201);

		Assert::true($response->hasHeader('Content-Type'));
		Assert::same('application/xml', $response->getHeaderLine('Content-Type'));
	}

	public function testCreateJsonResponse(): void
	{
		$response = Http\Response::json([
			'key_one' => 'val_one',
			'key_two' => 10,
		], 201);

		Assert::true($response->hasHeader('Content-Type'));
		Assert::same('application/json', $response->getHeaderLine('Content-Type'));
	}

	public function testCreateRedirectResponse(): void
	{
		$response = Http\Response::redirect('/redirect/link');

		Assert::false($response->hasHeader('Content-Type'));
		Assert::true($response->hasHeader('Location'));
		Assert::same('/redirect/link', $response->getHeaderLine('Location'));
	}

	public function testCreateNotFoundResponse(): void
	{
		$response = Http\Response::notFound();

		Assert::same(404, $response->getStatusCode());
		Assert::same('', (string) $response->getBody());
	}

}

$test_case = new ResponseTest();
$test_case->run();
