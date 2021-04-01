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

namespace W7\Sdk\Cloud\Tests;

use W7\Api\Common\Download;
use W7\Api\Module\Build;
use W7\Api\Module\Check;
use W7\Api\Module\Info;
use W7\Api\Module\Query;
use W7\Api\Module\Setting;
use W7\Api\Site\SiteToken;
use W7\Sdk\Core\Tests\TestCase;

class ApiModuleTest extends TestCase {
	use Helper;

	public function testModuleQuery() {
		$_SERVER['HTTP_HOST'] = 'donknap.w7.cc';

		$response = (new Query())->setSiteInfo($this->getSiteinfo())->get();

		$this->assertIsArray($response);
		$this->assertArrayHasKey('yun_shop', $response);

		return $response['yun_shop'];
	}

	public function testModuleCheck() {
		$_SERVER['HTTP_HOST'] = 'donknap.w7.cc';

		try {
			$response = (new Check($this->fileCacher))->setSiteInfo($this->getSiteinfo())->setName('yun_shop')->get();
		} catch (\Exception $e) {
			$this->assertStringContainsString('已设置版权保护', $e->getMessage());
		}
		$response = (new Check($this->fileCacher))->setSiteInfo($this->getSiteinfo())->setName('yun_shop1')->get();
		$this->assertEquals('yun_shop1', $response['module']);
	}

	public function testSetting() {
		$_SERVER['HTTP_HOST'] = 'donknap.w7.cc';

		$type = substr(time(), -1, 1);

		$response = (new Setting\Save($this->fileCacher))
			->setSiteInfo($this->getCCCeShiSiteInfo())
			->setName('we7_coupon')
			->setVersion('7.6.15')
			->setAcid(1)
			->setSetting([
				'coupon_type' => $type,
			])->get();

		$this->assertTrue($response);

		$response = (new Setting\Load($this->fileCacher))
			->setSiteInfo($this->getCCCeShiSiteInfo())
			->setName('we7_coupon')
			->setVersion('7.6.15')
			->setAcid(1)->get();

		$this->assertIsArray($response);
		$this->assertEquals($response['params']['0']['name'], 'coupon_type');
		$this->assertEquals($response['setting']['coupon_type'], $type);
	}

	/**
	 * @depends testModuleQuery
	 * @param array $module
	 * @return mixed
	 */
	public function testModuleBuild(array $module) {
		$_SERVER['HTTP_HOST'] = 'donknap.w7.cc';

		$response = (new Build($this->fileCacher))
			->setSiteInfo($this->getSiteinfo())
			->setName($module['name'])->setVersion($module['version'])->get();

		$this->assertArrayHasKey('files', $response);
		$this->assertArrayHasKey('version', $response);
		$this->assertNotEmpty($response['manifest']);
		$this->assertNotEmpty($response['scripts']);

		return $response['files'][0];
	}

	/**
	 * @depends testModuleQuery
	 */
	public function testModuleInfo(array $module) {
		$_SERVER['HTTP_HOST'] = 'donknap.w7.cc';

		$siteInfo = $this->getSiteinfo();
		$siteInfo->setMethod('module.info' . self::$random);

		$response = (new Info($this->fileCacher))->setName($module['name'])->setSiteInfo($siteInfo)->get();

		$this->assertEquals($response['id'], '4048');
	}

	/**
	 * @depends testModuleBuild
	 * @depends testSiteToken
	 */
	public function testShippingModuleFile($file, $token) {
		$_SERVER['HTTP_HOST'] = 'donknap.w7.cc';

		$siteInfo = $this->getSiteinfo();
		$siteInfo->setMethod('application.shipping' . self::$random);

		$start = microtime(true);
		$response = (new Download())->setSiteInfo($siteInfo)->setPath('/yun_shop/api.php')->setTransToken($token)->get();
		echo microtime(true) - $start;

		$this->assertEquals('/addons/yun_shop/api.php', $response['path']);
	}

	public function testSiteToken() {
		$_SERVER['HTTP_HOST'] = 'donknap.w7.cc';

		$siteInfo = $this->getSiteinfo();
		$siteInfo->setMethod('application.build' . self::$random);

		$response = (new SiteToken($this->fileCacher))->setSiteInfo($siteInfo)->get();

		$this->assertEquals(strlen($response['token']), 32);

		return $response['token'];
	}
}
