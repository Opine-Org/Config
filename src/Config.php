<?php
/**
 * Opine\Config
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
namespace Opine;

class Config {
    private $_root;
    private $_storage;
    private $_attempted;
    private $_noCache = false;
    private $_cache = false;
    private $_id;
    private $_separator = '/../';

    public function __construct ($root, $cache) {
        $this->_root = $root;
        if (substr($this->_root, -7) != '/public') {
            $this->_separator = '/';
        }
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
            $this->fromPath($project, $this->_root . $this->_separator . 'config/' . $config . '.php');
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
        return include $this->_root . $this->_separator . 'config/' . $config . '.php'; 
    }
}