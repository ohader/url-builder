<?php

namespace TS\Web\UrlBuilder;


use PHPUnit\Framework\TestCase;


class UrlSchemeTest extends TestCase
{

	public function testEquals()
	{
		$this->assertTrue($this->scheme->equals(null));
		$this->assertTrue($this->scheme->equals(''));
		
		$this->scheme->set('https');
		$this->assertTrue($this->scheme->equals('https'));
		
		$other = clone $this->scheme;
		$this->assertTrue($this->scheme->equals($other));
		
		$other->set('http');
		$this->assertFalse($this->scheme->equals($other));
	
	}

	public function testEmpty()
	{
		$this->assertTrue($this->scheme->isEmpty());
	}

	/**
	 *
	 * @var UrlScheme
	 */
	private $scheme;

	/**
	 * @before
	 */
	public function setup(): void
	{
		$this->scheme = new UrlScheme();
	}

	/**
	 * @after
	 */
	public function teardown(): void
	{
		$this->scheme = null;
	}
}

