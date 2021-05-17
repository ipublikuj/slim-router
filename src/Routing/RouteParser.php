<?php declare(strict_types = 1);

/**
 * RouteParser.php
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

use FastRoute\RouteParser\Std;
use IPub\SlimRouter\Exceptions;
use Psr\Http\Message\UriInterface;

class RouteParser implements IRouteParser
{

	/** @var IRouter */
	private IRouter $router;

	/** @var Std */
	private Std $routeParser;

	public function __construct(IRouter $router)
	{
		$this->router = $router;
		$this->routeParser = new Std();
	}

	/**
	 * {@inheritDoc}
	 */
	public function relativeUrlFor(string $routeName, array $data = [], array $queryParams = []): string
	{
		$route = $this->router->getNamedRoute($routeName);

		if ($route === null) {
			throw new Exceptions\InvalidArgumentException('Route could not be found in storage');
		}

		$pattern = $route->getPattern();

		$segments = [];
		$segmentName = '';

		/*
		 * $routes is an associative array of expressions representing a route as multiple segments
		 * There is an expression for each optional parameter plus one without the optional parameters
		 * The most specific is last, hence why we reverse the array before iterating over it
		 */
		$expressions = array_reverse($this->routeParser->parse($pattern));

		foreach ($expressions as $expression) {
			foreach ($expression as $segment) {
				/*
				 * Each $segment is either a string or an array of strings
				 * containing optional parameters of an expression
				 */
				if (is_string($segment)) {
					$segments[] = $segment;
					continue;
				}

				/*
				 * If we don't have a data element for this segment in the provided $data
				 * we cancel testing to move onto the next expression with a less specific item
				 */
				if (!array_key_exists($segment[0], $data)) {
					$segments = [];
					$segmentName = $segment[0];
					break;
				}

				$segments[] = $data[$segment[0]];
			}

			/*
			 * If we get to this logic block we have found all the parameters
			 * for the provided $data which means we don't need to continue testing
			 * less specific expressions
			 */
			if ($segments !== []) {
				break;
			}
		}

		if ($segments === []) {
			throw new Exceptions\InvalidArgumentException('Missing data for URL segment: ' . $segmentName);
		}

		$url = implode('', $segments);

		if ($queryParams !== []) {
			$url .= '?' . http_build_query($queryParams);
		}

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function urlFor(string $routeName, array $data = [], array $queryParams = []): string
	{
		$basePath = $this->router->getBasePath();
		$url = $this->relativeUrlFor($routeName, $data, $queryParams);

		if ($basePath !== '') {
			$url = $basePath . $url;
		}

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function fullUrlFor(UriInterface $uri, string $routeName, array $data = [], array $queryParams = []): string
	{
		$path = $this->urlFor($routeName, $data, $queryParams);
		$scheme = $uri->getScheme();
		$authority = $uri->getAuthority();
		$protocol = ($scheme !== '' ? $scheme . ':' : '') . ($authority !== '' ? '//' . $authority : '');

		return $protocol . $path;
	}

}
