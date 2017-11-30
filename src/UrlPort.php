<?php

namespace TS\Web\UrlBuilder;


class UrlPort implements UrlComponentInterface
{

	/**
	 *
	 * @var int|NULL
	 */
	private $port;

	/**
	 *
	 * @return int|NULL
	 */
	public function get()
	{
		return $this->port;
	}

	/**
	 *
	 * @param int|UrlPort|NULL $value
	 * @throws \InvalidArgumentException
	 */
	public function set($value)
	{
		if ($value instanceof UrlPort) {
			$this->port = $value->port;
		} else if (is_int($value)) {
			$this->port = $value;
		} else if (is_null($value)) {
			$this->port = null;
		} else {
			throw new \InvalidArgumentException('Unexpected type.');
		}
	}

	
	/**
	 *
	 * {@inheritdoc}
	 * @see UrlComponentInterface::parseComponent()
	 */
	public function parseComponent($str)
	{
		if (! is_string($str)) {
			throw new InvalidUrlException('Unexpected type: ' . gettype($str));
		}
		if (empty($str)) {
			$this->port = null;
		} else {
			$int = intval($str);
			if (strval($int) !== $str) {
				throw new InvalidUrlException('Invalid value.');
			}
			$this->port = $int;
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see UrlComponentInterface::clear()
	 */
	public function clear()
	{
		$this->port = null;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see UrlComponentInterface::isEmpty()
	 */
	public function isEmpty()
	{
		return empty($this->port);
	}
	
	
	/**
	 * @param UrlPort|int|NULL $other
	 * 
	 * {@inheritDoc}
	 * @see UrlComponentInterface::equals()
	 */
	public function equals($other) {
		if ($other instanceof UrlPort) {
			return ($this->isEmpty() && $other->isEmpty()) || ($this->port === $other->port);
		}
		if (is_int($other)) {
			return $this->port === $other;
		}
		if (is_null($other)) {
			return is_null($this->port);
		}
		return false;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see UrlComponentInterface::__toString()
	 */
	public function __toString()
	{
		return is_null($this->port) ? '' : strval($this->port);
	}
}
