<?php

namespace TS\Web\UrlBuilder;


use PHPUnit\Framework\TestCase;


class UrlCredentialsTest extends TestCase
{
	
	const CREDENTIALS_COMPONENT = 'peter%40example.com:pass%3A%2F123';
	
	public function testParse()
	{
		$this->credentials->parseComponent(self::CREDENTIALS_COMPONENT);
		$this->assertEquals('peter@example.com', $this->credentials->username);
		$this->assertEquals('pass:/123', $this->credentials->password);
	}
	
	public function testToString()
	{
		$this->credentials->parseComponent(self::CREDENTIALS_COMPONENT);
		$this->assertEquals(self::CREDENTIALS_COMPONENT, $this->credentials->__toString());
	}
	
	public function testEmpty()
	{
		$this->assertTrue($this->credentials->isEmpty());
		$this->credentials->parseComponent('');
		$this->assertTrue($this->credentials->isEmpty());
	}
	
	public function testEquals() {
		$this->assertTrue($this->credentials->equals(null));
		
		$other = new UrlCredentials();
		$this->assertTrue($this->credentials->equals($other));
		
		$other->username = 'peter';
		$other->password = 'pass';
		$this->assertFalse($this->credentials->equals($other));
	}
	
	/**
	 *
	 * @var UrlCredentials
	 */
	private $credentials;
	
	/**
	 * @before
	 */
	public function setup()
	{
		$this->credentials = new UrlCredentials();
	}
	
	/**
	 * @after
	 */
	public function teardown()
	{
		$this->credentials = null;
	}
}

