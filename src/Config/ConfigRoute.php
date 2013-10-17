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
		$dirFiles = glob($root . '/../config/*.php');
		$this->config->cacheToggle();
		$configObj = $this->config;
		foreach ($dirFiles as $config) {
			$config = basename($config, '.php');
			$key = $root . '-config-' . $config;
			$this->cache->delete($key);
			$data = $configObj::get($config);
			try {
				$data = serialize($data);
			} catch (\Exception $e) {
				continue;
			}
			$this->cache->set($key, $data, 2, 0);
		}
		$this->config->cacheToggle();
	}
}