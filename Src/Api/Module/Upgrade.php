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

namespace W7\Api\Module;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use W7\Contracts\ModuleUpgradeInterface;
use W7\Sdk\Cloud\Exception\ModuleRootPathUndefinedException;

class Upgrade
{
	protected $version;
	protected $name;
	
	/**
	 * 获取从当前版本到最新版本所有要执行的升级class
	 * @param string $name
	 * @param string $currentVersion
	 * @return array
	 */
	public function upInit(string $name, string $currentVersion = '')
	{
		if (!defined('ADDONS_PATH') || empty(ADDONS_PATH)) {
			throw new \RuntimeException('请先设置应用根目录常量!');
		}
		$result = [];
		
		$localAdapter = new LocalFilesystemAdapter(ADDONS_PATH . '/' . $name);
		$fileSystem   = new Filesystem($localAdapter);
		
		$upgradeDirPath = 'upgrade/';
		$allFiles       = $fileSystem->listContents($upgradeDirPath, true);
		/** @var StorageAttributes $file */
		foreach ($allFiles as $file) {
			if (!$file->isDir()) {
				continue;
			}
			$dirVersion = str_replace($upgradeDirPath, '', $file->path());
			if (1 !== version_compare($dirVersion, $currentVersion)) {
				continue;
			}
			$className = '\W7\Addons\\' . $name . '\Upgrade' . str_replace('.', '', $dirVersion) . '\Up';
			include_once ADDONS_PATH . '/' . $name . '/upgrade/' . $dirVersion . '/Up.php';
			if (!class_exists($className)) {
				continue;
			}
			$result[] = ['version' => $dirVersion, 'class_name' => $className];
		}
		return $result;
	}
	
	/**
	 * 升级数据库结构
	 * @param string $moduleName
	 * @param string $toVersion
	 * @param callable $callback
	 * @return bool
	 * @throws ModuleRootPathUndefinedException
	 * @throws \League\Flysystem\FilesystemException
	 */
	public function upDatabase(string $moduleName, string $toVersion, callable $callback)
	{
		$result = true;
		
		$classNames = $this->upInit($moduleName);
		foreach ($classNames as $className) {
			if (0 !== version_compare($className['version'], $toVersion)) {
				continue;
			}
			$up = new $className['class_name']();
			if (!($up instanceof ModuleUpgradeInterface)) {
				continue;
			}
			$result = $callback($up->database());
			$up     = null;
		}
		return $result;
	}
	
	/**
	 * 执行升级脚本
	 * @param string $moduleName
	 * @param string $toVersion
	 * @return bool
	 * @throws ModuleRootPathUndefinedException
	 * @throws \League\Flysystem\FilesystemException
	 */
	public function upScript(string $moduleName, string $toVersion)
	{
		$result = true;
		
		$classNames = $this->upInit($moduleName);
		foreach ($classNames as $className) {
			if (0 !== version_compare($className['version'], $toVersion)) {
				continue;
			}
			$up = new $className['class_name']();
			if (!($up instanceof ModuleUpgradeInterface)) {
				continue;
			}
			$result = $up->script();
		}
		return $result;
	}
}
