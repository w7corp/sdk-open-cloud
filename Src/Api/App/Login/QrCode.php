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

namespace W7\Api\App\Login;

use W7\Sdk\Cloud\Request\We7Request;
use W7\Sdk\Cloud\Util\SiteInfoTraiter;

class QrCode extends We7Request {
	use SiteInfoTraiter;

	protected $apiPath = '/wxapp/login/qr-code';

	public function get() {
		if (empty($this->siteInfo)) {
			throw new \RuntimeException('缺少站点信息');
		}
		$data = $this->siteInfo->toArray();
		return parent::post($data);
	}
}
