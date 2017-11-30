<?php

namespace TS\Web\UrlBuilder;


use TS\Filesystem\Path;


class UrlPath implements UrlComponentInterface
{

	/**
	 * The decoded and parsed path.
	 *
	 * @var Path
	 */
	private $path;

	/**
	 *
	 * @var Url
	 */
	private $url;

	/**
	 *
	 * @param Url|NULL $url
	 */
	public function __construct(Url $url = null)
	{
		$this->path = new Path('');
		$this->url = $url;
	}

	/**
	 * Parses a path component as it appears in a URL.
	 *
	 * @param string $str
	 * @throws \InvalidArgumentException
	 */
	public function parseComponent($str)
	{
		if (! is_string($str)) {
			throw new InvalidUrlException('Unexpected type.');
		}
		if (strpos($str, ' ') !== false) {
			throw new InvalidUrlException('Path contains whitespace.');
		}
		$p = explode('/', $str);
		foreach ($p as $i => $v) {
			$p[$i] = rawurldecode($v);
		}
		$decodedPath = join('/', $p);
		$this->path->set($decodedPath);
	}

	/**
	 * Returns the decoded path.
	 *
	 * The path will start with a slash if the host is set, regardless whether
	 * the slash was present in the parsed URL and regardless whether you
	 * included the slash when setting the path via set().
	 *
	 * Please not that __toString() does not prepend the slash.
	 *
	 * @return string
	 */
	public function get()
	{
		if (! is_null($this->url) && ! $this->url->host->isEmpty()) {
			return Path::info('/')->resolve($this->path)->get();
		}
		return $this->path->get();
	}

	/**
	 * Sets the decoded path.
	 *
	 * @param string|UrlPath|Path $str
	 * @throws \InvalidArgumentException
	 */
	public function set($str)
	{
		if ($str instanceof Path) {
			$this->path->set($str->get());
		} else if ($str instanceof UrlPath) {
			$this->path->set($str->get());
		} else if (is_string($str)) {
			$this->path->set($str);
		} else {
			throw new \InvalidArgumentException('Unexpected type.');
		}
	}

	/**
	 * Normalizes the path, resolving parent-references (..) and droppping current-references (.) where
	 * sensible.
	 *
	 * @return self
	 */
	public function normalize()
	{
		$this->path = $this->path->normalize();
		return $this;
	}

	/**
	 * Makes the path empty.
	 *
	 * @return self
	 */
	public function clear()
	{
		$this->path->set('');
		return $this;
	}

	/**
	 * URLs can be relative if scheme and host are omitted.
	 *
	 * Examples for relative URLs are:
	 * - ../foo.html
	 * - foo.html
	 *
	 * However, they can have an absolute path at the same time:
	 * - /index.html
	 *
	 * You can use this method to determine whether the path is absolute or not.
	 *
	 * @return boolean
	 */
	public function isAbsolute()
	{
		if (! is_null($this->url) && $this->path->isEmpty()) {
			return ! $this->url->host->isEmpty();
		}
		return $this->path->isAbsolute();
	}

	/**
	 * Returns the filename part of the path.
	 *
	 * @return string
	 */
	public function filename()
	{
		return $this->path->filename();
	}

	/**
	 * Return the path excluding the filename.
	 *
	 * If the URL has a host but no path, we still return '/'.
	 *
	 * @return string
	 */
	public function dirname()
	{
		if (! is_null($this->url) && $this->path->isEmpty() && ! $this->url->host->isEmpty()) {
			return '/';
		}
		return $this->path->dirname();
	}

	/**
	 * Return the file extension of the path.
	 *
	 * @return string
	 */
	public function extension()
	{
		return $this->path->extension();
	}

	/**
	 * Checks whether the path is empty.
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
		return $this->path->isEmpty();
	}

	/**
	 *
	 * @param UrlPath|string|NULL $other
	 *
	 * {@inheritdoc}
	 * @see UrlComponentInterface::equals()
	 */
	public function equals($other)
	{
		if ($other instanceof UrlPath) {
			return ($this->isEmpty() && $other->isEmpty()) || ($this->path->equals($other->path));
		}
		if (is_null($other)) {
			return $this->isEmpty();
		}
		if (is_string($other)) {
			return $this->path->equals($other);
		}
		return false;
	}

	/**
	 * Returns the path component as it appears in the URL.
	 *
	 * The parts of the path are URL-encoded as specified in RFC 3986.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$p = explode('/', $this->path->get());
		foreach ($p as $i => $v) {
			$p[$i] = rawurlencode($v);
		}
		return join('/', $p);
	}

	public function __clone()
	{
		$this->path = clone $this->path;
	}

}
