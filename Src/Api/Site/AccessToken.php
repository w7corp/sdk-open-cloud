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

namespace W7\Api\Site;

use W7\Sdk\Cloud\Request\We7Request;
use W7\Sdk\Cloud\Util\SiteInfoTraiter;

class AccessToken extends We7Request
{
	use SiteInfoTraiter;

	protected $method  = 'site.access_token';
	protected $apiPath = '/site/accesstoken/with-js-token';

	public function getByJsToken($jsToken)
	{
		if (empty($this->siteInfo)) {
			throw new \RuntimeException('缺少站点信息参数');
		}

		$data             = $this->siteInfo->toArray();
		$data['method']   = $data['method'] ?? $this->method;
		$data['js_token'] = $jsToken;
		return parent::post($data);
	}
}
