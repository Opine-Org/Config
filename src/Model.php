<?php
/**
 * Opine\Config\Model
 *
 * Copyright (c)2013, 2014 Ryan Mahoney, https://github.com/Opine-Org <ryan@virtuecenter.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Opine\Config;

use Exception;
use Opine\Interfaces\Cache as CacheInterface;

class Model {
    private $root;
    private $cache;

    public function __construct ($root, CacheInterface $cache) {
        $this->root = $root;
        $this->cache = $cache;
        $this->cacheFile = $this->root . '/../var/cache/config.json';
    }

    public function getCacheFileData () {
        if (!file_exists($this->cacheFile)) {
            return [];
        }
        $config = (array)json_decode(file_get_contents($this->cacheFile), true);
        $environment = 'default';
        if (isset($_SERVER['OPINE_ENV'])) {
            $environment = $_SERVER['OPINE_ENV'];
        } else {
            $test = getenv('OPINE_ENV');
            if ($test !== false) {
                $environment = $test;
            }
        }
        if (isset($config[$environment])) {
            return $config[$environment];
        } elseif (isset($config['default'])) {
            return $config['default'];
        }
        return [];
    }

    public function build () {
        $config['default'] = $this->processFolder($this->root . '/../configs');
        $environments = glob($this->root . '/../configs/*', GLOB_ONLYDIR);
        if ($environments != false) {
            foreach ($environments as $directory) {
                $env = explode('/', $directory);
                $env = array_pop($env);
                if ($env === 'layouts') {
                    continue;
                }
                $config[$env] = $this->processFolder($directory);
                foreach ($config[$env] as $configName => $value) {
                    if (!isset($config['default'][$configName])) {
                        continue;
                    }
                    $config[$env][$configName] = array_merge($config['default'][$configName], $config[$env][$configName]);
                }
                foreach ($config['default'] as $configName => $value) {
                    if (isset($config[$env][$configName])) {
                        continue;
                    }
                    $config[$env][$configName] = $value;
                }
            }
        }
        $this->cache->set($this->root . '-config', json_encode($config));
        file_put_contents($this->cacheFile, json_encode($config, JSON_PRETTY_PRINT));
    }

    private function processFolder ($folder) {
        $files = glob($folder . '/*.php');
        if ($files === false) {
            return [];
        }
        $data = [];
        foreach ($files as $configFile) {
            $configName = basename($configFile, '.php');
            $data[$configName] = include $configFile;
        }
        return $data;
    }
}