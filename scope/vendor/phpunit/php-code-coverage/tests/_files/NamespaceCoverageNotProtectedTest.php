<?php

namespace _PhpScoper5bf3cbdac76b4;

use PHPUnit\Framework\TestCase;
class NamespaceCoverageNotProtectedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Foo\CoveredClass::<!protected>
     */
    public function testSomething()
    {
        $o = new \_PhpScoper5bf3cbdac76b4\Foo\CoveredClass();
        $o->publicMethod();
    }
}
