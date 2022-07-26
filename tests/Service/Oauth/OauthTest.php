<?php

namespace W7\Sdk\OpenCloud\Tests\Service\Oauth;

use PHPUnit\Framework\TestCase;
use W7\Sdk\OpenCloud\Request\Request;
use W7\Sdk\OpenCloud\Service\Oauth\Oauth;

class OauthTest extends TestCase {
	public function testGetLoginUrl() {
		$result = Oauth::instance()
			->withAppId('test')
			->withAppSecret('d659404b91c7e5546fe')
			->getLoginUrl([
				'redirect' => 'http://s.w7.cc'
			]);

		$this->assertArrayHasKey('url', $result);
	}

	public function testGetAccessToken() {
		$result = Oauth::instance()
			->withAppId('test')
			->withAppSecret('d659404b91c7e5546fe')
			->getAccessToken([
				'code' => 'test'
			]);

		$this->assertArrayHasKey('access_token', $result);
		$this->assertArrayHasKey('expire_time', $result);
	}

	public function testGetUserInfo() {
		$result = Oauth::instance()
			->getUserInfo([
				'access_token' => 'P2Eb5T6psA2sY2e96ZE26xLByh2nNEEXa2NE2228YSAaxN8265wAb2zkl8'
			]);

		$this->assertArrayHasKey('open_id', $result);
		$this->assertArrayHasKey('nickname', $result);
		$this->assertArrayHasKey('avatar', $result);
	}
}