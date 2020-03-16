<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControllerMethod
{

	public function someAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		return $response;
	}

}
