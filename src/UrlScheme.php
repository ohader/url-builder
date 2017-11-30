<?php

namespace TS\Web\UrlBuilder;


class UrlScheme implements UrlComponentInterface
{

	private $str;

	/**
	 *
	 * @return string|NULL
	 */
	public function get()
	{
		return $this->str;
	}

	/**
	 *
	 * @param string|UrlScheme|NULL $value
	 * @throws \InvalidArgumentException
	 */
	public function set($value)
	{
		if ($value instanceof UrlScheme) {
			$this->str = $value->str;
		} else if (is_string($value)) {
			$this->str = $value;
		} else if (is_null($value)) {
			$this->str = null;
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
			throw new InvalidUrlException('Unexpected type.');
		}
		$this->str = $str;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see UrlComponentInterface::clear()
	 */
	public function clear()
	{
		$this->str = null;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see UrlComponentInterface::isEmpty()
	 */
	public function isEmpty()
	{
		return empty($this->str);
	}
	
	/**
	 * @param UrlScheme|string|NULL $other
	 * 
	 * {@inheritDoc}
	 * @see UrlComponentInterface::equals()
	 */
	public function equals($other) {
		if ($other instanceof UrlScheme) {
			return ($this->isEmpty() && $other->isEmpty()) || ($this->str === $other->str);
		}
		if (is_null($other)) {
			return is_null($this->str);
		}
		if (is_string($other)) {
			return $this->__toString() === $other;
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
		return is_null($this->str) ? '' : $this->str;
	}
}
