<?php

namespace TS\Web\UrlBuilder;


use PHPUnit\Framework\TestCase;


class UrlMakeAbsoluteTest extends TestCase
{

	public function testMakeAbsolutePath()
	{
		$base = 'http://domain.tld/catalog/products.html';
		$url = new Url('../assets/style.css');
		$url->makeAbsolutePath($base);
		$this->assertEquals('/assets/style.css', $url->__toString());
		
		$url = new Url('assets/style.css');
		$url->makeAbsolutePath('/');
		$this->assertEquals('/assets/style.css', $url->__toString());
		
	}

	public function testBaseEmpty()
	{
		$url = new Url('foo.html');
		$empty = new Url();
		$this->expectException(InvalidUrlException::class);
		$url->makeAbsolute($empty);
	}

	public function testBaseRelative()
	{
		$url = new Url('foo.html');
		$relative = new Url('bar.html');
		$this->expectException(InvalidUrlException::class);
		$url->makeAbsolute($relative);
	}

	/**
	 * @dataProvider absoluteCasesProvider
	 */
	public function testMakeAbsolute($relativeUrl, $baseUrl, $expected)
	{
		$relative = new Url($relativeUrl);
		$abs = $relative->makeAbsolute($baseUrl);
		$this->assertEquals($expected, $abs->__toString());
	}

	public function absoluteCasesProvider()
	{
		return [
			[
				'foo.html',
				'http://domain.tld',
				'http://domain.tld/foo.html'
			],
			[
				'foo.html',
				'http://domain.tld/',
				'http://domain.tld/foo.html'
			],
			[
				'foo/index.html',
				'http://domain.tld/',
				'http://domain.tld/foo/index.html'
			],
			[
				'foo/index.html',
				'http://domain.tld/bar/',
				'http://domain.tld/bar/foo/index.html'
			],
			[
				'../assets/style.css',
				'http://domain.tld/catalog/',
				'http://domain.tld/assets/style.css'
			],
			[
				'../assets/style.css',
				'http://domain.tld/catalog/products.html',
				'http://domain.tld/assets/style.css'
			],
			[
				'http://foo.com/index.html',
				'http://domain.tld/',
				'http://foo.com/index.html'
			]
		];
	}

}

