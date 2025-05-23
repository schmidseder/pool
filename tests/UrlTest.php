<?php
/*
 * This file is part of POOL (PHP Object-Oriented Library)
 *
 * (c) Alexander Manhart <alexander@manhart-it.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use pool\classes\Core\Url;


class UrlTest extends TestCase
{
    public function setUp(): void
    {
        $_SERVER['SERVER_NAME'] = 'dev.local';
        $_SERVER['SERVER_PORT'] = '80';
        if (!defined('DIR_DOCUMENT_ROOT'))
            require_once __DIR__.'/../configs/config.inc.php';
    }

    public function testUrl()
    {
        $url = Url::fromString('http://www.example.com:80/foo/bar?test=1#fragment');
        $this->assertEquals('http', $url->getScheme());
        $this->assertEquals('www.example.com', $url->getHost());
        $this->assertEquals(80, $url->getPort());
        $this->assertEquals('/foo/bar', $url->getPath());
        $this->assertEquals('test=1', http_build_query($url->getQuery()));
        $this->assertEquals('fragment', $url->getFragment());
    }
}