<?php declare(strict_types=1);
/*
 * This file is part of POOL (PHP Object-Oriented Library)
 *
 * (c) Alexander Manhart <alexander@manhart-it.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace pool\classes\Database;

use pool\classes\Core\PoolObject;

/**
 * @abstract abstract Data Interface
 */
abstract class DataInterface extends PoolObject
{

    /**
     * set options for the interface e.g. connection data
     *
     * @param array $connectionOptions options
     * @return bool
     */
    abstract public function setOptions(array $connectionOptions): bool;

    /**
     * get the driver name of the interface. the name is used to get the connection data from the config and for namespaces of the data access objects
     * @return string
     */
    abstract static function getDriverName(): string;

    /**
     * factory method to create a data interface
     *
     * @param array $connectionOptions
     * @param string|null $interfaceType
     * @return DataInterface
     */
    public static function createDataInterface(array $connectionOptions, ?string $interfaceType = null): DataInterface
    {
        $DataInterface = $interfaceType? new $interfaceType() : new static();
        assert($DataInterface instanceof DataInterface);
        $DataInterface->setOptions($connectionOptions);
        return $DataInterface;
    }

    /**
     * open connection or file handle
     * @abstract open connection or file handle
     * @return bool
     */
    abstract public function open(): bool;

    /**
     * close connection or file handle
     * @abstract close connection or file handle
     * @return bool
     */
    abstract public function close(): bool;
}