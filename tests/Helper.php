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

namespace W7\Sdk\OpenCloud\Tests;

use W7\Sdk\OpenCloud\Cache\File;
use W7\Sdk\OpenCloud\Message\SiteInfo;

trait Helper
{
    private static $rootPath;
    private $fileCacher;
    
    public static $random;
    
    public static function setUpBeforeClass(): void
    {
        !defined('IN_IA')   && define('IN_IA', true);
        !defined('IA_ROOT') && define('IA_ROOT', self::$rootPath);
        
        $config = [];
        require_once self::$rootPath . '/data/config.php';
        require_once self::$rootPath . '/framework/class/loader.class.php';
        
        load()->func('global');
        load()->func('pdo');
        
        $GLOBALS['_W']['config']                   = $config;
        $GLOBALS['_W']['config']['db']['tablepre'] = !empty($GLOBALS['_W']['config']['db']['master']['tablepre']) ? $GLOBALS['_W']['config']['db']['master']['tablepre'] : $GLOBALS['_W']['config']['db']['tablepre'];
        
        $row = pdo_get('core_cache', [
            'key' => 'we7:random',
        ]);
        self::$random = unserialize($row['value'])['data'];
        echo self::$random, PHP_EOL;
        !defined('W7_CLOUD_SDK_AUTHKEY') && define('W7_CLOUD_SDK_AUTHKEY', $config['setting']['authkey']);
        parent::setUpBeforeClass();
    }
    
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        self::$rootPath   = str_replace('\\', '/', dirname(dirname(__FILE__)));
        $this->fileCacher = new File(self::$rootPath . '/data');
        parent::__construct($name, $data, $dataName);
    }
    
    /**
     * @return \W7\Sdk\OpenCloud\Message\SiteInfo
     */
    public function getSiteInfo()
    {
        $data = [
            'host'         => 'ccceshi.w7.cc',
            'token'        => 'd659404b91c7e556a5e0a528764346fe',
            'family'       => 'x',
            'php_version'  => '7.2.33',
            'version'      => '2.7.5',
            'current_host' => 'ccceshi.w7.cc',
            'release'      => '202103260001',
            'key'          => '112982',
            'client'       => '7aeaa141641e5a0fdc666b70b836e37d',
            'encode'       => 'base64',
        ];
        
        $siteInfo = new SiteInfo();
        $siteInfo->setKey($data['key']);
        $siteInfo->setFamily($data['family']);
        $siteInfo->setHost($data['host']);
        $siteInfo->setRelease($data['release']);
        $siteInfo->setToken($data['token']);
        $siteInfo->setVersion($data['version']);
        
        return $siteInfo;
    }
    
    /**
     * 测试时需要更换
     * @return string
     */
    public function getTransToken()
    {
        return 'eb5cbb58e76c8210aed08c7cf8e80011';
    }
}
