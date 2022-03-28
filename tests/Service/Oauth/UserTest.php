<?php

namespace W7\Sdk\OpenCloud\Tests\Service\Oauth;

use PHPUnit\Framework\TestCase;
use W7\Sdk\OpenCloud\Request\Request;
use W7\Sdk\OpenCloud\Service\Oauth\User;

class UserTest extends TestCase {
	public function testGetUserInfoByJsCode() {
		Request::setEnvBeta();

		$result = User::instance()
			->withAppId('test')
			->withAppSecret('d659404b91c7e5546fe')
			->getUserInfoByIsCode([
				'code' => 'GI50F0p0pIYz55iz1me3Dy0yG515GYG5'
			]);

		$this->assertArrayHasKey('open_id', $result);
	}
}