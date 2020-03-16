<?php declare(strict_types = 1);

/**
 * RouteGroup.php
 *
 * @license        More in license.md
 * @copyright      https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Routing
 * @since          0.1.0
 *
 * @date           14.03.20
 */

namespace IPub\SlimRouter\Routing;

use Psr\Http\Server\MiddlewareInterface;

class RouteGroup implements IRouteGroup
{

	/** @var string */
	private $pattern;

	/** @var IRouteCollector */
	private $routeCollector;

	public function __construct(
		string $pattern,
		IRouteCollector $routeCollector
	) {
		$this->pattern = $pattern;
		$this->routeCollector = $routeCollector;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMiddleware(MiddlewareInterface $middleware): void
	{
		$this->routeCollector->addMiddleware($middleware);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPattern(): string
	{
		return $this->pattern;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRouteCollector(): IRouteCollector
	{
		return $this->routeCollector;
	}

}
