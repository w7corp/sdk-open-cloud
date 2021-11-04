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

use PHPUnit\Framework\TestCase;
use W7\Sdk\OpenCloud\Api\Common\Download;
use W7\Sdk\OpenCloud\Api\Module\Build;
use W7\Sdk\OpenCloud\Api\Module\Check;
use W7\Sdk\OpenCloud\Api\Module\Info;
use W7\Sdk\OpenCloud\Api\Module\Query;
use W7\Sdk\OpenCloud\Api\Module\Setting;
use W7\Sdk\OpenCloud\Api\Site\SiteToken;

class ApiModuleTest extends TestCase
{
    use Helper;
    
    public function testModuleQuery()
    {
        $response = (new Query())->setSiteInfo($this->getSiteinfo())->setTransToken($this->getTransToken())->get();
        $this->assertIsArray($response);
        $this->assertArrayHasKey('yun_shop', $response);
        
        return $response['yun_shop'];
    }
    
    public function testModuleCheck()
    {
        try {
            $response = (new Check())->setSiteInfo($this->getSiteinfo())->setTransToken($this->getTransToken())->setName('yun_shop')->get();
        } catch (\Exception $e) {
            $this->assertStringContainsString('已设置版权保护', $e->getMessage());
        }
        $response = (new Check())->setSiteInfo($this->getSiteinfo())->setName('yun_shop1')->get();
        $this->assertEquals('yun_shop1', $response['module']);
    }
    
    public function testSetting()
    {
        $type = substr(time(), -1, 1);
        
        $response = (new Setting\Save($this->fileCacher))
            ->setSiteInfo($this->getSiteInfo())
            ->setName('we7_coupon')
            ->setVersion('7.6.15')
            ->setAcid(1)
            ->setSetting([
                'coupon_type' => $type,
            ])->get();
        
        $this->assertTrue($response);
        
        $response = (new Setting\Load($this->fileCacher))
            ->setSiteInfo($this->getSiteInfo())
            ->setName('we7_coupon')
            ->setVersion('7.6.15')
            ->setAcid(1)->get();
        
        $this->assertIsArray($response);
        $this->assertEquals($response['params']['0']['name'], 'coupon_type');
        $this->assertEquals($response['setting']['coupon_type'], $type);
    }
    
    public function testModuleBuild(array $module)
    {
        $response = (new Build($this->fileCacher))
            ->setSiteInfo($this->getSiteinfo())
            ->setName($module['name'])
            ->setVersion($module['version'])
            ->setTransToken($this->getTransToken())
            ->get();
        
        $this->assertArrayHasKey('files', $response);
        $this->assertArrayHasKey('version', $response);
        $this->assertNotEmpty($response['manifest']);
        $this->assertNotEmpty($response['scripts']);
        
        return $response['files'][0];
    }
    
    public function testModuleInfo(array $module)
    {
        $response = (new Info())->setName($module['name'])->setSiteInfo($this->getSiteInfo())->setTransToken($this->getTransToken())->get();
        $this->assertEquals($response['id'], '4048');
    }
    
    public function testShippingModuleFile()
    {
        $path     = '/we7_coupon/class/weixinstore.class.php';
        $start    = microtime(true);
        $response = (new Download())->setSiteInfo($this->getSiteInfo())->setTransToken($this->getTransToken())->setPath($path)->get();
        echo microtime(true) - $start;
        
        $this->assertEquals('/addons' . $path, $response['path']);
    }
    
    public function testSiteToken()
    {
        $siteInfo = $this->getSiteinfo();
        $siteInfo->setMethod('application.build' . self::$random);
        
        $response = (new SiteToken($this->fileCacher))->setSiteInfo($siteInfo)->get();
        
        $this->assertEquals(strlen($response['token']), 32);
        
        return $response['token'];
    }
}
