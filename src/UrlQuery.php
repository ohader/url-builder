<?php

namespace TS\Web\UrlBuilder;


// TODO keep order by storing each pair individually
// TODO keep info whether blank parameter uses = or not

class UrlQuery implements UrlComponentInterface, \Countable, \IteratorAggregate
{

	/**
	 * array<string, array<string>>
	 *
	 * Values are always string, never null.
	 *
	 * @var array
	 */
	private $params = [];

	public function __construct()
	{}

	/**
	 * Returns the value of the first parameter with the given key or $default if not found.
	 *
	 * @param string $key
	 * @param string|NULL $default
	 * @throws \InvalidArgumentException If the key is empty, the key or a value is not a string.
	 * @return string|mixed
	 */
	public function get($key, $default = null)
	{
		$this->validateKey($key);
		return $this->has($key) ? $this->params[$key][0] : $default;
	}

	/**
	 * Set one or more values for the given parameter key.
	 *
	 * @param string $key
	 * @param string ...$value
	 * @throws \InvalidArgumentException If the key is empty, the key or a value is not a string.
	 * @return self
	 */
	public function set($key, ...$value)
	{
		return $this->setArray($key, $value);
	}

	/**
	 * Sets several values for the given parameter key.
	 *
	 * @param string $key
	 * @param array $values
	 * @throws \InvalidArgumentException If the key is empty, the key is not a string, no values are given or a value is not a string.
	 * @return self
	 */
	public function setArray($key, array $values)
	{
		$this->validateKey($key);
		if (empty($values)) {
			throw new \InvalidArgumentException("Missing value.");
		}
		foreach ($values as $v) {
			$this->validateValue($v);
		}
		$this->params[$key] = [];
		foreach ($values as $v) {
			$this->params[$key][] = $v;
		}
		return $this;
	}

	/**
	 * Return all parameters with the given key.
	 *
	 * @param string $key
	 * @return boolean True if the parameter existed.
	 */
	public function remove($key)
	{
		$this->validateKey($key);
		if ($this->has($key)) {
			unset($this->params[$key]);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets several new parameters, replacing the old values if already present.
	 *
	 * The provided array must be associative with the parameter keys as array 
	 * keys and parameter values as array values.
	 * 
	 * The array values may be either a string (to set a single parameter value) 
	 * or an array (to set multiple parameter values).
	 *
	 * @param array $new
	 */
	public function replace(array $new)
	{
		foreach ($new as $key => $values) {
			$this->validateKey($key);
			if (! is_array($values)) {
				$values = $new[$key] = [
					$values
				];
			}
			foreach ($values as $v) {
				$this->validateValue($v);
			}
		}
		foreach ($new as $key => $values) {
			$this->setArray($key, $values);
		}
	}

	/**
	 * Returns true if one or more parameters have the given key.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has($key)
	{
		$this->validateKey($key);
		return array_key_exists($key, $this->params);
	}

	/**
	 * Returns all values of the parameters with the given key.
	 * If no parameter with the given key exists, an empty array is returned.
	 *
	 * @param string $key
	 * @return array
	 */
	public function getArray($key)
	{
		$this->validateKey($key);
		return $this->has($key) ? $this->params[$key] : [];
	}

	/**
	 * Get all parameters as an associative array with the parameter keys as array keys.
	 * Returns either only the first values of the parameters, or all values of the parameters.
	 *
	 * @param boolean $onlyFirst
	 * @return array
	 */
	public function toArray($onlyFirst = false)
	{
		$r = [];
		foreach ($this->params as $key => $values) {
			$r[$key] = $onlyFirst ? $values[0] : $values;
		}
		return $r;
	}

	/**
	 * Iterate over all parameters.
	 * Note that all parameters are arrays that contain one more more values.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->params);
	}

	/**
	 * Counts the parameters.
	 * Parameter keys that appear multiple times count as one array parameter.
	 *
	 * If the $key parameter is used, the number of values of this parameter are counted.
	 *
	 * @param string $key
	 * @return int
	 */
	public function count($key = null)
	{
		if (is_null($key)) {
			return count($this->params);
		}
		return count($this->getArray($key));
	}

	/**
	 * Return all paramter keys.
	 *
	 * @return array
	 */
	public function keys()
	{
		return array_keys($this->params);
	}

	/**
	 * Makes the query empty.
	 */
	public function clear()
	{
		$this->params = [];
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
		if (strpos($str, '#') !== false) {
			throw new InvalidUrlException('Query string must not contain fragment separator #.');
		}
		if (strlen($str) > 0 && trim($str) === '') {
			throw new InvalidUrlException('Query string must not be whitespace only.');
		}
		$params = [];
		$pairs = explode('&', $str);
		foreach ($pairs as $pair) {
			if ($pair === '') {
				continue;
			}
			$seperatorPos = strpos($pair, '=');
			if ($seperatorPos === false) {
				$key = urldecode($pair);
				$value = '';
			} else {
				$key = urldecode(substr($pair, 0, $seperatorPos));
				$value = urldecode(substr($pair, $seperatorPos + 1));
			}
			if ($key === '') {
				throw new InvalidUrlException('Empty parameter name in query.');
			}
			if (! array_key_exists($key, $params)) {
				$params[$key] = [];
			}
			$params[$key][] = $value;
		}
		$this->params = $params;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see UrlComponentInterface::__toString()
	 */
	public function __toString()
	{
		$parts = [];
		foreach ($this->params as $key => $values) {
			foreach ($values as $value) {
				if ($value === '') {
					$parts[] = rawurlencode($key) . '=';
				} else {
					$parts[] = rawurlencode($key) . '=' . rawurlencode($value);
				}
			}
		}
		return join('&', $parts);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see UrlComponentInterface::isEmpty()
	 */
	public function isEmpty()
	{
		return empty($this->params);
	}
	
	
	/**
	 * @param UrlQuery|NULL $other
	 *
	 * {@inheritDoc}
	 * @see UrlComponentInterface::equals()
	 */
	public function equals($other) {
		if ($other instanceof UrlQuery) {
			return ($this->isEmpty() && $other->isEmpty()) || ($this->__toString() === $other->__toString() );
		}
		if (is_null($other)) {
			return $this->isEmpty();
		}
		return false;
	}
	
	

	private function validateKey($key)
	{
		if (! is_string($key)) {
			throw new \InvalidArgumentException("Expected parameter key to be string, got " . gettype($key));
		}
		if ($key === '') {
			throw new \InvalidArgumentException("Empty parameter key.");
		}
	}

	private function validateValue($value)
	{
		if (! is_string($value)) {
			throw new \InvalidArgumentException("Expected parameter value to be string, got " . gettype($value));
		}
	}
}
