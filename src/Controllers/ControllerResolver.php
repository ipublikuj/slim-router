<?php declare(strict_types = 1);

/**
 * ControllerResolver.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           14.04.19
 */

namespace IPub\SlimRouter\Controllers;

use IPub\SlimRouter\Exceptions;

/**
 * Endpoint controller callback resolver
 *
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class ControllerResolver implements IControllerResolver
{

	private const CALLABLE_PATTERN = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';

	/**
	 * {@inheritdoc}
	 */
	public function resolve($toResolve): callable
	{
		if (is_callable($toResolve)) {
			return $toResolve;
		}

		$resolved = $toResolve;

		if (is_string($toResolve)) {
			$resolved = $this->resolveClassNotation($toResolve);
			$resolved[1] = $resolved[1] ?? '__invoke';
		}

		return $this->assertCallable($resolved, $toResolve);
	}

	/**
	 * @param string $toResolve
	 *
	 * @return mixed[] [Instance, Method Name]
	 *
	 * @throws Exceptions\RuntimeException
	 */
	private function resolveClassNotation(string $toResolve): array
	{
		preg_match(self::CALLABLE_PATTERN, $toResolve, $matches);

		[$class, $method] = $matches ? [$matches[1], $matches[2]] : [$toResolve, null];

		if (!class_exists($class)) {
			throw new Exceptions\RuntimeException(sprintf('Callable %s does not exist', $class));
		}

		$instance = new $class();

		return [$instance, $method];
	}

	/**
	 * @param mixed $resolved
	 * @param mixed $toResolve
	 *
	 * @return callable
	 *
	 * @throws Exceptions\RuntimeException
	 */
	private function assertCallable($resolved, $toResolve): callable
	{
		if (!is_callable($resolved)) {
			throw new Exceptions\RuntimeException(sprintf(
				'%s is not resolvable',
				is_callable($toResolve) || is_object($toResolve) || is_array($toResolve) ?
					json_encode($toResolve) : $toResolve
			));
		}

		return $resolved;
	}

}
