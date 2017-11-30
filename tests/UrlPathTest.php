<?php

namespace TS\Web\UrlBuilder;


use PHPUnit\Framework\TestCase;


class UrlPathTest extends TestCase
{

	
	public function testEquals()
	{
		$this->assertTrue($this->path->equals(null));
		
		$other = new UrlPath();
		$this->assertTrue($this->path->equals($other));
		
		$this->path->set('/foo');
		$this->assertTrue($this->path->equals('/foo'));
		
		$other = clone $this->path;
		$this->assertTrue($this->path->equals($other));
		
		$other->set('/bar');
		$this->assertFalse($this->path->equals($other));
	}
	
	
	public function testSet()
	{
		$this->path->set('/foo-ü-$-?-bar/x');
		$this->assertEquals('/foo-%C3%BC-%24-%3F-bar/x', $this->path->__toString());
	}

	public function testGet()
	{
		$this->path->parseComponent('/foo-%C3%BC-%24-%3F-bar/x');
		$this->assertEquals('/foo-ü-$-?-bar/x', $this->path->get());
	}

	public function testInvalid()
	{
		$tests = [
			null,
			123,
			' ',
			'/ foo'
		];
		foreach ($tests as $t) {
			try {
				$this->path->parseComponent($t);
				$this->assertTrue(false, 'Expected parsing of "' . $t . '" to fail with InvalidUrlException');
			} catch (InvalidUrlException $ex) {
				$this->assertTrue(true);
			}
		}
	}

	public function testFilename()
	{
		$tests = [
			'/' => '',
			'' => '',
			'assets/styles/foo.css' => 'foo.css',
			'../styles/foo.css' => 'foo.css'
		];
		foreach ($tests as $t => $expected) {
			$this->path->parseComponent($t);
			$this->assertEquals($expected, $this->path->filename());
		}
	}

	public function testExtension()
	{
		$tests = [
			'/' => '',
			'' => '',
			'assets/styles/foo.css' => 'css',
			'../styles/foo.css' => 'css'
		];
		foreach ($tests as $t => $expected) {
			$this->path->parseComponent($t);
			$this->assertEquals($expected, $this->path->extension());
		}
	}
	
	public function testDirname()
	{
		$tests = [
			'/' => '/',
			'' => '',
			'assets/styles/foo.css' => 'assets/styles/',
			'../styles/foo.css' => '../styles/'
		];
		foreach ($tests as $t => $expected) {
			$this->path->parseComponent($t);
			$this->assertEquals($expected, $this->path->dirname());
		}
	}
	
	public function testDirnameWithHost()
	{
		$tests = [
			'/' => '/',
			'' => '/'
		];
		$u = new Url();
		$u->host->set('domain.tld');
		$p = new UrlPath($u);
		foreach ($tests as $t => $expected) {
			$p->parseComponent($t);
			$this->assertEquals($expected, $p->dirname());
			$this->assertTrue($p->isAbsolute());
		}
	}

	public function testIsAbsolute()
	{
		$tests = [
			'/' => true,
			'' => false,
			'.' => false,
			'foo/bar' => false
		];
		foreach ($tests as $t => $expected) {
			$this->path->parseComponent($t);
			$this->assertEquals($expected, $this->path->isAbsolute());
		}
	}

	public function testIsAbsoluteWithHost()
	{
		$tests = [
			'/' => true,
			'' => true,
			'foo/bar' => false
		];
		$u = new Url();
		$u->host->set('domain.tld');
		$p = new UrlPath($u);
		foreach ($tests as $t => $expected) {
			$p->parseComponent($t);
			$this->assertEquals($expected, $p->isAbsolute());
		}
	}

	public function testToString()
	{
		$tests = [
			'/foo-%C3%BC-%24-%3F-bar/x',
			'',
			'/',
			'./',
			'../../x'
		];
		foreach ($tests as $t) {
			$this->path->parseComponent($t);
			$this->assertEquals($t, $this->path->__toString());
		}
	}

	public function testEmpty()
	{
		$this->assertTrue($this->path->isEmpty());
		
		$this->path->parseComponent('');
		$this->assertTrue($this->path->isEmpty());
		
		$this->path->parseComponent('/123');
		$this->assertFalse($this->path->isEmpty());
	}
	

	public function testInitial()
	{
		$this->assertEquals('', $this->path->get());
	}
	
	
	
	/**
	 *
	 * @var UrlPath
	 */
	private $path;
	
	/**
	 * @before
	 */
	public function setup()
	{
		$this->path = new UrlPath();
	}
	
	/**
	 * @after
	 */
	public function teardown()
	{
		$this->path = null;
	}
	
}

