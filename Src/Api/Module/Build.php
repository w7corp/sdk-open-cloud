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
use League\Flysystem\StorageAttributes;
use W7\Contracts\ModuleUpgradeInterface;
use W7\Sdk\Cloud\Util\Common;
use W7\Sdk\Cloud\Request\We7Request;
use W7\Sdk\Cloud\Util\SiteInfoTraiter;

class Build extends We7Request
{
	protected $apiPath  = '/module/build';
	protected $method   = 'module.build';
	protected $postData = [];
	
	protected $version;
	protected $name;
	protected $moduleRootPath;
	//操作类型:安装install;更新upgrade;卸载uninstall
	protected $type = 'install';
	
	use SiteInfoTraiter;
	
	public function get()
	{
		if (empty($this->siteInfo)) {
			throw new \RuntimeException('缺少站点信息参数');
		}
		if (empty($this->name)) {
			throw new \RuntimeException('缺点模块名称');
		}
		$data                   = $this->siteInfo->toArray();
		$data['module']         = $this->name;
		$data['module_version'] = $this->version;
		$data['method']         = $data['method'] ?? $this->method;
		$data['type']           = $this->type;
		
		$this->postData = array_merge([], $this->postData, $data);
		
		return parent::post($this->postData);
	}
	
	/**
	 * 获取从当前版本到最新版本所有要执行的升级class
	 * @return array
	 * @throws \League\Flysystem\FilesystemException
	 */
	private function getUpClassNames()
	{
		$localAdapter = new LocalFilesystemAdapter($this->moduleRootPath);
		$fileSystem   = new Filesystem($localAdapter);
		
		$pathArray  = explode('/', $this->moduleRootPath);
		$this->name = (is_array($pathArray) && !empty($pathArray)) ? end($pathArray) : '';
		if (empty($this->name)) {
			return [];
		}
		
		$upgradeDirPath = 'upgrade/';
		$allFiles       = $fileSystem->listContents($upgradeDirPath, true);
		/** @var StorageAttributes $file */
		foreach ($allFiles as $file) {
			if (!$file->isDir()) {
				continue;
			}
			$dirVersion = str_replace($upgradeDirPath, '', $file->path());
			if (1 != version_compare($dirVersion, $this->version)) {
				continue;
			}
			$className = '\W7\Addons\\' . ucfirst($this->name) . '\Upgrade' . str_replace('.', '', $dirVersion) . '\Up';
			include_once $this->moduleRootPath . '/upgrade/' . $dirVersion . '/Up.php';
			if (!class_exists($className)) {
				continue;
			}
			$result[] = $className;
		}
		return $result;
	}
	
	/**
	 * 获取升级数据库结构sql
	 * @return array
	 */
	public function upDatabase()
	{
		$sql = [];
		
		$classNames = $this->getUpClassNames();
		foreach ($classNames as $className) {
			$up = new $className();
			if (!($up instanceof ModuleUpgradeInterface)) {
				continue;
			}
			$sql = array_merge([], $sql, $up->database());
			$up  = null;
		}
		return $sql;
	}
	
	/**
	 * 执行升级脚本
	 * @return bool
	 */
	public function upScript()
	{
		$classNames = $this->getUpClassNames();
		foreach ($classNames as $className) {
			$up = new $className();
			if (!($up instanceof ModuleUpgradeInterface)) {
				continue;
			}
			$up->script();
		}
		return true;
	}
	
	/**
	 * 降版本/卸载数据库结构
	 * @return string
	 */
	public function downDatabase()
	{
		return ['down-sql'];
	}
	
	/**
	 * 降版本/卸载升级脚本
	 * @return bool
	 */
	public function downScript()
	{
		return true;
	}
	
	protected function decode($method, $response)
	{
		$data = parent::decode($method, $response);
		//保存transToken
		if (!empty($data['token'])) {
			$this->cache->save('trans.token', Common::authcode($data['token'], 'ENCODE'));
		}
		return $data;
	}
	
	public function setModuleRootPath(string $path)
	{
		$this->moduleRootPath = $path;
		return $this;
	}
	
	/**
	 * @param mixed $version
	 * @return Build
	 */
	public function setVersion($version)
	{
		$this->version = $version;
		return $this;
	}
	
	/**
	 * @param mixed $name
	 * @return Build
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @param string $type
	 * @return Build
	 */
	public function setType(string $type)
	{
		$this->type = $type;
		return $this;
	}
}
