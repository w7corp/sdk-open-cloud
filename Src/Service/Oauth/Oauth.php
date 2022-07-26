<?php

namespace W7\Sdk\OpenCloud\Service\Oauth;

use W7\Sdk\OpenCloud\Request\ServiceRequest;
use W7\Sdk\OpenCloud\Util\InstanceTraiter;

/**
 * @method array getLoginUrl(array $params = ['redirect'])
 * @method array getAccessToken(array $params = ['code'])
 * @method array getUserInfo(array $params = ['access_token'])
 */
class Oauth extends ServiceRequest {
	use InstanceTraiter;

	public function getApiMap() {
		return [
			'getLoginUrl' => [
				'extends' => self::OPERATION_CHECK_SIGN,
				'uri' => '/we7/open/oauth/login-url/index',
				'parameters' => [
					'redirect' => [
						'type' => self::TYPE_STRING,
						'location' => self::LOCATION_FORM_PARAM,
						'required' => true,
					]
				]
			],
			'getAccessToken' => [
				'extends' => self::OPERATION_CHECK_SIGN,
				'uri' => '/we7/open/oauth/access-token/code',
				'parameters' => [
					'code' => [
						'type' => self::TYPE_STRING,
						'location' => self::LOCATION_FORM_PARAM,
						'required' => true,
					]
				]
			],
			'getUserInfo' => [
				'uri' => '/we7/open/oauth/user/info',
				'parameters' => [
					'access_token' => [
						'type' => self::TYPE_STRING,
						'location' => self::LOCATION_FORM_PARAM,
						'required' => true,
					]
				]
			],
		];
	}
}