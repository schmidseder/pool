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

/**
 * Thrown to indicate that a service is unavailable.
 */
class ServiceUnavailableException extends PhpRuntimeException implements PoolExceptionInterface {}