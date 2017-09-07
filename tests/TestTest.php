<?php
/**
 *  test
 *
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

declare(strict_types=1);

namespace Kerisy\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @covers Email
 */
final class EmailTest extends TestCase
{
    public function testHelloworld()
    {
        $this->assertEquals(
            "hello world",
            (new \Kerisy\Test())->helloworld()
        );
    }
}