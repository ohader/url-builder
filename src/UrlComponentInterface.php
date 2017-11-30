<?php

namespace TS\Web\UrlBuilder;


interface UrlComponentInterface
{

	/**
	 * Parses the component as it appears in a URL.
	 *
	 * @param string $str
	 * @throws InvalidUrlException
	 */
	function parseComponent($str);

	/**
	 * Makes the component empty.
	 */
	function clear();

	/**
	 * Checks whether the component is empty.
	 *
	 * @return boolean
	 */
	function isEmpty();

	/**
	 * Checks whether the component is equal to the given variable.
	 * 
	 * @param mixed $other
	 * @return bool
	 */
	function equals($other);
	
	/**
	 * Returns the component as it appears in the URL.
	 *
	 * @return string
	 */
	function __toString();
}

