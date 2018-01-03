<?php

namespace TS\Web\UrlBuilder;


use PHPUnit\Framework\TestCase;


class UrlTest extends TestCase
{

	const FULL_URL = 'http://peter%40example.com:pass%3A%2F123@domain.tld:8080/foo-%C3%BC-%24-%3F-bar/x?query=&x-%26-y=x-%26-y#fragment-x-%26-y';

	public function testProtocolRelativeUrl()
	{
		$u = new Url('//cdn.com/angular.js');
		$this->assertTrue($u->isAbsolute());
		$this->assertTrue($u->path->isAbsolute());
		$this->assertEquals('cdn.com', $u->host->get());
		$u->makeAbsolute('https://domain.tld/index.html');
		$this->assertEquals('https://cdn.com/angular.js', $u);
	
	}

	public function testRoundtripEquals()
	{
		$tests = [
			'http://domain.tld/foo?x=',
			'http://peter%40example.com:pass%3A%2F123@domain.tld:8080/foo-%C3%BC-%24-%3F-bar/x?query=&x-%24-y=x-%24-y#fragment-x-%26-y'
		];
		foreach ($tests as $t) {
			$u = new Url($t);
			$this->assertEquals($t, $u->__toString());
		}
	}

	public function testRoundtripDiffers()
	{
		$tests = [
			'http://domain.tld/foo?x'
		];
		foreach ($tests as $t) {
			$u = new Url($t);
			$this->assertNotEquals($t, $u->__toString());
		}
	}

	public function testParseInvalid()
	{
		$tests = [
			'',
			null,
			'http:///',
			'https:///',
			'http://',
			'https://',
			' ',
			'http://domain.tld/ foo'
		];
		$u = new Url();
		foreach ($tests as $t) {
			try {
				$u->parseUrl($t);
				$this->assertTrue(false, 'Expected parsing of "' . $t . '" to fail with InvalidUrlException');
			} catch (InvalidUrlException $ex) {
				$this->assertTrue(true);
			}
		}
	}

	public function testParseValid()
	{
		$tests = [
			'data-uri:///xyz', 
			'//domain.tld/xx',
			'file:///',
			'custom:///',
			'custom:///xyz',
			'http://domain.tld/foo??x',
			'http://domain.tld/foo?x'
		];
		$u = new Url();
		foreach ($tests as $t) {
			$u->parseUrl($t);
			$this->assertTrue(true);
		}
	}

	public function testIsValid()
	{
		$u = new Url();
		$this->assertFalse($u->isValid());
		
		$u = new Url();
		$u->credentials->username = 'peter';
		$this->assertFalse($u->isValid());
		
		$u = new Url();
		$u->scheme->set('https');
		$this->assertFalse($u->isValid());
		
		$u = new Url();
		$u->port->set(80);
		$this->assertFalse($u->isValid());
	}

	public function testIsEmpty()
	{
		$u = new Url();
		$this->assertTrue($u->isEmpty());
	}

	public function testIsAbsolute()
	{
		$tests = [
			'//domain.tld/xx' => true,
			'/index.html' => false,
			'index.html' => false,
			'http://domain.tld/foo??x' => true,
			'http://domain.tld/foo?x' => true
		];
		$u = new Url();
		foreach ($tests as $test => $expected) {
			$u->parseUrl($test);
			$this->assertEquals($expected, $u->isAbsolute());
			$this->assertEquals(! $expected, $u->isRelative());
		}
	}

	public function testPathFromRelativeUrl()
	{
		$u = new Url('foo.css');
		$this->assertFalse($u->path->isAbsolute());
		$this->assertEquals('foo.css', $u->path->get());
	}

	public function testReplace()
	{
		$this->url->replace('//domain.tld/foo');
		$this->assertTrue($this->url->scheme->isEmpty());
		$this->assertTrue($this->url->credentials->isEmpty());
		$this->assertEquals('domain.tld', $this->url->host->get());
		$this->assertTrue($this->url->port->isEmpty());
		$this->assertEquals('/foo', $this->url->path->get());
		$this->assertTrue($this->url->query->isEmpty());
		$this->assertTrue($this->url->fragment->isEmpty());
	}

	public function testReplacePath()
	{
		$this->url->replacePath('https://example.com:443/foo?bar=x');
		$this->assertEquals('http', $this->url->scheme->get());
		$this->assertEquals('domain.tld', $this->url->host->get());
		$this->assertEquals(8080, $this->url->port->get());
		$this->assertEquals('peter@example.com', $this->url->credentials->username);
		$this->assertEquals('pass:/123', $this->url->credentials->password);
		$this->assertEquals('/foo', $this->url->path->get());
		$this->assertEquals('x', $this->url->query->get('bar'));
		$this->assertTrue($this->url->fragment->isEmpty());
	}

	public function testReplaceHost()
	{
		$this->url->replaceHost('https://example.com:443/foo');
		$this->assertEquals('https', $this->url->scheme->get());
		$this->assertEquals('example.com', $this->url->host->get());
		$this->assertEquals(443, $this->url->port->get());
		$this->assertEquals('/foo-ü-$-?-bar/x', $this->url->path->get());
		$this->assertTrue($this->url->credentials->isEmpty());
		
		$this->assertFalse($this->url->path->isEmpty());
		$this->assertFalse($this->url->query->isEmpty());
		$this->assertFalse($this->url->fragment->isEmpty());
	}

	public function testMerge()
	{
		$this->url->merge('//domain.tld/foo?bar=x');
		$this->assertEquals('http', $this->url->scheme->get());
		$this->assertEquals('domain.tld', $this->url->host->get());
		$this->assertEquals(8080, $this->url->port->get());
		$this->assertEquals('peter@example.com', $this->url->credentials->username);
		$this->assertEquals('pass:/123', $this->url->credentials->password);
		$this->assertEquals('/foo', $this->url->path->get());
		$this->assertEquals('x', $this->url->query->get('bar'));
		$this->assertFalse($this->url->fragment->isEmpty());
	}

	public function testClearAll()
	{
		$this->assertFalse($this->url->isEmpty());
		$this->url->clear();
		$this->assertTrue($this->url->isEmpty());
	}

	public function testClearScheme()
	{
		$this->assertFalse($this->url->scheme->isEmpty());
		$this->url->clear(Url::SCHEME);
		$this->assertTrue($this->url->scheme->isEmpty());
	}

	public function testClearHost()
	{
		$this->assertFalse($this->url->host->isEmpty());
		$this->url->clear(Url::HOST);
		$this->assertTrue($this->url->host->isEmpty());
	}

	public function testClearPort()
	{
		$this->assertFalse($this->url->port->isEmpty());
		$this->url->clear(Url::PORT);
		$this->assertTrue($this->url->port->isEmpty());
	}

	public function testClearPath()
	{
		$this->assertFalse($this->url->path->isEmpty());
		$this->url->clear(Url::PATH);
		$this->assertTrue($this->url->path->isEmpty());
	}

	public function testClearQuery()
	{
		$this->assertFalse($this->url->query->isEmpty());
		$this->url->clear(Url::QUERY);
		$this->assertTrue($this->url->query->isEmpty());
	}

	public function testClearFragment()
	{
		$this->assertFalse($this->url->fragment->isEmpty());
		$this->url->clear(Url::FRAGMENT);
		$this->assertTrue($this->url->fragment->isEmpty());
	}

	public function test_php_parse_url()
	{
		$parts = parse_url(self::FULL_URL);
		$this->assertEquals('http', $parts['scheme']);
		$this->assertEquals('domain.tld', $parts['host']);
		$this->assertEquals(8080, $parts['port']);
		$this->assertEquals('peter%40example.com', $parts['user']);
		$this->assertEquals('pass%3A%2F123', $parts['pass']);
		$this->assertEquals('/foo-%C3%BC-%24-%3F-bar/x', $parts['path']);
		$this->assertEquals('query=&x-%26-y=x-%26-y', $parts['query']);
		$this->assertEquals('fragment-x-%26-y', $parts['fragment']);
		
		$this->assertCount(1, parse_url(null));
		$this->assertEquals("", parse_url(null)['path']);
		
		$this->assertCount(1, parse_url(''));
		$this->assertEquals("", parse_url('')['path']);
		
		$this->assertCount(1, parse_url(' '));
		$this->assertEquals(" ", parse_url(' ')['path']);
		
		$this->assertEquals("/ foo", parse_url('http://domain.tld/ foo')['path']);
		
		$this->assertNull(parse_url('http://domain.tld', PHP_URL_PATH));
		$this->assertEquals("/", parse_url('http://domain.tld/')['path']);
		
		$this->assertFalse(parse_url('http:///'));
		
		$this->assertEquals("?x", parse_url('http://domain.tld/foo??x')['query']);
	}

	public function test_php_parse_url2()
	{
		$path = parse_url('http://domain.tld', PHP_URL_PATH);
		$this->assertNull($path);
		$path = parse_url('http://domain.tld/', PHP_URL_PATH);
		$this->assertEquals('/', $path);
	}

	/**
	 * @dataProvider equalsCasesProvider
	 */
	public function testEquals($a, $b, $flags, $expected)
	{
		$a = new Url($a);
		if (is_null($flags)) {
			$equal = $a->equals($b);
		} else {
			$equal = $a->equals($b, $flags);
		}
		$this->assertEquals($expected, $equal);
	}
	
	public function equalsCasesProvider() {
		return [
			[ 'foo.html', 'http://domain.tld', null, false ],
			[ 'http://domain.tld', 'http://domain.tld', null, true ],
			[ 'http://domain.tld', 'http://domain.tld', Url::HOST, true ],
			[ self::FULL_URL, 'http://domain.tld', Url::HOST, true ],
			[ self::FULL_URL, 'http://domain.tld', Url::HOST|Url::SCHEME, true ],
			[ self::FULL_URL, 'http://domain.tld', null, false ],
			[ self::FULL_URL, self::FULL_URL, null, true ],
		];
	}

	public function testClone()
	{
		$other = clone $this->url;
		$this->assertTrue($this->url->equals($other));
		
		$other = clone $this->url;
		$other->path->set('/new-path');
		$this->assertFalse($this->url->equals($other));
		
		$other = clone $this->url;
		$other->host->clear();
		$this->assertFalse($this->url->equals($other));
		
		$other = clone $this->url;
		$other->scheme->clear();
		$this->assertFalse($this->url->equals($other));
		
		$other = clone $this->url;
		$other->port->clear();
		$this->assertFalse($this->url->equals($other));
		
		$other = clone $this->url;
		$other->credentials->clear();
		$this->assertFalse($this->url->equals($other));
		
		$other = clone $this->url;
		$other->path->clear();
		$this->assertFalse($this->url->equals($other));
		
		$other = clone $this->url;
		$other->query->clear();
		$this->assertFalse($this->url->equals($other));
		
		$other = clone $this->url;
		$other->fragment->clear();
		$this->assertFalse($this->url->equals($other));
	}
	
	
	public function testFileUrl()
	{
		$this->url
			->replacePath('foo.jpeg')
			->clearHost()
			->scheme->set('file');
		$this->assertEquals('file:///foo.jpeg', $this->url->__toString());
	}
	
	

	/**
	 *
	 * @var Url
	 */
	private $url;

	/**
	 * @before
	 */
	public function setup()
	{
		$this->url = new Url(self::FULL_URL);
	}

	/**
	 * @after
	 */
	public function teardown()
	{
		$this->url = null;
	}

}

