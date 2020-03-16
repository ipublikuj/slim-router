<?php declare(strict_types = 1);

/**
 * RoutingResults.php
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

namespace IPub\SlimRouter\Routing;

class RoutingResults
{

	public const NOT_FOUND = 0;
	public const FOUND = 1;
	public const METHOD_NOT_ALLOWED = 2;

	/** @var string */
	private $method;

	/** @var string */
	private $uri;

	/**
	 * The status is one of the constants shown above
	 *
	 * NOT_FOUND = 0
	 * FOUND = 1
	 * METHOD_NOT_ALLOWED = 2
	 *
	 * @var int
	 */
	private $routeStatus;

	/** @var string|null */
	private $routeIdentifier;

	/** @var mixed[] */
	private $routeArguments = [];

	/**
	 * @param string $method
	 * @param string $uri
	 * @param int $routeStatus
	 * @param string|null $routeIdentifier
	 * @param mixed[] $routeArguments
	 */
	public function __construct(
		string $method,
		string $uri,
		int $routeStatus,
		?string $routeIdentifier = null,
		array $routeArguments = []
	) {
		$this->method = $method;
		$this->uri = $uri;
		$this->routeStatus = $routeStatus;
		$this->routeIdentifier = $routeIdentifier;
		$this->routeArguments = $routeArguments;
	}

	/**
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

	/**
	 * @return string
	 */
	public function getUri(): string
	{
		return $this->uri;
	}

	/**
	 * @return int
	 */
	public function getRouteStatus(): int
	{
		return $this->routeStatus;
	}

	/**
	 * @return string|null
	 */
	public function getRouteIdentifier(): ?string
	{
		return $this->routeIdentifier;
	}

	/**
	 * @param bool $urlDecode
	 *
	 * @return mixed[]
	 */
	public function getRouteArguments(bool $urlDecode = true): array
	{
		if (!$urlDecode) {
			return $this->routeArguments;
		}

		$routeArguments = [];

		foreach ($this->routeArguments as $key => $value) {
			$routeArguments[$key] = rawurldecode($value);
		}

		return $routeArguments;
	}

}
