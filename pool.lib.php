<?php
/*
 * This file is part of POOL (PHP Object-Oriented Library)
 *
 * (c) Alexander Manhart <alexander@manhart-it.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace pool {

    use pool\classes\Autoloader;

    if(!defined('DIR_POOL_ROOT')) {
        define('DIR_POOL_ROOT', __DIR__);
    }

    const PWD_TILL_INCLUDES = 'includes'; // todo remove after refactoring to PSR-4
    const PWD_TILL_CLASSES = 'classes'; // todo remove after refactoring to PSR-4
    const PWD_TILL_GUIS = 'guis'; // todo remove after refactoring to PSR-4
    const PWD_TILL_SCHEMES = 'schemes';
    const PWD_TILL_SKINS = 'skins';
    const PWD_TILL_JS = 'js';
    const PWD_TILL_SUBCODES = 'subcodes';

    require_once(__DIR__ . '/' . PWD_TILL_CLASSES . '/Autoloader.php');
    Autoloader::getLoader()->register();

    // @todo replace against Utils classes after refactoring to PSR-4
    require __DIR__ . '/' . PWD_TILL_INCLUDES . '/includes.lib.php';
    // @todo load from autoloader
    require __DIR__ . '/' . PWD_TILL_CLASSES . '/classes.lib.php';
}