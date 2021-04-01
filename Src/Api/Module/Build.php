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

use W7\Sdk\Cloud\Util\Common;
use W7\Sdk\Cloud\Request\We7Request;
use W7\Sdk\Cloud\Util\SiteInfoTraiter;

class Build extends We7Request {
	protected $apiPath = '/module/build';
	protected $method = 'module.build';
	protected $postData = [];

	protected $version;
	protected $name;
	//操作类型:安装install;更新upgrade;卸载uninstall
	protected $type = 'install';

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
		$data['module_version'] = $this->version;
		$data['method'] = $data['method'] ?? $this->method;
		$data['type'] = $this->type;

		$this->postData = array_merge([], $this->postData, $data);

		return parent::post($this->postData);
	}

	protected function decode($method, $response) {
		$data = parent::decode($method, $response);
		//保存transToken
		if (!empty($data['token'])) {
			$this->cache->save('trans.token', Common::authcode($data['token'], 'ENCODE'));
		}
		return $data;
	}

	/**
	 * @param mixed $version
	 * @return Build
	 */
	public function setVersion($version) {
		$this->version = $version;
		return $this;
	}

	/**
	 * @param mixed $name
	 * @return Build
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @param string $type
	 * @return Build
	 */
	public function setType(string $type) {
		$this->type = $type;
		return $this;
	}
}
