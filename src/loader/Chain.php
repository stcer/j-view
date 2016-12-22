<?php

namespace j\view\loader;

use j\view\Exception;
use j\view\LoaderInterface;

/**
 * Class LoaderChain
 * @package j\view\loader
 */
class Chain implements LoaderInterface{

	/**
	 * @var array
	 */
	protected $loaders = array();

	/**
	 * @var array
	 */
	private $hasSourceCache = array();

	/**
	 * @param string $name
	 * @return string
	 * @throws Exception
	 */
	public function getSource($name){
		$exceptions = array();
		foreach ($this->loaders as $loader) {
			if (!$loader->exists($name)) {
				continue;
			}

			try {
				return $loader->getSource($name);
			} catch (Exception $e) {
				$exceptions[] = $e->getMessage();
			}
		}

		throw new Exception(sprintf('Template "%s" is not defined%s.', $name, $exceptions ? ' ('.implode(', ', $exceptions).')' : ''));
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists($name) {
		if (isset($this->hasSourceCache[$name])) {
			return $this->hasSourceCache[$name];
		}

		foreach ($this->loaders as $loader) {
			if ($loader->exists($name)) {
				return $this->hasSourceCache[$name] = true;
			}
		}
		return $this->hasSourceCache[$name] = false;
	}

	/**
	 * Constructor.
	 *
	 * @param LoaderInterface[] $loaders An array of loader instances
	 */
	public function __construct(array $loaders = array()){
		foreach ($loaders as $loader) {
			$this->addLoader($loader);
		}
	}

	/**
	 * Adds a loader instance.
	 *
	 * @param LoaderInterface $loader A Loader instance
	 */
	public function addLoader(LoaderInterface $loader){
		$this->loaders[] = $loader;
		$this->hasSourceCache = array();
	}
}