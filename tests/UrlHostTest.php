<?php

namespace TS\Web\UrlBuilder;


use PHPUnit\Framework\TestCase;


class UrlHostTest extends TestCase
{

	public function testEquals()
	{
		$this->assertTrue($this->host->equals(null));
		$this->assertTrue($this->host->equals(''));
		
		$this->host->set('domain.tld');
		$this->assertTrue($this->host->equals('domain.tld'));
		
		$other = clone $this->host;
		$this->assertTrue($this->host->equals($other));
		
		$other->set('example.com');
		$this->assertFalse($this->host->equals($other));
	
	}

	public function testEmpty()
	{
		$this->assertTrue($this->host->isEmpty());
	}

	/**
	 *
	 * @var UrlHost
	 */
	private $host;

	/**
	 * @before
	 */
	public function setup()
	{
		$this->host = new UrlHost();
	}

	/**
	 * @after
	 */
	public function teardown()
	{
		$this->host = null;
	}
}

