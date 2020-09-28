<?php

namespace TS\Web\UrlBuilder;


use PHPUnit\Framework\TestCase;


class UrlFragmentTest extends TestCase
{

	public function testEquals()
	{
		$this->assertTrue($this->fragment->equals(null));
		$this->assertTrue($this->fragment->equals(''));
		
		$this->fragment->set('foo');
		$this->assertTrue($this->fragment->equals('foo'));
		
		$other = clone $this->fragment;
		$this->assertTrue($this->fragment->equals($other));
		
		$other->set('bar');
		$this->assertFalse($this->fragment->equals($other));
	
	}

	public function testEmpty()
	{
		$this->assertTrue($this->fragment->isEmpty());
	}

	/**
	 *
	 * @var UrlFragment
	 */
	private $fragment;

	/**
	 * @before
	 */
	public function setup(): void
	{
		$this->fragment = new UrlFragment();
	}

	/**
	 * @after
	 */
	public function teardown(): void
	{
		$this->fragment = null;
	}
}

