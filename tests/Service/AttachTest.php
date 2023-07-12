<?php

namespace W7\Sdk\OpenCloud\Tests\Service;

use PHPUnit\Framework\TestCase;
use W7\Sdk\OpenCloud\Service\Attach;
use W7\Sdk\OpenCloud\Util\Downloader;

class AttachTest extends TestCase {
	public function testDownloadFile() {
		$ur = 'https://app-attachment-1251470023.cos.ap-shanghai.myqcloud.com/85/36/07/f7/ff/57/be/bb/24/9b/4c/e9/66/23/5b/a8?sign=q-sign-algorithm%3Dsha1%26q-ak%3DAKIDaw3wPd0Mq4QXjhFs52mGXZQ1lFUWmm23%26q-sign-time%3D1689146950%3B1689147370%26q-key-time%3D1689146950%3B1689147370%26q-header-list%3Dhost%26q-url-param-list%3D%26q-signature%3Dddfb8a3602238da2c4b4777574d12339405110d8&';
		$content = Attach::instance()->downloadFileFromRemoteZip($ur, 'ox_recycle/.env', 13840227, 238789, 1689, 0);

		$this->assertNotEmpty($content);
	}
}