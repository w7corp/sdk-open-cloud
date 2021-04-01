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

namespace W7\Api\Module;

use W7\Sdk\Cloud\Request\We7Request;
use W7\Sdk\Cloud\Util\SiteInfoTraiter;

class Info extends We7Request {
	protected $apiPath = '/module/info';
	protected $method = 'module.info';
	protected $name;

	use SiteInfoTraiter;

	public function get() {
		if (empty($this->siteInfo)) {
			throw new \RuntimeException('缺少站点信息参数');
		}
		if (empty($this->name)) {
			throw new \RuntimeException('缺点模块名称');
		}
		$data = $this->siteInfo->toArray();
		$data['module'] = $this->name;
		$data['method'] = $data['method'] ?? $this->method;
		return parent::post($data);
	}

	/**
	 * 要获取数据的模块名称
	 * @param $name
	 * @return $this
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
}
