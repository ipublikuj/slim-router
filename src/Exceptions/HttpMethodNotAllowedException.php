<?php declare(strict_types = 1);

/**
 * HttpMethodNotAllowedException.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Exceptions
 * @since          0.1.0
 *
 * @date           15.03.20
 */

namespace IPub\SlimRouter\Exceptions;

use function implode;

class HttpMethodNotAllowedException extends HttpSpecializedException
{

	/** @var string[] */
	protected array $allowedMethods = [];

	/** @var int */
	protected $code = 405;

	/** @var string */
	protected $message = 'Method not allowed.';

	/** @var string */
	protected string $title = '405 Method Not Allowed';

	/** @var string */
	protected string $description = 'The request method is not supported for the requested resource.';

	/**
	 * @return string[]
	 */
	public function getAllowedMethods(): array
	{
		return $this->allowedMethods;
	}

	/**
	 * @param string[] $methods
	 *
	 * @return self
	 */
	public function setAllowedMethods(array $methods): HttpMethodNotAllowedException
	{
		$this->allowedMethods = $methods;
		$this->message = 'Method not allowed. Must be one of: ' . implode(', ', $methods);

		return $this;
	}

}
