<?php

namespace W7\Sdk\OpenCloud\Service\Oauth;

use W7\Sdk\OpenCloud\Request\ServiceRequest;
use W7\Sdk\OpenCloud\Util\InstanceTraiter;

/**
 * @method array getUserInfoByIsCode(array $params = ['code'])
 */
class User extends ServiceRequest {
	use InstanceTraiter;

	public function getApiMap() {
		return [
			'getUserInfoByIsCode' => [
				'extends' => self::OPERATION_CHECK_SIGN,
				'uri' => '/we7/open/oauth/user/info/with-js-code',
				'parameters' => [
					'code' => [
						'type' => self::TYPE_STRING,
						'location' => self::LOCATION_FORM_PARAM,
						'required' => true,
					]
				]
			],
		];
	}
}