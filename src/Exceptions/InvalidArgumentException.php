<?php declare(strict_types = 1);

/**
 * RuntimeException.php
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

use InvalidArgumentException as PHPInvalidArgumentException;

class InvalidArgumentException extends PHPInvalidArgumentException implements IException
{

}
