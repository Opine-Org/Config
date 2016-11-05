<?php
/**
 * Opine\Config\Service
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

use Opine\Cache\Service as Cache;
use Opine\Interfaces\Config as ConfigInterface;

class Service implements ConfigInterface
{
    private $cache = false;
    private $root;

    public function __construct($root)
    {
        $this->root = $root;
    }

    public function cacheSet(Array $config = [])
    {
        if (empty($config)) {
            $model = new Model($this->root, new Cache($this->root));
            $model->build();
            $this->cache = $model->getCacheFileData();

            return true;
        }
        $this->cache = $config;

        return true;
    }

    public function get($key)
    {
        if (substr_count($key, '.') == 1) {
            $parts = explode('.', $key);
            if (!isset($this->cache[$parts[0]])) {
                return null;
            }
            if (!isset($this->cache[$parts[0]][$parts[1]])) {
                return null;
            }
            return $this->cache[$parts[0]][$parts[1]];
        }

        if (!isset($this->cache[$key])) {
            return null;
        }

        return $this->cache[$key];
    }
}
