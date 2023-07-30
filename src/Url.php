<?php

namespace TS\Web\UrlBuilder;


use TS\Filesystem\Path;


class Url
{

	const SCHEME = 2;

	const HOST = 4;

	const PORT = 8;

	const CREDENTIALS = 16;

	const PATH = 32;

	const QUERY = 64;

	const FRAGMENT = 128;

	/**
	 *
	 * @var UrlScheme
	 */
	public $scheme;

	/**
	 *
	 * @var UrlHost
	 */
	public $host;

	/**
	 *
	 * @var UrlPort
	 */
	public $port;

	/**
	 *
	 * @var UrlCredentials
	 */
	public $credentials;

	/**
	 *
	 * @var UrlPath
	 */
	public $path;

	/**
	 *
	 * @var UrlQuery
	 */
	public $query;

	/**
	 *
	 * @var UrlFragment
	 */
	public $fragment;

    /**
     * @var InvalidUrlException
     */
    public $parsingException = null;

	public function __construct($url = null)
	{
		$this->scheme = new UrlScheme();
		$this->host = new UrlHost();
		$this->port = new UrlPort();
		$this->credentials = new UrlCredentials();
		$this->path = new UrlPath($this);
		$this->query = new UrlQuery();
		$this->fragment = new UrlFragment();
		if (! is_null($url)) {
            try {
                $this->parseUrl($url);
            } catch (InvalidUrlException $e) {
                $this->parsingException = $e;
            }
		}
	}

	/**
	 * Parses an URL.
	 *
	 * @param string $str
	 * @throws InvalidUrlException
	 * @return self
	 */
	public function parseUrl($str)
	{
		if (! is_string($str)) {
			throw new InvalidUrlException('Unexpected type.');
		}
		if ($str === '') {
			throw new InvalidUrlException('Empty URL.');
		}
		if (trim($str, ' ') === '') {
			throw new InvalidUrlException('Empty URL.');
		}
		$components = $this->parseUrlComponents($str);
		$components = array_replace([
			'scheme' => '',
			'host' => '',
			'port' => '',
			'user' => '',
			'pass' => '',
			'path' => '',
			'query' => '',
			'fragment' => ''
		], $components);
		$this->scheme->parseComponent($components['scheme']);
		$this->host->parseComponent($components['host']);
		$this->port->parseComponent(strval($components['port']));
		if (empty($components['user']) && empty($components['pass'])) {
			$this->credentials->username = null;
			$this->credentials->password = null;
		} else {
			$this->credentials->parseComponent($components['user'] . ':' . $components['pass']);
		}
		$this->path->parseComponent($components['path']);
		$this->query->parseComponent($components['query']);
		$this->fragment->parseComponent($components['fragment']);
		return $this;
	}

	private function parseUrlComponents($str)
	{
		$components = parse_url($str);
		if ($components === false) {
			// parse_url() accepts host-less urls like file:///, but only for the file scheme.
			if (preg_match('/^(.*):\/\//', $str, $ma) ) {
				$scheme = isset($ma[1]) ? $ma[1] : null;
				if ( in_array($scheme, ['http', 'https', 'ftp', 'sftp', 'ssh']) ) {
					throw new InvalidUrlException('Unable to parse URL.');
				}
				$w = substr($str, strlen($scheme) + 3);
				$components = parse_url('file://' . $w);
				$components['scheme'] = $scheme;
			} else {
				throw new InvalidUrlException('Unable to parse URL.');
			}
		}
		return $components;
	}

	/**
	 * Returns true if the URL contains a host, false otherwise.
	 *
	 * @return boolean
	 */
	public function isAbsolute()
	{
		if ($this->isEmpty()) {
			return false;
		}
		return ! $this->host->isEmpty() || ! $this->scheme->isEmpty();
	}

	/**
	 * Returns true if the URL is not empty and does not contain a host.
	 *
	 * @return boolean
	 */
	public function isRelative()
	{
		if ($this->isEmpty()) {
			return false;
		}
		return $this->host->isEmpty();
	}

	/**
	 * Makes the current relative URL absolute to the given base URL.
	 *
	 * Example:
	 * $base = 'http://domain.tld/catalog/products.html';
	 * $url = new Url('../assets/style.css');
	 * $url->makeAbsolute($base);
	 * print $url; // => http://domain.tld/assets/style.css
	 *
	 * If the current URL is already absolute, it does not change.
	 *
	 * @param Url|string $base
	 * @throws InvalidUrlException if the current URL is empty, or the base URL is not absolute.
	 * @return self
	 */
	public function makeAbsolute($base)
	{
		if (is_string($base)) {
			$baseUrl = new Url($base);
		} else if ($base instanceof Url) {
			$baseUrl = $base;
		} else {
			throw new InvalidUrlException();
		}
		if ($baseUrl->isEmpty()) {
			throw new InvalidUrlException();
		}
		if (! $baseUrl->isAbsolute()) {
			throw new InvalidUrlException(sprintf('Cannot make absolute URL from "%s" because the given base URL "%s" is not absolute.', $this, $baseUrl));
		}
		if ($this->isAbsolute()) {
			if ($this->scheme->isEmpty()) {
				$this->scheme->set($baseUrl->scheme);
			}
			return $this;
		}
		if (! $this->path->isAbsolute()) {
			$relPath = Path::info($this->path->get());
			$baseDir = $baseUrl->path->isEmpty() ? '/' : Path::info($baseUrl->path->get())->dir();
			$absPath = $relPath->abs($baseDir)->normalize();
			$this->path->set($absPath);
		}
		$this->scheme->set($baseUrl->scheme);
		$this->credentials->username = $baseUrl->credentials->username;
		$this->credentials->password = $baseUrl->credentials->password;
		$this->host->set($baseUrl->host);
		$this->port->set($baseUrl->port);
		return $this;
	}

	/**
	 * Makes the path of the current relative URL absolute to the given base URL.
	 *
	 * Example:
	 * $base = 'http://domain.tld/catalog/products.html';
	 * $url = new Url('../assets/style.css');
	 * $url->makeAbsolutePath($base);
	 * print $url; // => /assets/style.css
	 *
	 * If the current URL is already absolute, it does not change.
	 * If the path of the current URL is already absolute, it does not change.
	 *
	 * @param Url|Path|string $base
	 * @throws InvalidUrlException if the current URL is empty, or the base URL is not absolute.
	 * @return self
	 */
	public function makeAbsolutePath($base)
	{
		if (is_string($base)) {
			$baseUrl = new Url($base);
		} else if ($base instanceof Url) {
			$baseUrl = $base;
		} else if ($newUrl instanceof Path) {
			$baseUrl = new Url();
			$baseUrl->path->set($newUrl);
		} else {
			throw new InvalidUrlException();
		}
		if ($baseUrl->isEmpty()) {
			throw new InvalidUrlException();
		}
		if (! $baseUrl->path->isAbsolute()) {
			throw new InvalidUrlException();
		}
		if ($this->isAbsolute()) {
			return $this;
		}
		if (! $this->path->isAbsolute()) {
			$relPath = Path::info($this->path->get());
			$baseDir = Path::info($baseUrl->path->get())->dir();
			$absPath = $relPath->abs($baseDir)->normalize();
			$this->path->set($absPath);
		}
		return $this;
	}

	/**
	 * Returns true if all components are empty.
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
        // either there was an error, of all parts are empty
		return $this->parsingException !== null
            || $this->scheme->isEmpty()
                && $this->host->isEmpty()
                && $this->path->isEmpty()
                && $this->port->isEmpty()
                && $this->credentials->isEmpty()
                && $this->query->isEmpty()
                && $this->fragment->isEmpty();
	}

	/**
	 * Clear all or only specific components.
	 *
	 * @param int $opt
	 * @return self
	 */
	public function clear($opt = self::SCHEME | self::HOST | self::PORT | self::CREDENTIALS | self::PATH | self::QUERY | self::FRAGMENT)
	{
		$scheme = ($opt & self::SCHEME) === self::SCHEME;
		$host = ($opt & self::HOST) === self::HOST;
		$port = ($opt & self::PORT) === self::PORT;
		$creds = ($opt & self::CREDENTIALS) === self::CREDENTIALS;
		$path = ($opt & self::PATH) === self::PATH;
		$query = ($opt & self::QUERY) === self::QUERY;
		$fragment = ($opt & self::FRAGMENT) === self::FRAGMENT;
		if ($scheme) {
			$this->scheme->clear();
		}
		if ($host) {
			$this->host->clear();
		}
		if ($port) {
			$this->port->clear();
		}
		if ($creds) {
			$this->credentials->clear();
		}
		if ($path) {
			$this->path->clear();
		}
		if ($query) {
			$this->query->clear();
		}
		if ($fragment) {
			$this->fragment->clear();
		}
		return $this;
	}

	/**
	 * Clear components at the right, starting with the path.
	 *
	 * @param int $opt
	 * @return self
	 */
	public function clearPath($opt = self::PATH | self::QUERY | self::FRAGMENT)
	{
		$path = ($opt & self::PATH) === self::PATH;
		$query = ($opt & self::QUERY) === self::QUERY;
		$fragment = ($opt & self::FRAGMENT) === self::FRAGMENT;
		if ($path) {
			$this->path->clear();
		}
		if ($query) {
			$this->query->clear();
		}
		if ($fragment) {
			$this->fragment->clear();
		}
		return $this;
	}

	/**
	 * Clear components at the left, up to the path.
	 *
	 * @param int $opt
	 * @return self
	 */
	public function clearHost($opt = self::SCHEME | self::HOST | self::PORT | self::CREDENTIALS)
	{
		$scheme = ($opt & self::SCHEME) === self::SCHEME;
		$host = ($opt & self::HOST) === self::HOST;
		$port = ($opt & self::PORT) === self::PORT;
		$creds = ($opt & self::CREDENTIALS) === self::CREDENTIALS;
		if ($scheme) {
			$this->scheme->clear();
		}
		if ($host) {
			$this->host->clear();
		}
		if ($port) {
			$this->port->clear();
		}
		if ($creds) {
			$this->credentials->clear();
		}
		return $this;
	}

	/**
	 * Replaces all or specific components with the components of the given URL.
	 *
	 * @param string|Url $newUrl
	 * @param int $opt
	 * @throws InvalidUrlException
	 * @return self
	 */
	public function replace($newUrl, $opt = self::SCHEME | self::HOST | self::PORT | self::CREDENTIALS | self::PATH | self::QUERY | self::FRAGMENT)
	{
		if ($newUrl instanceof Url) {
			$url = $newUrl;
		} else if (is_string($newUrl)) {
			$url = new Url($newUrl);
		} else {
			throw new InvalidUrlException();
		}
		$scheme = ($opt & self::SCHEME) === self::SCHEME;
		$host = ($opt & self::HOST) === self::HOST;
		$port = ($opt & self::PORT) === self::PORT;
		$creds = ($opt & self::CREDENTIALS) === self::CREDENTIALS;
		$path = ($opt & self::PATH) === self::PATH;
		$query = ($opt & self::QUERY) === self::QUERY;
		$fragment = ($opt & self::FRAGMENT) === self::FRAGMENT;
		if ($scheme) {
			$this->scheme->set($url->scheme->get());
		}
		if ($host) {
			$this->host->set($url->host->get());
		}
		if ($port) {
			$this->port->set($url->port->get());
		}
		if ($creds) {
			$this->credentials->username = $url->credentials->username;
			$this->credentials->password = $url->credentials->password;
		}
		if ($path) {
			$this->path->set($url->path->get());
		}
		if ($query) {
			$this->query->parseComponent($url->query->__toString());
		}
		if ($fragment) {
			$this->fragment->set($url->fragment->get());
		}
		return $this;
	}

	/**
	 * Replaces the components left of the path with the components of the given URL.
	 *
	 * @param string|Url $newUrl
	 * @param int $opt
	 * @throws InvalidUrlException
	 * @return self
	 */
	public function replaceHost($newUrl, $opt = self::SCHEME | self::HOST | self::PORT | self::CREDENTIALS)
	{
		if ($newUrl instanceof Url) {
			$url = $newUrl;
		} else if (is_string($newUrl)) {
			$url = new Url($newUrl);
		} else {
			throw new InvalidUrlException();
		}
		$scheme = ($opt & self::SCHEME) === self::SCHEME;
		$host = ($opt & self::HOST) === self::HOST;
		$port = ($opt & self::PORT) === self::PORT;
		$creds = ($opt & self::CREDENTIALS) === self::CREDENTIALS;
		if ($scheme) {
			$this->scheme->set($url->scheme->get());
		}
		if ($host) {
			$this->host->set($url->host->get());
		}
		if ($port) {
			$this->port->set($url->port->get());
		}
		if ($creds) {
			$this->credentials->username = $url->credentials->username;
			$this->credentials->password = $url->credentials->password;
		}
		return $this;
	}

	/**
	 * Replaces the components on the right side, starting with the path, with the components of the given URL.
	 *
	 * @param string|Url|Path $newUrl
	 * @param int $opt
	 * @throws InvalidUrlException
	 * @return self
	 */
	public function replacePath($newUrl, $opt = self::PATH | self::QUERY | self::FRAGMENT)
	{
		if ($newUrl instanceof Url) {
			$url = $newUrl;
		} else if ($newUrl instanceof Path) {
			$url = new Url();
			$url->path->set($newUrl);
		} else if (is_string($newUrl)) {
			$url = new Url($newUrl);
		} else {
			throw new InvalidUrlException();
		}
		$path = ($opt & self::PATH) === self::PATH;
		$query = ($opt & self::QUERY) === self::QUERY;
		$fragment = ($opt & self::FRAGMENT) === self::FRAGMENT;
		if ($path) {
			$this->path->set($url->path->get());
		}
		if ($query) {
			$this->query->parseComponent($url->query->__toString());
		}
		if ($fragment) {
			$this->fragment->set($url->fragment->get());
		}
		return $this;
	}

	/**
	 * Replaces all or specific components with the components of the given URL,
	 * but only for the components that are not empty in the given URL.
	 *
	 * @param string|Url $newUrl
	 * @param int $opt
	 * @throws InvalidUrlException
	 * @return self
	 */
	public function merge($newUrl, $opt = self::SCHEME | self::HOST | self::PORT | self::CREDENTIALS | self::PATH | self::QUERY | self::FRAGMENT)
	{
		if ($newUrl instanceof Url) {
			$url = $newUrl;
		} else if (is_string($newUrl)) {
			$url = new Url($newUrl);
		} else {
			throw new InvalidUrlException();
		}
		$scheme = ($opt & self::SCHEME) === self::SCHEME;
		$host = ($opt & self::HOST) === self::HOST;
		$port = ($opt & self::PORT) === self::PORT;
		$creds = ($opt & self::CREDENTIALS) === self::CREDENTIALS;
		$path = ($opt & self::PATH) === self::PATH;
		$query = ($opt & self::QUERY) === self::QUERY;
		$fragment = ($opt & self::FRAGMENT) === self::FRAGMENT;
		if ($scheme && ! $url->scheme->isEmpty()) {
			$this->scheme->set($url->scheme->get());
		}
		if ($host && ! $url->host->isEmpty()) {
			$this->host->set($url->host->get());
		}
		if ($port && ! $url->port->isEmpty()) {
			$this->port->set($url->port->get());
		}
		if ($creds && ! $url->credentials->isEmpty()) {
			$this->credentials->username = $url->credentials->username;
			$this->credentials->password = $url->credentials->password;
		}
		if ($path && ! $url->path->isEmpty()) {
			$this->path->set($url->path->get());
		}
		if ($query && ! $url->query->isEmpty()) {
			$this->query->parseComponent($url->query->__toString());
		}
		if ($fragment && ! $url->fragment->isEmpty()) {
			$this->fragment->set($url->fragment->get());
		}
		return $this;
	}

	/**
	 * Compare all or only specific components of this URL with another URL.
	 *
	 * @param string|Url $otherUrl
	 * @param int $opt
	 * @throws InvalidUrlException
	 * @return bool
	 */
	public function equals($otherUrl, $opt = self::SCHEME | self::HOST | self::PORT | self::CREDENTIALS | self::PATH | self::QUERY | self::FRAGMENT)
	{
		if ($otherUrl instanceof Url) {
			$url = $otherUrl;
		} else if (is_string($otherUrl)) {
			$url = new Url($otherUrl);
		} else {
			throw new InvalidUrlException();
		}
		$scheme = ($opt & self::SCHEME) === self::SCHEME;
		$host = ($opt & self::HOST) === self::HOST;
		$port = ($opt & self::PORT) === self::PORT;
		$creds = ($opt & self::CREDENTIALS) === self::CREDENTIALS;
		$path = ($opt & self::PATH) === self::PATH;
		$query = ($opt & self::QUERY) === self::QUERY;
		$fragment = ($opt & self::FRAGMENT) === self::FRAGMENT;
		if ($scheme && ! $this->scheme->equals($url->scheme)) {
			return false;
		}
		if ($host && ! $this->host->equals($url->host)) {
			return false;
		}
		if ($port && ! $this->port->equals($url->port)) {
			return false;
		}
		if ($creds && ! $this->credentials->equals($url->credentials)) {
			return false;
		}
		if ($path && ! $this->path->equals($url->path)) {
			return false;
		}
		if ($query && ! $this->query->equals($url->query)) {
			return false;
		}
		if ($fragment && ! $this->fragment->equals($url->fragment)) {
			return false;
		}
		return true;
	}

	/**
	 * If the URL is empty, it is invalid.
	 * If the URL contains a component that requires a host, but the host is empty, it is invalid.
	 *
	 * @return boolean
	 */
	public function isValid()
	{
		if ($this->isEmpty()) {
			return false;
		}
		$requiresHost = ! $this->port->isEmpty() || ! $this->credentials->isEmpty() || $this->scheme->equals('http') || $this->scheme->equals('https');
		if ($requiresHost && $this->host->isEmpty()) {
			return false;
		}
		$requiresPath = (! $this->query->isEmpty() || ! $this->fragment->isEmpty()) && (! $this->scheme->isEmpty() || ! $this->host->isEmpty() || ! $this->port->isEmpty() || ! $this->credentials->isEmpty());
		if ($requiresPath && $this->path->isEmpty()) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @throws \DomainException
	 * @return string
	 */
	public function getUrl()
	{
		if (! $this->isValid()) {
			throw new InvalidUrlException('Url is invalid.');
		}
		return $this->__toString();
	}

	public function __toString()
	{
		$parts = [];

		$hasBeforePath = ! $this->scheme->isEmpty() || ! $this->host->isEmpty() || ! $this->port->isEmpty() || ! $this->credentials->isEmpty();
		$hasAfterPath = ! $this->query->isEmpty() || ! $this->fragment->isEmpty();

		if ($hasBeforePath) {

			if (! $this->scheme->isEmpty()) {
				$parts[] = $this->scheme;
				$parts[] = ':';
			}

			$parts[] = '//';

			if (! $this->credentials->isEmpty()) {
				$parts[] = $this->credentials;
				$parts[] = '@';
			}

			$parts[] = $this->host;

			if (! $this->port->isEmpty()) {
				$parts[] = ':';
				$parts[] = $this->port;
			}
		}

		if ($hasAfterPath && $this->path->isEmpty()) {
			$parts[] = '/';
		} else {

			// ensure triple slashes for file:// url
			if ( ! $this->path->isAbsolute() && ! $this->scheme->isEmpty() && false === in_array($this->scheme->get(), ['http', 'https', 'ftp', 'sftp', 'ssh']) ) {
				$parts[] = '/';
			}

			$parts[] = $this->path;

		}

		if (! $this->query->isEmpty()) {
			$parts[] = '?';
			$parts[] = $this->query;
		}

		if (! $this->fragment->isEmpty()) {
			$parts[] = '#';
			$parts[] = $this->fragment;
		}

		return join('', $parts);
	}

	/**
	 *
	 * @return Url a clone.
	 */
	public function clone()
	{
		return clone $this;
	}

	public function __clone()
	{
		$this->scheme = clone $this->scheme;
		$this->host = clone $this->host;
		$this->port = clone $this->port;
		$this->credentials = clone $this->credentials;
		$this->path = clone $this->path;
		$this->query = clone $this->query;
		$this->fragment = clone $this->fragment;
	}
}
