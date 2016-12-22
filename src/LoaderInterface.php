<?php

namespace j\view;

/**
 * Interface LoaderInterface
 * @package j\view
 */
interface  LoaderInterface {
	/**
	 * @param string $name
	 * @return string
	 */
	public function getSource($name);

	/**
	 * Check if we have the source code of a template, given its name.
	 *
	 * @param string $name The name of the template to check if we can load
	 *
	 * @return bool If the template source code is handled by this loader or not
	 */
	public function exists($name);
}