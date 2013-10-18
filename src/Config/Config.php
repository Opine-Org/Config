<?php
namespace Config;

class Config {
	private $_root;
	private $_storage;
	private $_attempted;
	private $_noCache = false;
	private $_cache = false;
	private $_id;

	public function __construct ($root, $cache) {
		$this->_root = $root;
		$this->_cache = $cache;
		$this->_storage = new \ArrayObject();
		$this->_attempted = new \ArrayObject();
		$this->_id = uniqid();
	}

	public function __get ($config) {
		if (!isset($this->_attempted[$config]) && !isset($this->_storage[$config])) {
			$this->__set($config);
		}
		if (isset($this->_storage[$config])) {
			return $this->_storage[$config];
		}
		return [];
	}

	public function __set ($config, Array $instance=[]) {
		$this->_attempted[$config] = true;
		if (isset($this->_storage[$config])) {
			$this->_storage[$config] = new \ArrayObject(array_merge($this->_storage[$config], $instance));
			return;
		}
		$project = [];
		if ($this->fromMemory($project, $this->_root . '-config-' . $config) === false) {
			$this->fromPath($project, $this->_root . '/../config/' . $config . '.php');
		}
		if (!is_array($project)) {
			if (is_array($instance)) {
				$this->_storage[$config] = new \ArrayObject($instance);
				return;
			} else {
				$this->_storage[$config] = new \ArrayObject();
				return;
			}
		}
		$this->_storage[$config] = new \ArrayObject(array_merge($project, $instance));
	}

	private function fromMemory (&$data, $key) {
		$data = $this->_cache->get($key, 2);
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

	public function fromDisk ($config) {
		return include $this->_root . '/../config/' . $config . '.php';	
	}
}