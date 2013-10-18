<?php
namespace Config;

class ConfigRoute {
	private $cache;
	private $config;

	public function __construct ($config, $cache) {
		$this->config = $config;
		$this->cache = $cache;
	}

	public function build ($root) {
		$configObject = $this->config;
		$dirFiles = glob($root . '/../config/*.php');
		foreach ($dirFiles as $config) {
			$config = basename($config, '.php');
			$key = $root . '-config-' . $config;
			$this->cache->delete($key);
			$data = $configObject->fromDisk($config);
			try {
				$data = serialize((array)$data);
			} catch (\Exception $e) {
				continue;
			}
			$this->cache->set($key, $data, 2, 0);
		}
	}
}