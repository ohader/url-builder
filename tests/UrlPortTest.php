<?php

namespace TS\Web\UrlBuilder;


use PHPUnit\Framework\TestCase;


class UrlPortTest extends TestCase
{

	public function testEquals()
	{
		$this->assertTrue($this->port->equals(null));
		
		$other = new UrlPort();
		$this->assertTrue($this->port->equals($other));
		
		$this->port->set(80);
		$this->assertTrue($this->port->equals(80));
		
		$other = clone $this->port;
		$this->assertTrue($this->port->equals($other));
		
		$other->set(443);
		$this->assertFalse($this->port->equals($other));
	}

	public function testEmpty()
	{
		$this->assertTrue($this->port->isEmpty());
	}

	/**
	 *
	 * @var UrlPort
	 */
	private $port;

	/**
	 * @before
	 */
	public function setup(): void
	{
		$this->port = new UrlPort();
	}

	/**
	 * @after
	 */
	public function teardown(): void
	{
		$this->port = null;
	}
}

