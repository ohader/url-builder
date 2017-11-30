<?php

namespace TS\Web\UrlBuilder;


use PHPUnit\Framework\TestCase;


class UrlQueryTest extends TestCase
{

	public function testEquals()
	{
		$this->assertTrue($this->query->equals(null));
		
		$other = new UrlQuery();
		$this->assertTrue($this->query->equals($other));
		
		$this->query->set('foo', 'bar');
		$this->assertFalse($this->query->equals($other));
		
		$other->set('foo', 'bar');
		$this->assertTrue($this->query->equals($other));
	
	}

	public function testEncoding()
	{
		$this->query->set('foo-ü-$-?-bar', 'foo-ü-$-?-bar');
		$this->assertEquals('foo-%C3%BC-%24-%3F-bar=foo-%C3%BC-%24-%3F-bar', $this->query->__toString());
		$this->query->clear();
		$this->query->set('foo-$-bar', 'foo-$-bar');
		$this->assertEquals('foo-%24-bar=foo-%24-bar', $this->query->__toString());
		$this->query->clear();
		$this->query->set('a', '&');
		$this->assertEquals('a=%26', $this->query->__toString());
	}

	public function testDecoding()
	{
		$this->query->parseComponent('family=Permanent+Marker');
		$this->assertEquals('Permanent Marker', $this->query->get('family'));
		
		$this->query->parseComponent('a=%26');
		$this->assertEquals('&', $this->query->get('a'));
		$this->query->parseComponent('a=');
		$this->assertEquals('', $this->query->get('a'));
	}

	public function testBlankParameterValues()
	{
		$this->query->parseComponent('a=&b');
		$this->assertCount(2, $this->query);
		$this->assertTrue($this->query->has('a'));
		$this->assertTrue($this->query->has('b'));
		$this->assertEquals('', $this->query->get('a'));
		$this->assertEquals('', $this->query->get('b'));
	}

	public function testGet()
	{
		$this->query->set('a', 'a');
		$this->query->set('b', 'b1', 'b2', 'b3');
		$this->assertEquals('a', $this->query->get('a'));
		$this->assertEquals('b1', $this->query->get('b'));
	}

	public function testGetDefault()
	{
		$this->assertEquals('c-default', $this->query->get('c', 'c-default'));
	}

	public function testGetArray()
	{
		$this->query->set('a', 'a');
		$this->query->set('b', 'b1', 'b2', 'b3');
		$this->assertEquals('a', $this->query->get('a'));
		$this->assertEquals([
			'b1',
			'b2',
			'b3'
		], $this->query->getArray('b'));
	}

	public function testSet()
	{
		$this->assertFalse($this->query->has('a'));
		$this->assertCount(0, $this->query);
		$this->query->set('a', 'a');
		$this->query->set('b', 'b1', 'b2', 'b3');
		$this->assertCount(2, $this->query);
		$this->assertTrue($this->query->has('a'));
		$this->assertEquals('a', $this->query->get('a'));
		$this->assertEquals([
			'a'
		], $this->query->getArray('a'));
		$this->assertTrue($this->query->has('b'));
		$this->assertEquals(3, $this->query->count('b'));
	}

	public function testSetArray()
	{
		$this->assertFalse($this->query->has('a'));
		$this->query->set('a', 'a');
		$this->assertTrue($this->query->has('a'));
		$this->assertEquals('a', $this->query->get('a'));
		$this->assertEquals([
			'a'
		], $this->query->getArray('a'));
	}

	public function testRemove()
	{
		$this->query->set('a', 'a');
		$this->query->set('b', 'b1', 'b2', 'b3');
		$this->assertCount(2, $this->query);
		$this->query->remove('a');
		$this->assertCount(1, $this->query);
		$this->assertFalse($this->query->has('a'));
	}

	public function testClear()
	{
		$this->query->set('a', 'a');
		$this->query->set('b', 'b1', 'b2', 'b3');
		$this->query->clear();
		$this->assertCount(0, $this->query);
	}

	public function testInvalid()
	{
		$tests = [
			'#',
			null,
			' ',
			'=x'
		];
		foreach ($tests as $t) {
			try {
				$this->query->parseComponent($t);
				$this->assertTrue(false, 'Expected parsing of "' . $t . '" to fail with InvalidUrlException');
			} catch (InvalidUrlException $ex) {
				$this->assertTrue(true);
			}
		}
	}

	public function testValid()
	{
		$tests = [
			'x',
			'x=',
			'x&y',
			'x=&y=',
			'x=a&y=b',
			'x&&b&'
		];
		foreach ($tests as $t) {
			$this->query->parseComponent($t);
			$this->assertTrue(true);
		}
	}

	public function testRoundTripEquals()
	{
		$tests = [
			'x=',
			'x=a',
			'x=a&y=b'
		];
		foreach ($tests as $t) {
			$this->query->parseComponent($t);
			$this->assertEquals($t, $this->query->__toString());
		}
	}

	public function testEmpty()
	{
		$empty = [
			'',
			'&'
		];
		foreach ($empty as $e) {
			$this->query->parseComponent($e);
			$this->assertTrue($this->query->isEmpty(), $e);
		}
	}

	/**
	 *
	 * @var UrlQuery
	 */
	private $query;

	/**
	 * @before
	 */
	public function setup()
	{
		$this->query = new UrlQuery();
	}

	/**
	 * @after
	 */
	public function teardown()
	{
		$this->query = null;
	}

}

