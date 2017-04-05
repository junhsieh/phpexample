<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers Car
 */
final class CarTest extends TestCase
{
	public function testTrue()
	{
		$car = new \My\Hello\Car();
		$this->assertTrue(true);
	}
}
