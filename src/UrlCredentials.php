<?php

namespace TS\Web\UrlBuilder;


class UrlCredentials implements UrlComponentInterface
{

	/**
	 *
	 * @var string
	 */
	public $username;

	/**
	 *
	 * @var string
	 */
	public $password;

	/**
	 * Create a credentials component.
	 *
	 * In the URL "http://pete:pass@domain.tld", the string
	 * "pete:pass" is the credentials component. You would
	 * pass this string to this constructor to parse it.
	 *
	 * An empty string is also an acceptable input. This means
	 * that the credentials are omitted.
	 *
	 * @param string|NULL $compontent
	 *        	the raw component
	 * @throws \InvalidArgumentException
	 * @return self
	 */
	public function __construct($compontent = null)
	{
		if (! is_null($compontent)) {
			$this->parseComponent($compontent);
		}
	}

	/**
	 * Parses a credentials component as it appears in a URL.
	 * Example:
	 *
	 * In the URL "http://pete:pass@domain.tld", the string
	 * "pete:pass" is the credentials component. You would
	 * pass this string to this function to parse it.
	 *
	 * An empty string is also an acceptable input. This means
	 * that the credentials are omitted.
	 *
	 * @param string $str
	 * @throws InvalidUrlException
	 */
	public function parseComponent($str)
	{
		if (! is_string($str)) {
			throw new InvalidUrlException('Unexpected type.');
		}
		$lastchar = substr($str, - 1);
		if ($lastchar === '@') {
			$str = substr($str, 0, - 1);
		}
		$p = explode(':', $str);
		if (count($p) > 2) {
			throw new InvalidUrlException('Invalid colon count.');
		}
		$this->username = rawurldecode($p[0]);
		if (count($p) > 1) {
			$this->password = rawurldecode($p[1]);
		}
	}

	/**
	 * Makes the credentials empty.
	 */
	public function clear()
	{
		$this->username = $this->password = null;
	}

	/**
	 * Checks whether a username and/or password is present.
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
		return empty($this->username) && empty($this->password);
	}

	/**
	 *
	 * @param UrlCredentials|NULL $other
	 *
	 * {@inheritdoc}
	 * @see UrlComponentInterface::equals()
	 */
	public function equals($other)
	{
		if ($other instanceof UrlCredentials) {
			return ($this->isEmpty() && $other->isEmpty()) || ($this->username === $other->username && $this->password === $other->password);
		}
		if (is_null($other)) {
			return $this->isEmpty();
		}
		return false;
	}

	/**
	 * Returns the credentials component as it appears in the URL.
	 *
	 * A username "peter@example.com" and password "pass:/123" are
	 * returned as "peter%40example.com:pass%3A%2F123".
	 *
	 * @return string
	 */
	public function __toString()
	{
		if ($this->isEmpty()) {
			return '';
		}
		$p = [];
		if (! empty($this->username)) {
			$p[] = rawurlencode($this->username);
		}
		if (! empty($this->password)) {
			$p[] = rawurlencode($this->password);
		}
		return join(':', $p);
	}
}
