<?php
namespace Config;
use Cache\Cache;

class ConfigRoute {
	public static function build ($root) {
		$dirFiles = glob($root . '/config/*.php');
		Config::cacheToggle();
		foreach ($dirFiles as $config) {
			$config = basename($config, '.php');
			$key = $root . '-config-' . $config;
			Cache::factory()->delete($key);
			$data = call_user_func(['Config\Config', $config]);
			try {
				$data = serialize($data);
			} catch (\Exception $e) {
				continue;
			}
			Cache::factory()->set($key, $data, 2, 0);
		}
		Config::cacheToggle();
	}
}