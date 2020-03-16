<?php declare(strict_types = 1);

/**
 * RequestHandler.php
 *
 * @license        More in license.md
 * @copyright      https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Routing
 * @since          0.1.0
 *
 * @date           15.03.20
 */

namespace IPub\SlimRouter\Routing\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * PSR-15 RequestHandler invocation strategy
 */
class RequestHandler implements IRequestHandler
{

	/** @var bool */
	private $appendRouteArgumentsToRequestAttributes;

	public function __construct(bool $appendRouteArgumentsToRequestAttributes = false)
	{
		$this->appendRouteArgumentsToRequestAttributes = $appendRouteArgumentsToRequestAttributes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function __invoke(
		callable $callable,
		ServerRequestInterface $request,
		ResponseInterface $response,
		array $routeArguments
	): ResponseInterface {
		if ($this->appendRouteArgumentsToRequestAttributes) {
			foreach ($routeArguments as $k => $v) {
				$request = $request->withAttribute($k, $v);
			}
		}

		return $callable($request);
	}

}
