<?php

/**
 * WeEngine Cloud SDK System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Sdk\Cloud\Cache;

use W7\Sdk\Cloud\Util\Common;

class File implements CacheInterface
{
	private $cachePath;

	public function __construct($cachePath)
	{
		if (empty($cachePath)) {
			throw new \RuntimeException('必须指定缓存目录');
		}

		if (false !== strpos($cachePath, './') || false !== strpos($cachePath, '../')) {
			throw new \RuntimeException('路径必须为绝对路径');
		}

		if (!Common::writeAble($cachePath)) {
			throw new \RuntimeException('目录不存在或是不可写');
		}

		$this->cachePath = rtrim($cachePath, '/');
	}

	public function save($key, $data)
	{
		if (empty($data)) {
			return true;
		}

		if (false !== strpos($key, './') || false !== strpos($key, '../') || false !== strpos($key, '/')) {
			throw new \RuntimeException('非法的名称');
		}

		return file_put_contents(sprintf('%s/%s', $this->cachePath, $key), serialize($data));
	}

	public function load($key, $delete = true)
	{
		$cacheFile = sprintf('%s/%s', $this->cachePath, $key);
		if (!file_exists($cacheFile)) {
			return '';
		}
		$data = file_get_contents($cacheFile);
		if (empty($data)) {
			return '';
		}
		if ($delete) {
			@unlink($cacheFile);
		}
		return Common::unserialize($data);
	}
}
