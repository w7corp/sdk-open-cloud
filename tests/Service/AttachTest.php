<?php

namespace W7\Sdk\OpenCloud\Tests\Service;

use PHPUnit\Framework\TestCase;
use W7\Sdk\OpenCloud\Service\Attach;
use W7\Sdk\OpenCloud\Util\Downloader;

class AttachTest extends TestCase {
	public function testDownloadFile() {
		$ur = 'https://app-attachment-1251470023.cos.ap-shanghai.myqcloud.com/85/36/07/f7/ff/57/be/bb/24/9b/4c/e9/66/23/5b/a8?sign=q-sign-algorithm%3Dsha1%26q-ak%3DAKIDaw3wPd0Mq4QXjhFs52mGXZQ1lFUWmm23%26q-sign-time%3D1689150183%3B1689150603%26q-key-time%3D1689150183%3B1689150603%26q-header-list%3Dhost%26q-url-param-list%3D%26q-signature%3D754e154b00f2e37fd26ecdef6376262372ad8f2a&';
		Attach::instance()->downloadFileFromRemoteZip($ur, 'ox_recycle/.env', './test.env', 13840227, 238789, 1689, 0);

		$this->assertTrue(file_exists('./test.env'));
	}
}