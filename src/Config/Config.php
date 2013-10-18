<?php
namespace Config;

class Config {
	private $root;
	private $storage = [];
	private $attempted = [];
	private $noCache = false;
	private $cache = false;

	public function __construct ($root, $cache) {
		$this->root = $root;
		$this->cache = $cache;
	}

	public function cacheToggle () {
		if ($this->noCache) {
			$this->noCache = false;
		} else {
			$this->noCache = true;
		}
	}

	public function __get ($config) {
		if (!isset($this->attempted[$config]) && !isset($this->storage[$config])) {
			$this->__set($config);
		}
		if (isset($this->storage[$config])) {
			return $this->$storage[$config];
		}
		return [];
	}

	public function __set ($config, Array $instance=[]) {
		$this->attempted[$config] = true;
		if (isset($this->storage[$config])) {
			$this->storage[$config] = new \ArrayObject(array_merge($this->storage[$config], $instance));
			return; 
		}
		$project = [];
		if ($this->noCache == true || $this->fromMemory($project, $this->root . '-config-' . $config) === false) {
			$this->fromPath($project, $this->root . '/../config/' . $config . '.php');
		}
		$this->storage[$config] = new \ArrayObject(array_merge($project, $instance));
	}

	private function fromMemory (&$data, $key) {
		$data = $this->cache->get($key, 2);
		if ($data !== false) {
			$data = unserialize($data);
			return true;
		}
		return false;
	}

	private function fromPath (&$data, $path) {
		if (!file_exists($path)) {
			return [];
		}
		$data = include $path;
	}
}