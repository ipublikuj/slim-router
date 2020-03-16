<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControllerInvoke
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		return $response;
	}

}
