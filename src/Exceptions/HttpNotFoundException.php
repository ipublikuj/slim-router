<?php declare(strict_types = 1);

/**
 * HttpNotFoundException.php
 *
 * @license        More in license.md
 * @copyright      https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Exceptions
 * @since          0.1.0
 *
 * @date           15.03.20
 */

namespace IPub\SlimRouter\Exceptions;

class HttpNotFoundException extends HttpSpecializedException
{

	/** @var int */
	protected $code = 404;

	/** @var string */
	protected $message = 'Not found.';

	/** @var string */
	protected $title = '404 Not Found';

	/** @var string */
	protected $description = 'The requested resource could not be found. Please verify the URI and try again.';

}
