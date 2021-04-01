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

class Profile extends We7Request {
	use SiteInfoTraiter;

	protected $apiPath = '/site/register/profile';

	public function get(array $data = [
		'url' => ''
	]) {
		if (empty($this->siteInfo)) {
			throw new \RuntimeException('缺少站点信息参数');
		}
		if (empty($data['url'])) {
			throw new \RuntimeException('缺少Url参数');
		}
		$post = $this->siteInfo->toArray();
		$post['url'] = $data['url'];

		return parent::post($post);
	}
}
