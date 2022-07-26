<?php

/**
 * WeEngine System
 *
 * (c) We7Team 2022 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Sdk\OpenCloud\Api\Module;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use W7\Sdk\OpenCloud\Contracts\ModuleUpgradeInterface;
use W7\Sdk\OpenCloud\Exception\ApiErrorException;

class Upgrade
{
    const UPGRADE_TYPE_UP   = 'Up';
    const UPGRADE_TYPE_DOWN = 'Down';
    protected $version;
    protected $name;
    private $upgradeType = 'Up';
    
    /**
     * 获取从当前版本到最新版本所有要执行的升/降级class
     * @param string $name
     * @param string $currentVersion
     * @return array
     * @throws ApiErrorException
     * @throws \League\Flysystem\FilesystemException
     */
    private function init(string $name, string $currentVersion = '')
    {
        if (!defined('ADDONS_PATH') || empty(ADDONS_PATH)) {
            throw new ApiErrorException('请先设置应用根目录常量!');
        }
        $result = [];
        
        $localAdapter = new LocalFilesystemAdapter(ADDONS_PATH . DIRECTORY_SEPARATOR . $name);
        $fileSystem   = new Filesystem($localAdapter);
        
        $upgradeDirPath = 'upgrade' . DIRECTORY_SEPARATOR;
        $allFiles       = $fileSystem->listContents($upgradeDirPath, true);
        /** @var StorageAttributes $file */
        foreach ($allFiles as $file) {
            if (!$file->isDir()) {
                continue;
            }
            $dirVersion = str_replace($upgradeDirPath, '', $file->path());
            if (self::UPGRADE_TYPE_UP == $this->upgradeType && 1 !== version_compare($dirVersion, $currentVersion)) {
                continue;
            }
            if (self::UPGRADE_TYPE_DOWN == $this->upgradeType && 1 === version_compare($dirVersion, $currentVersion)) {
                continue;
            }
            $className = '\W7\Addons\\' . $name . '\Upgrade' . str_replace('.', '', $dirVersion) . '\\' . $this->upgradeType;
            include_once ADDONS_PATH . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'upgrade' . DIRECTORY_SEPARATOR . $dirVersion . DIRECTORY_SEPARATOR . $this->upgradeType . '.php';
            if (!class_exists($className)) {
                continue;
            }
            $result[] = ['version' => $dirVersion, 'class_name' => $className];
        }
        return $result;
    }
    /**
     * 获取从当前版本到最新版本所有要执行的升级class
     * @param string $name
     * @param string $currentVersion
     * @return array
     */
    public function upInit(string $name, string $currentVersion = '')
    {
        $this->upgradeType = self::UPGRADE_TYPE_UP;
        return $this->init($name, $currentVersion);
    }
    
    public function downInit(string $name, string $currentVersion)
    {
        $this->upgradeType = self::UPGRADE_TYPE_DOWN;
        return $this->init($name, $currentVersion);
    }
    
    public function downScriptAndDatabase(string $name, string $currentVersion, callable $callback)
    {
        $database   = [];
        $classNames = $this->downInit($name, $currentVersion);
        foreach ($classNames as $className) {
            if (1 === version_compare($className['version'], $currentVersion)) {
                continue;
            }
            $down = new $className['class_name']();
            if (!($down instanceof ModuleUpgradeInterface)) {
                continue;
            }
            $down->script();
            $result = $down->database();
            if (!is_array($result)) {
                throw new ApiErrorException('卸载脚本内数据库函数返回有误(返回必须是个数组),请联系开发者处理');
            }
            $database = array_merge($database, $result);
            $down     = null;
        }
        $callback($database);
        return true;
    }
    
    /**
     * 升级数据库结构
     * @param string $moduleName
     * @param string $toVersion
     * @param callable $callback
     * @return bool
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
            $result = $up->database();
            if (!is_array($result)) {
                throw new ApiErrorException('升级脚本内数据库函数返回有误(返回必须是个数组),请联系开发者处理');
            }
            $result = $callback($result);
            $up     = null;
        }
        return $result;
    }
    
    /**
     * 执行升级脚本
     * @param string $moduleName
     * @param string $toVersion
     * @return bool
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
