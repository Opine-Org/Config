<?php
namespace Config;

class Config {
	private static $storage = [];
	private static $attempted = [];
	
	public static function __callstatic($config, $args=[]) {
		$argCount = count($args);
		switch ($argCount) {
			case 0:
				return Config::get($config);
				
			case 1:
				return Config::set($config, $args[0]);
				
			case 2:
				return Config::append($config, $args[0], $args[1]);

			case 3:
				return Config::append($config, $args[0], $args[1], $args[2]);
		}
	}

	private static function get($config) {
		if (!isset(self::$attempted[$config]) && !isset(self::$storage[$config])) {
			self::$attempted[$config] = true;
			Config::set($config);
		}
		if (isset(self::$storage[$config])) {
			return self::$storage[$config];
		}
		return [];
	}

	private static function append ($config, $key, $value, $mode='replace') {
		if (!isset(self::$attempted[$config]) && !isset(self::$storage[$config])) {
			self::$attempted[$config] = true;
			Config::set($config);
		}
		if (!isset(self::$storage[$config][$key])) {
			self::$storage[$config][$key] = []; 
		} elseif (!is_array(self::$storage[$config][$key])) {
			throw new Exception ('Can not append to a config variable that is not an array.');
		}
		if ($mode == 'push') {
			self::$storage[$config][$key][] = $value;
		} else {
			self::$storage[$config][$key] = $value;
		}
	}
	
	private static function set($config, array $instance=[]) {
		self::$attempted[$config] = true;
		if (isset(self::$storage[$config])) {
			self::$storage[$config] = array_merge(self::$storage[$config], $instance);
			return; 
		}
		$project = [];
		$theme = [];
		$configData = [];
		$themeName = 'basic';

		self::initCB($instance);
		self::fromPath($project, $_SERVER['DOCUMENT_ROOT'] . '/config/' . $config . '.php');
		self::$storage[$config] = array_merge($configData, $theme, $project, $instance);
	}

	private static function fromPath (&$data, $path) {
		if (!file_exists($path)) {
			return [];
		}
		$data = include $path;		
		self::initCB($data);
	}

	private static function initCB (&$data) {
		if (isset($data['initCB'])) {
			$cb = $data['initCB'];
			$data = $cb($data);
		}
	}
}