<?php
/*
 * This file is part of POOL (PHP Object-Oriented Library)
 *
 * (c) Alexander Manhart <alexander@manhart-it.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace pool\classes\Exception;

use RuntimeException as PhpRuntimeException;
use Throwable;

/**
 * Thrown to indicate that the argument received is not valid.
 */
class SessionDisabledException extends PhpRuntimeException implements PoolExceptionInterface
{
    public function __construct(string $message = 'Session is disabled', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}