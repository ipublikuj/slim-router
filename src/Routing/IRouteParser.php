<?php declare(strict_types = 1);

/**
 * IRouteParser.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Routing
 * @since          0.1.0
 *
 * @date           14.03.20
 */

namespace IPub\SlimRouter\Routing;

use Psr\Http\Message\UriInterface;

interface IRouteParser
{

	/**
	 * Build the path for a named route excluding the base path
	 *
	 * @param string $routeName    Route name
	 * @param mixed[] $data        Named argument replacement data
	 * @param mixed[] $queryParams Optional query string parameters
	 *
	 * @return string
	 */
	public function relativeUrlFor(string $routeName, array $data = [], array $queryParams = []): string;

	/**
	 * Build the path for a named route including the base path
	 *
	 * @param string $routeName    Route name
	 * @param mixed[] $data        Named argument replacement data
	 * @param mixed[] $queryParams Optional query string parameters
	 *
	 * @return string
	 */
	public function urlFor(string $routeName, array $data = [], array $queryParams = []): string;

	/**
	 * Get fully qualified URL for named route
	 *
	 * @param UriInterface $uri
	 * @param string $routeName    Route name
	 * @param mixed[] $data        Named argument replacement data
	 * @param mixed[] $queryParams Optional query string parameters
	 *
	 * @return string
	 */
	public function fullUrlFor(UriInterface $uri, string $routeName, array $data = [], array $queryParams = []): string;

}
