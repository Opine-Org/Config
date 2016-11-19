<?php
/**
 * Opine\Config\Model
 *
 * Copyright (c)2013, 2014, 2015, 2016, 2017 Ryan Mahoney, https://github.com/Opine-Org <ryan@virtuecenter.com>
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
use Symfony\Component\Yaml\Yaml;

class Model
{
    private $root;
    private $cache;
    private $cacheFile;
    private $cacheFolder;
    private $environment;
    private $projectName;
    private $cachePrefix;

    public function __construct(string $root, CacheInterface $cache)
    {
        $this->root = $root;
        $this->cache = $cache;
        $this->cacheFile = $this->root.'/../var/cache/config.json';
        $this->cacheFolder = $this->root.'/../var/cache';

        // set environment
        $this->environment = 'default';
        $test = getenv('OPINE_ENV');
        if ($test !== false) {
            $this->environment = $test;
        }

        // set environment
        $this->projectName = 'project';
        $test = getenv('OPINE_PROJECT');
        if ($test !== false) {
            $this->projectName = $test;
        }

        $this->cachePrefix = $this->projectName . $this->environment;
    }

    public function getCacheFileData()
    {
        if (!file_exists($this->cacheFile)) {
            return [];
        }
        $config = (array) json_decode(file_get_contents($this->cacheFile), true);
        if (isset($config[$this->environment])) {
            return $config[$this->environment];
        } elseif (isset($config['default'])) {
            return $config['default'];
        }

        return [];
    }

    public function build()
    {
        // establish the default folder
        $config['default'] = $this->processFolder($this->root.'/../config/settings');

        // environments are represented by sub-folders with one level of depth
        $environments = glob($this->root.'/../config/settings/*', GLOB_ONLYDIR) ?: [];

        // loop through each environment
        foreach ($environments as $directory) {
            // the environment name is the last piece of each path
            $env = explode('/', $directory);
            $env = array_pop($env);

            // process the sub-folder
            $config[$env] = $this->processFolder($directory);

            // merge the values with the default values for each config
            foreach ($config[$env] as $configName => $value) {
                if (!isset($config['default'][$configName])) {
                    continue;
                }
                $config[$env][$configName] = array_merge($config['default'][$configName], $config[$env][$configName]);
            }

            // if the default has any configurations not in the environment, add those too
            foreach ($config['default'] as $configName => $value) {
                if (isset($config[$env][$configName])) {
                    continue;
                }
                $config[$env][$configName] = $value;
            }
        }

        // if the current environment is not present, derive it from the default
        if (!isset($config[$this->environment])) {
            $config[$this->environment] = $config['default'];
        }

        // cache this information for future reference
        $this->cache->set($this->cachePrefix.'-config', json_encode($config));
        if (!file_exists($this->cacheFolder)) {
            mkdir($this->cacheFolder, 0777, true);
        }
        file_put_contents($this->cacheFile, json_encode($config, JSON_PRETTY_PRINT));
    }

    private function processFolder($folder)
    {
        $files = glob($folder.'/*.yml');
        if ($files === false) {
            return [];
        }
        $data = [];
        foreach ($files as $configFile) {
            $configName = basename($configFile, '.yml');
            $config = $this->yaml($configFile);
            if ($config === false) {
                throw new Exception('error in YAML file: '.$configFile);
            }
            if (!isset($config['settings'])) {
                throw new Exception('all config files must be under the top level settings key: '.$configFile);
            }
            $data[$configName] = $config['settings'];
        }

        return $data;
    }

    private function yaml($configFile)
    {
        return Yaml::parse(file_get_contents($configFile));
    }
}
